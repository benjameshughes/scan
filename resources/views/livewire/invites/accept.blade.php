<div class="p-6">
    <!-- Header -->
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
            Complete Your Registration
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Set a password to complete your registration and start using the scanner application
        </p>
    </div>

    <!-- Form -->
    <form wire:submit="acceptInvite" class="space-y-4">
        <!-- Name Field -->
        <div>
            <flux:input 
                wire:model="name" 
                id="name"
                name="name"
                label="Full Name *"
                placeholder="Enter your full name"
                required
                class="w-full"
            />
            <flux:error name="name"/>
        </div>

        <!-- Email Field (Read-only) -->
        <div>
            <flux:input 
                wire:model="email" 
                id="email"
                name="email"
                type="email"
                label="Email Address"
                readonly
                variant="filled"
                class="w-full"
            />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                This email address is associated with your invitation
            </p>
        </div>

        <!-- Password Field -->
        <div>
            <flux:input 
                wire:model="password" 
                id="password"
                name="password"
                type="password"
                label="Password *"
                placeholder="Create a strong password"
                required
                class="w-full"
            />
            <flux:error name="password"/>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Must be at least 6 characters with mixed case letters
            </p>
        </div>

        <!-- Confirm Password Field -->
        <div>
            <flux:input 
                wire:model="password_confirmation" 
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                label="Confirm Password *"
                placeholder="Confirm your password"
                required
                class="w-full"
            />
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <flux:button 
                type="submit" 
                variant="primary" 
                class="w-full"
            >
                <flux:icon.check class="size-4" />
                Complete Registration
            </flux:button>
        </div>
    </form>

    <!-- Help Text -->
    <div class="mt-6 text-center">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            By completing registration, you'll gain access to the barcode scanning system.
        </p>
    </div>
</div>