<?php

use App\Livewire\Admin\Users\Add;
use App\Livewire\Admin\Users\Edit;
use App\Livewire\UsersTable;
use App\Models\Invite;
use App\Models\User;
use App\Notifications\InviteNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles and permissions
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'user']);

    // Create all permissions
    $permissions = [
        'view users', 'create users', 'edit users', 'delete users',
        'view scans', 'view scanner', 'create scans', 'edit scans', 'delete scans', 'sync scans',
        'view products', 'create products', 'edit products', 'delete products', 'import products',
        'view invites', 'create invites', 'edit invites', 'delete invites',
        'receive empty bay notifications',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    // Assign all permissions to admin role
    Role::findByName('admin')->givePermissionTo(Permission::all());

    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('admin');

    $this->actingAs($this->adminUser);
});

describe('Add User Component', function () {
    test('it renders successfully', function () {
        Livewire::test(Add::class)
            ->assertStatus(200);
    });

    test('it initializes with default values', function () {
        Livewire::test(Add::class)
            ->assertSet('name', '')
            ->assertSet('email', '')
            ->assertSet('role', 'user')
            ->assertSet('sendInvite', true);
    });

    test('it validates required fields', function () {
        Livewire::test(Add::class)
            ->call('save')
            ->assertHasErrors(['name' => 'required', 'email' => 'required']);
    });

    test('it validates email format', function () {
        Livewire::test(Add::class)
            ->set('name', 'Test User')
            ->set('email', 'invalid-email')
            ->call('save')
            ->assertHasErrors(['email' => 'email']);
    });

    test('it validates unique email', function () {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        Livewire::test(Add::class)
            ->set('name', 'Test User')
            ->set('email', 'existing@example.com')
            ->call('save')
            ->assertHasErrors(['email' => 'unique']);
    });

    test('it creates user with invitation', function () {
        Notification::fake();

        Livewire::test(Add::class)
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('role', 'user')
            ->set('sendInvite', true)
            ->call('save')
            ->assertRedirect(route('users.index'));

        // Verify user was created
        $user = User::where('email', 'newuser@example.com')->first();
        expect($user)->not->toBeNull();
        expect($user->name)->toBe('New User');
        expect($user->hasRole('user'))->toBeTrue();

        // Verify invitation was created
        $invite = Invite::where('email', 'newuser@example.com')->first();
        expect($invite)->not->toBeNull();
        expect($invite->user_id)->toBe($user->id);
        expect($invite->invited_by)->toBe($this->adminUser->id);

        // Verify notification was sent
        Notification::assertSentTo($invite, InviteNotification::class);
    });

    test('it creates user without invitation', function () {
        Notification::fake();

        Livewire::test(Add::class)
            ->set('name', 'New User')
            ->set('email', 'newuser@example.com')
            ->set('role', 'admin')
            ->set('sendInvite', false)
            ->call('save')
            ->assertRedirect(route('users.index'));

        // Verify user was created
        $user = User::where('email', 'newuser@example.com')->first();
        expect($user)->not->toBeNull();
        expect($user->hasRole('admin'))->toBeTrue();

        // Verify no invitation was created
        $invite = Invite::where('email', 'newuser@example.com')->first();
        expect($invite)->toBeNull();

        // Verify no notification was sent
        Notification::assertNothingSent();
    });
});

describe('Edit User Component', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $this->user->assignRole('user');
        $this->user->givePermissionTo('view scans');
    });

    test('it renders successfully', function () {
        Livewire::test(Edit::class, ['user' => $this->user])
            ->assertStatus(200);
    });

    test('it loads user data correctly', function () {
        Livewire::test(Edit::class, ['user' => $this->user])
            ->assertSet('form.name', 'Test User')
            ->assertSet('form.email', 'test@example.com')
            ->assertSet('selectedRole', 'user')
            ->assertSet('userPermissions.view scans', true)
            ->assertSet('userPermissions.create users', false);
    });

    test('it updates user basic information', function () {
        Livewire::test(Edit::class, ['user' => $this->user])
            ->set('form.name', 'Updated Name')
            ->set('form.email', 'updated@example.com')
            ->call('updateUser')
            ->assertRedirect(route('users.index'));

        $this->user->refresh();
        expect($this->user->name)->toBe('Updated Name');
        expect($this->user->email)->toBe('updated@example.com');
    });

    test('it updates user password when provided', function () {
        $oldPassword = $this->user->password;

        Livewire::test(Edit::class, ['user' => $this->user])
            ->set('form.password', 'newpassword123')
            ->call('updateUser');

        $this->user->refresh();
        expect($this->user->password)->not->toBe($oldPassword);
        expect(\Hash::check('newpassword123', $this->user->password))->toBeTrue();
    });

    test('it updates user role', function () {
        Livewire::test(Edit::class, ['user' => $this->user])
            ->set('selectedRole', 'admin')
            ->call('updateUser');

        $this->user->refresh();
        expect($this->user->hasRole('admin'))->toBeTrue();
        expect($this->user->hasRole('user'))->toBeFalse();
    });

    test('it updates individual permissions for non-admin users', function () {
        Livewire::test(Edit::class, ['user' => $this->user])
            ->set('userPermissions.create users', true)
            ->set('userPermissions.view scans', false)
            ->call('updateUser');

        $this->user->refresh();
        expect($this->user->can('create users'))->toBeTrue();
        expect($this->user->can('view scans'))->toBeFalse();
    });

    test('it sets all permissions when admin role is selected', function () {
        $component = Livewire::test(Edit::class, ['user' => $this->user]);

        // Initially, not all permissions are checked
        expect($component->get('userPermissions')['delete users'])->toBeFalse();

        // Select admin role
        $component->set('selectedRole', 'admin');

        // All permissions should be checked
        foreach ($component->get('userPermissions') as $permission => $value) {
            expect($value)->toBeTrue();
        }
    });
});

describe('UsersTable Component', function () {
    beforeEach(function () {
        // Create some test users
        $this->users = User::factory()->count(5)->create();
        $this->users[0]->update(['email_verified_at' => now()]);
        $this->users[1]->update(['email_verified_at' => now()]);
    });

    test('it renders successfully', function () {
        Livewire::test(UsersTable::class)
            ->assertStatus(200);
    });

    test('it displays users', function () {
        $component = Livewire::test(UsersTable::class);

        foreach ($this->users as $user) {
            $component->assertSee($user->name)
                ->assertSee($user->email);
        }
    });

    test('it searches users by name', function () {
        $searchUser = User::factory()->create(['name' => 'John Doe']);

        Livewire::test(UsersTable::class)
            ->set('search', 'John Doe')
            ->assertSee('John Doe')
            ->assertDontSee($this->users[0]->name);
    });

    test('it searches users by email', function () {
        $searchUser = User::factory()->create(['email' => 'unique@example.com']);

        Livewire::test(UsersTable::class)
            ->set('search', 'unique@example.com')
            ->assertSee('unique@example.com')
            ->assertDontSee($this->users[0]->email);
    });

    test('it filters by verified status', function () {
        // Create more users to ensure we have enough data
        $verifiedUser = User::factory()->create(['email_verified_at' => now()]);
        $unverifiedUser = User::factory()->create(['email_verified_at' => null]);

        $component = Livewire::test(UsersTable::class)
            ->set('filters.verified', '1');

        // Should see verified user email
        $component->assertSee($verifiedUser->email);

        // Set filter to unverified
        $component->set('filters.verified', '0')
            ->assertSee($unverifiedUser->email);
    });

    test('it can delete a user', function () {
        $userToDelete = $this->users[0];

        Livewire::test(UsersTable::class)
            ->call('deleteUser', $userToDelete->id);

        expect(User::find($userToDelete->id))->toBeNull();
    });

    test('bulk verify action works', function () {
        $unverifiedUsers = [$this->users[2]->id, $this->users[3]->id];

        Livewire::test(UsersTable::class)
            ->set('bulkSelectedIds', $unverifiedUsers)
            ->call('executeBulkAction', 'verify');

        // Check users were verified
        expect(User::find($this->users[2]->id)->email_verified_at)->not->toBeNull();
        expect(User::find($this->users[3]->id)->email_verified_at)->not->toBeNull();
    });

    test('bulk delete action works', function () {
        $usersToDelete = [$this->users[0]->id, $this->users[1]->id];

        Livewire::test(UsersTable::class)
            ->set('bulkSelectedIds', $usersToDelete)
            ->call('executeBulkAction', 'delete');

        // Check users were deleted
        expect(User::find($this->users[0]->id))->toBeNull();
        expect(User::find($this->users[1]->id))->toBeNull();
    });

    test('bulk send invites action works', function () {
        Notification::fake();

        $usersForInvites = [$this->users[2]->id, $this->users[3]->id];

        Livewire::test(UsersTable::class)
            ->set('bulkSelectedIds', $usersForInvites)
            ->call('executeBulkAction', 'send_invites');

        // Check invitations were created
        $invite1 = Invite::where('user_id', $this->users[2]->id)->first();
        $invite2 = Invite::where('user_id', $this->users[3]->id)->first();

        expect($invite1)->not->toBeNull();
        expect($invite2)->not->toBeNull();

        // Check notifications were sent
        Notification::assertSentTo($invite1, InviteNotification::class);
        Notification::assertSentTo($invite2, InviteNotification::class);
    });

    test('it does not send duplicate invites', function () {
        Notification::fake();

        // Create existing invite for one user
        Invite::create([
            'name' => $this->users[2]->name,
            'email' => $this->users[2]->email,
            'token' => \Str::random(64),
            'user_id' => $this->users[2]->id,
            'invited_by' => $this->adminUser->id,
            'expires_at' => now()->addHours(24),
        ]);

        $usersForInvites = [$this->users[2]->id, $this->users[3]->id];

        Livewire::test(UsersTable::class)
            ->set('bulkSelectedIds', $usersForInvites)
            ->call('executeBulkAction', 'send_invites');

        // Check only one new invitation was created
        expect(Invite::where('user_id', $this->users[3]->id)->count())->toBe(1);
        expect(Invite::where('user_id', $this->users[2]->id)->count())->toBe(1); // Still only 1
    });
});
