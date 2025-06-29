<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Sign In') }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ __('Welcome back! Please sign in to your account.') }}</p>
        </div>
        
        <div class="p-6">
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form wire:submit="login" class="space-y-4">
                <!-- Email Address -->
                <div>
                    <flux:input 
                        label="{{ __('Email Address') }}" 
                        wire:model="form.email" 
                        id="email" 
                        type="email" 
                        name="email" 
                        placeholder="{{ __('Enter your email address') }}"
                        autofocus 
                        required 
                        autocomplete="username"
                        class="w-full"
                    />
                    <flux:error name="form.email"/>
                </div>

                <!-- Password -->
                <div>
                    <flux:input 
                        label="{{ __('Password') }}" 
                        wire:model="form.password" 
                        id="password" 
                        type="password" 
                        name="password" 
                        placeholder="{{ __('Enter your password') }}"
                        required 
                        autocomplete="current-password"
                        class="w-full"
                    />
                    <flux:error name="form.password"/>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <flux:checkbox 
                            wire:model="form.remember" 
                            id="remember" 
                            name="remember"
                            label="{{ __('Remember me') }}"
                        />
                    </div>
                    
                    @if (Route::has('password.request'))
                        <a class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 rounded" href="{{ route('password.request') }}" wire:navigate>
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <div class="pt-4">
                    <flux:button 
                        variant="primary" 
                        type="submit" 
                        class="w-full"
                    >
                        {{ __('Sign In') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
