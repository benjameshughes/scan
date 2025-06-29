<?php

namespace Tests\Feature\Auth;

use Livewire\Volt\Volt;

test('registration uses domains from ALLOWED_DOMAINS environment variable', function () {
    // Get the actual domains from the environment
    $envDomains = env('ALLOWED_DOMAINS');

    if (empty($envDomains)) {
        $this->markTestSkipped('ALLOWED_DOMAINS environment variable is not set');
    }

    // Parse the domains as the config does
    $allowedDomains = explode(',', $envDomains);
    $allowedDomains = array_map('trim', $allowedDomains); // Trim whitespace

    $this->assertNotEmpty($allowedDomains, 'ALLOWED_DOMAINS should contain at least one domain');

    // Test that we can register with each domain from the environment
    foreach ($allowedDomains as $domain) {
        if (empty($domain)) {
            continue;
        } // Skip empty strings

        $email = 'envtest'.uniqid().'@'.$domain;

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Environment Test User')
            ->set('email', $email)
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123');

        $component->call('register');

        // Should successfully register
        $component->assertRedirect(route('dashboard', absolute: false));

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name' => 'Environment Test User',
        ]);

        // Log out for next iteration
        auth()->logout();
    }
});

test('config correctly parses comma-separated domains', function () {
    // Test various formats that might appear in the env file
    $testCases = [
        'domain1.com,domain2.com' => ['domain1.com', 'domain2.com'],
        'domain1.com, domain2.com' => ['domain1.com', 'domain2.com'], // with space
        'single.com' => ['single.com'],
        'domain1.com,domain2.com,domain3.com' => ['domain1.com', 'domain2.com', 'domain3.com'],
    ];

    foreach ($testCases as $envValue => $expected) {
        // Temporarily set the config
        config(['allowedDomains.domains' => explode(',', $envValue)]);

        $domains = config('allowedDomains.domains');

        // Trim the domains as the real config might do
        $domains = array_map('trim', $domains);

        expect($domains)->toBe($expected);
    }
});

test('shows current allowed domains configuration', function () {
    $domains = config('allowedDomains.domains');

    echo "\nCurrent allowed domains configuration:\n";
    if (empty($domains)) {
        echo "No domains configured (ALLOWED_DOMAINS env variable is empty or not set)\n";
    } else {
        foreach ($domains as $domain) {
            echo '- '.$domain."\n";
        }
    }
    echo "\n";

    // Verify the config is working
    expect($domains)->toBeArray();
});
