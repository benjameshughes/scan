<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

use function class_exists;

it('shows legacy scanner without feature', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    Permission::firstOrCreate(['name' => 'view scanner']);
    Permission::firstOrCreate(['name' => 'view scans']);
    $user->givePermissionTo('view scanner');
    $user->givePermissionTo('view scans');
    $this->actingAs($user);

    // Skip if Pennant not installed - legacy should still be accessible
    if (! class_exists(\Laravel\Pennant\Feature::class)) {
        $this->get(route('scan.scan'))
            ->assertOk()
            ->assertSeeLivewire('App\\Livewire\\ProductScanner');

        return;
    }

    // Ensure feature is not active
    \Laravel\Pennant\Feature::for($user)->deactivate('scanner_refactor');

    $this->get(route('scan.scan'))
        ->assertOk()
        ->assertSeeLivewire('App\\Livewire\\ProductScanner');
});

it('shows refactored scanner when feature active', function () {
    if (! class_exists(\Laravel\Pennant\Feature::class)) {
        test()->markTestSkipped('Laravel Pennant not installed.');
    }

    $user = User::factory()->create(['email_verified_at' => now()]);
    Permission::firstOrCreate(['name' => 'view scanner']);
    Permission::firstOrCreate(['name' => 'view scans']);
    $user->givePermissionTo('view scanner');
    $user->givePermissionTo('view scans');
    $this->actingAs($user);

    \Laravel\Pennant\Feature::for($user)->activate('scanner_refactor');

    $this->get(route('scan.scan'))
        ->assertOk()
        ->assertSee('Refactored'); // badge text in refactored view header
});

it('blocks direct refactored route without feature', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    Permission::firstOrCreate(['name' => 'view scanner']);
    $user->givePermissionTo('view scanner');
    $this->actingAs($user);

    if (! class_exists(\Laravel\Pennant\Feature::class)) {
        $this->get(route('scanner.refactored'))->assertForbidden();

        return;
    }

    \Laravel\Pennant\Feature::for($user)->deactivate('scanner_refactor');

    $this->get(route('scanner.refactored'))->assertForbidden();
});
