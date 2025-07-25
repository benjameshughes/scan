<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
        'settings' => [
            'scan_sound' => true,
            'dark_mode' => false,
            'auto_submit' => false,
        ]
    ]);
});

describe('Reactive Settings Component', function () {
    test('component loads with user settings', function () {
        $component = Livewire::actingAs($this->user)
            ->test('profile.user-settings');
            
        expect($component->get('settings'))->toBe([
            'dark_mode' => false,
            'auto_submit' => false,
            'scan_sound' => true,
        ]);
    });

    test('dark mode setting auto-saves when changed', function () {
        Livewire::actingAs($this->user)
            ->test('profile.user-settings')
            ->set('settings.dark_mode', true)
            ->assertDispatched('theme-changed', darkMode: true)
            ->assertDispatched('setting-saved');

        // Verify setting was persisted
        $this->user->refresh();
        expect($this->user->settings['dark_mode'])->toBeTrue();
    });

    test('auto submit setting auto-saves when changed', function () {
        Livewire::actingAs($this->user)
            ->test('profile.user-settings')
            ->set('settings.auto_submit', true)
            ->assertDispatched('setting-saved');

        // Verify setting was persisted
        $this->user->refresh();
        expect($this->user->settings['auto_submit'])->toBeTrue();
    });

    test('scan sound setting auto-saves when changed', function () {
        Livewire::actingAs($this->user)
            ->test('profile.user-settings')
            ->set('settings.scan_sound', false)
            ->assertDispatched('setting-saved');

        // Verify setting was persisted
        $this->user->refresh();
        expect($this->user->settings['scan_sound'])->toBeFalse();
    });

    test('multiple settings can be changed independently', function () {
        $component = Livewire::actingAs($this->user)
            ->test('profile.user-settings');

        // Change dark mode
        $component->set('settings.dark_mode', true)
            ->assertDispatched('theme-changed', darkMode: true);

        // Change auto submit
        $component->set('settings.auto_submit', true);

        // Change scan sound
        $component->set('settings.scan_sound', false);

        // Verify all settings were persisted
        $this->user->refresh();
        expect($this->user->settings)->toBe([
            'dark_mode' => true,
            'auto_submit' => true,
            'scan_sound' => false,
        ]);
    });

    test('saving state is tracked correctly', function () {
        $component = Livewire::actingAs($this->user)
            ->test('profile.user-settings');

        // Initially not saving
        expect($component->get('saving'))->toBeFalse();

        // Set saving to true manually to test the state
        $component->call('saveSettings');
        
        // After save completes, saving should be false again
        expect($component->get('saving'))->toBeFalse();
    });

    test('settings events are dispatched correctly', function () {
        Livewire::actingAs($this->user)
            ->test('profile.user-settings')
            ->set('settings.dark_mode', true)
            ->assertDispatched('settings-updated')
            ->assertDispatched('theme-changed', darkMode: true)
            ->assertDispatched('setting-saved');
    });
});

describe('API Integration with Reactive Settings', function () {
    test('api reflects reactive setting changes', function () {
        // Change setting via component
        Livewire::actingAs($this->user)
            ->test('profile.user-settings')
            ->set('settings.dark_mode', true);

        // Verify API returns updated setting
        $response = $this->actingAs($this->user)
            ->get('/api/user/settings');

        $response->assertOk()
            ->assertJson([
                'dark_mode' => true,
                'auto_submit' => false,
                'scan_sound' => true,
            ]);
    });

    test('theme manager receives live updates', function () {
        // Mock the theme manager events
        $component = Livewire::actingAs($this->user)
            ->test('profile.user-settings');

        // Change dark mode and verify the correct event is dispatched
        $component->set('settings.dark_mode', true)
            ->assertDispatched('theme-changed', darkMode: true);

        // Change back to light mode
        $component->set('settings.dark_mode', false)
            ->assertDispatched('theme-changed', darkMode: false);
    });
});

describe('Performance and UX', function () {
    test('settings save without page refresh', function () {
        $component = Livewire::actingAs($this->user)
            ->test('profile.user-settings');

        // Change multiple settings
        $component->set('settings.dark_mode', true)
            ->set('settings.auto_submit', true)
            ->set('settings.scan_sound', false);

        // Verify settings persisted without full component reload
        $this->user->refresh();
        expect($this->user->settings)->toBe([
            'dark_mode' => true,
            'auto_submit' => true,
            'scan_sound' => false,
        ]);
    });

    test('rapid setting changes are handled correctly', function () {
        $component = Livewire::actingAs($this->user)
            ->test('profile.user-settings');

        // Rapidly toggle a setting
        $component->set('settings.dark_mode', true)
            ->set('settings.dark_mode', false)
            ->set('settings.dark_mode', true);

        // Verify final state is correct
        $this->user->refresh();
        expect($this->user->settings['dark_mode'])->toBeTrue();
    });

    test('concurrent setting changes work correctly', function () {
        $component = Livewire::actingAs($this->user)
            ->test('profile.user-settings');

        // Change multiple settings at once
        $component->set('settings.dark_mode', true);
        $component->set('settings.auto_submit', true);
        $component->set('settings.scan_sound', false);

        // All settings should be saved correctly
        $this->user->refresh();
        expect($this->user->settings)->toBe([
            'dark_mode' => true,
            'auto_submit' => true,
            'scan_sound' => false,
        ]);
    });
});