<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Livewire\Volt\Volt;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response
        ->assertOk()
        ->assertSeeVolt('pages.auth.register');
});

test('new users can register with allowed domains', function () {
    // Get allowed domains from config
    $allowedDomains = config('allowedDomains.domains');

    // Skip test if no domains are configured
    if (empty($allowedDomains)) {
        $this->markTestSkipped('No allowed domains configured in ALLOWED_DOMAINS env variable');
    }

    // Test registration with each allowed domain
    foreach ($allowedDomains as $domain) {
        $email = 'test'.uniqid().'@'.$domain;

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', $email)
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123');

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => $email,
        ]);

        // Log out for next iteration
        auth()->logout();
    }
});

test('new users cannot register with disallowed domains', function () {
    // Get allowed domains from config
    $allowedDomains = config('allowedDomains.domains');

    // Test with some common domains that should not be allowed
    $disallowedDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'example.com'];

    // Remove any that might be in the allowed list
    $disallowedDomains = array_diff($disallowedDomains, $allowedDomains);

    // Test registration with each disallowed domain
    foreach ($disallowedDomains as $domain) {
        $email = 'test'.uniqid().'@'.$domain;

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', $email)
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123');

        $component->call('register');

        // Should have validation error for email
        $component->assertHasErrors(['email']);

        // Verify user was NOT created
        $this->assertDatabaseMissing('users', [
            'email' => $email,
        ]);

        // Should not be authenticated
        $this->assertGuest();
    }
});

test('registration fails with invalid email format', function () {
    $component = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', 'invalid-email')
        ->set('password', 'Password123')
        ->set('password_confirmation', 'Password123');

    $component->call('register');

    $component->assertHasErrors(['email']);
    $this->assertGuest();
});

test('registration fails with mismatched passwords', function () {
    $allowedDomains = config('allowedDomains.domains');
    if (empty($allowedDomains)) {
        $this->markTestSkipped('No allowed domains configured');
    }

    $email = 'test@'.$allowedDomains[0];

    $component = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', $email)
        ->set('password', 'Password123')
        ->set('password_confirmation', 'DifferentPassword');

    $component->call('register');

    $component->assertHasErrors(['password']);
    $this->assertGuest();
});

test('registration fails with weak password', function () {
    $allowedDomains = config('allowedDomains.domains');
    if (empty($allowedDomains)) {
        $this->markTestSkipped('No allowed domains configured');
    }

    $email = 'test@'.$allowedDomains[0];

    $component = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', $email)
        ->set('password', 'weak')
        ->set('password_confirmation', 'weak');

    $component->call('register');

    $component->assertHasErrors(['password']);
    $this->assertGuest();
});

test('registration fails with duplicate email', function () {
    $allowedDomains = config('allowedDomains.domains');
    if (empty($allowedDomains)) {
        $this->markTestSkipped('No allowed domains configured');
    }

    $email = 'existing@'.$allowedDomains[0];

    // Create existing user
    User::factory()->create(['email' => $email]);

    $component = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', $email)
        ->set('password', 'Password123')
        ->set('password_confirmation', 'Password123');

    $component->call('register');

    $component->assertHasErrors(['email']);
    $this->assertGuest();
});
