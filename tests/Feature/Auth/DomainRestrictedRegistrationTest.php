<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Livewire\Volt\Volt;

beforeEach(function () {
    // Set up test allowed domains
    config(['allowedDomains.domains' => ['company.com', 'test.org', 'example.net']]);
});

test('config loads allowed domains from environment', function () {
    // Reset config to default
    config(['allowedDomains.domains' => explode(',', env('ALLOWED_DOMAINS', 'default.com'))]);

    $domains = config('allowedDomains.domains');
    expect($domains)->toBeArray();
    expect($domains)->not->toBeEmpty();
});

test('users can register with each allowed domain', function () {
    $allowedDomains = ['company.com', 'test.org', 'example.net'];
    config(['allowedDomains.domains' => $allowedDomains]);

    foreach ($allowedDomains as $domain) {
        $email = 'user'.uniqid().'@'.$domain;

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', $email)
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123');

        $component->call('register');

        // Should redirect to dashboard on success
        $component->assertRedirect(route('dashboard', absolute: false));

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name' => 'Test User',
        ]);

        // Should be authenticated
        $this->assertAuthenticated();

        // Get the created user
        $user = User::where('email', $email)->first();
        expect($user)->not->toBeNull();
        expect($user->email)->toBe($email);

        // Log out for next iteration
        auth()->logout();
    }
});

test('users cannot register with disallowed domains', function () {
    $allowedDomains = ['company.com', 'test.org'];
    config(['allowedDomains.domains' => $allowedDomains]);

    $disallowedDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'notallowed.com'];

    foreach ($disallowedDomains as $domain) {
        $email = 'user'.uniqid().'@'.$domain;

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', $email)
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123');

        $component->call('register');

        // Should have validation error
        $component->assertHasErrors(['email']);

        // Error message should indicate domain is not allowed
        $errors = $component->errors();
        expect($errors->first('email'))->toContain('domain is not allowed');

        // Verify user was NOT created
        $this->assertDatabaseMissing('users', [
            'email' => $email,
        ]);

        // Should not be authenticated
        $this->assertGuest();
    }
});

test('domain validation is case insensitive', function () {
    config(['allowedDomains.domains' => ['company.com', 'test.org']]);

    // The validation rule handles case-insensitive domain matching
    // But Laravel's email validation requires lowercase emails

    // Test with lowercase email - should pass
    $email1 = 'user'.uniqid().'@company.com';
    $component = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', $email1)
        ->set('password', 'Password123')
        ->set('password_confirmation', 'Password123');

    $component->call('register');
    $component->assertRedirect(route('dashboard', absolute: false));
    $this->assertDatabaseHas('users', ['email' => $email1]);
    auth()->logout();

    // Test another allowed domain - should pass
    $email2 = 'user'.uniqid().'@test.org';
    $component2 = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', $email2)
        ->set('password', 'Password123')
        ->set('password_confirmation', 'Password123');

    $component2->call('register');
    $component2->assertRedirect(route('dashboard', absolute: false));
    $this->assertDatabaseHas('users', ['email' => $email2]);
    auth()->logout();

    // Test disallowed domain - should fail
    $email3 = 'user'.uniqid().'@gmail.com';
    $component3 = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', $email3)
        ->set('password', 'Password123')
        ->set('password_confirmation', 'Password123');

    $component3->call('register');
    $component3->assertHasErrors(['email']);
    $this->assertDatabaseMissing('users', ['email' => $email3]);

    // Test that uppercase domains in config still work with lowercase emails
    config(['allowedDomains.domains' => ['COMPANY.COM', 'TEST.ORG']]);

    $email4 = 'user'.uniqid().'@company.com';
    $component4 = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', $email4)
        ->set('password', 'Password123')
        ->set('password_confirmation', 'Password123');

    $component4->call('register');
    $component4->assertRedirect(route('dashboard', absolute: false));
    auth()->logout();
});

test('empty allowed domains array blocks all registrations', function () {
    config(['allowedDomains.domains' => []]);

    $component = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', 'user@any-domain.com')
        ->set('password', 'Password123')
        ->set('password_confirmation', 'Password123');

    $component->call('register');

    $component->assertHasErrors(['email']);
    $this->assertGuest();
});

test('subdomains are handled correctly', function () {
    config(['allowedDomains.domains' => ['company.com']]);

    $testCases = [
        'user@company.com' => true,           // should pass
        'user@mail.company.com' => false,     // subdomain should fail
        'user@sub.company.com' => false,      // subdomain should fail
        'user@company.com.fake' => false,     // should fail
    ];

    foreach ($testCases as $email => $shouldPass) {
        $uniqueEmail = str_replace('user@', 'user'.uniqid().'@', $email);

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', $uniqueEmail)
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123');

        $component->call('register');

        if ($shouldPass) {
            $component->assertRedirect(route('dashboard', absolute: false));
            $this->assertDatabaseHas('users', ['email' => $uniqueEmail]);
            auth()->logout();
        } else {
            $component->assertHasErrors(['email']);
            $this->assertDatabaseMissing('users', ['email' => $uniqueEmail]);
        }
    }
});
