<?php

use App\Models\User;
use App\Rules\RegisterAllowedDomains;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class, new RegisterAllowedDomains()],
            'password' => ['required', 'string', 'confirmed', Rules\Password::min(6)->max(255)->letters()->mixedCase()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Create Account') }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ __('Join our platform to get started with inventory management.') }}</p>
        </div>
        
        <div class="p-6">
            <form wire:submit="register" class="space-y-4">
                <!-- Name -->
                <div>
                    <flux:input 
                        wire:model="name" 
                        id="name" 
                        label="{{ __('Full Name') }}"
                        type="text" 
                        name="name" 
                        placeholder="{{ __('Enter your full name') }}"
                        required
                        autofocus 
                        autocomplete="name"
                        class="w-full"
                    />
                    <flux:error name="name"/>
                </div>

                <!-- Email Address -->
                <div>
                    <flux:input 
                        wire:model="email" 
                        id="email" 
                        label="{{ __('Email Address') }}"
                        type="email" 
                        name="email" 
                        placeholder="{{ __('Enter your email address') }}"
                        required
                        autocomplete="username"
                        class="w-full"
                    />
                    <flux:error name="email"/>
                </div>

                <!-- Password -->
                <div>
                    <flux:input 
                        wire:model="password" 
                        id="password" 
                        label="{{ __('Password') }}"
                        type="password"
                        name="password"
                        placeholder="{{ __('Create a strong password') }}"
                        required 
                        autocomplete="new-password"
                        class="w-full"
                    />
                    <flux:error name="password"/>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Must be at least 6 characters with mixed case letters') }}
                    </p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <flux:input 
                        wire:model="password_confirmation" 
                        id="password_confirmation" 
                        label="{{ __('Confirm Password') }}"
                        type="password"
                        name="password_confirmation" 
                        placeholder="{{ __('Confirm your password') }}"
                        required 
                        autocomplete="new-password"
                        class="w-full"
                    />
                    <flux:error name="password_confirmation"/>
                </div>

                <div class="flex items-center justify-between pt-4">
                    <a class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 rounded"
                       href="{{ route('login') }}" wire:navigate>
                        {{ __('Already registered?') }}
                    </a>

                    <flux:button 
                        variant="primary" 
                        type="submit"
                        class="ml-4"
                    >
                        {{ __('Create Account') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
