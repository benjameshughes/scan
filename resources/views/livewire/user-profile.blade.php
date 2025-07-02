<div class="w-full space-y-6">
    <!-- Profile Information -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Profile Information
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Update your account's profile information and email address.
            </p>
        </div>

        <form wire:submit.prevent="updateProfile" class="p-6 space-y-4">
            @if (session('profile-message'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-green-700 dark:text-green-300">{{ session('profile-message') }}</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                <div>
                    <flux:input 
                        wire:model="email" 
                        id="email"
                        name="email"
                        type="email"
                        label="Email Address *"
                        placeholder="Enter your email address"
                        required
                        class="w-full"
                    />
                    <flux:error name="email"/>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <div></div>
                <flux:button type="submit" variant="primary">
                    <flux:icon.check class="size-4" />
                    Update Profile
                </flux:button>
            </div>
        </form>
    </div>

    <!-- Change Password -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Change Password
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Ensure your account is using a long, random password to stay secure.
            </p>
        </div>

        <form wire:submit.prevent="updatePassword" class="p-6 space-y-4">
            @if (session('password-message'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-green-700 dark:text-green-300">{{ session('password-message') }}</p>
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                <div>
                    <flux:input 
                        wire:model="currentPassword" 
                        id="currentPassword"
                        name="currentPassword"
                        type="password"
                        label="Current Password *"
                        placeholder="Enter your current password"
                        required
                        class="w-full"
                    />
                    <flux:error name="currentPassword"/>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <flux:input 
                            wire:model="newPassword" 
                            id="newPassword"
                            name="newPassword"
                            type="password"
                            label="New Password *"
                            placeholder="Create a new strong password"
                            required
                            class="w-full"
                        />
                        <flux:error name="newPassword"/>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Must be at least 6 characters with mixed case letters
                        </p>
                    </div>

                    <div>
                        <flux:input 
                            wire:model="newPasswordConfirmation" 
                            id="newPasswordConfirmation"
                            name="newPasswordConfirmation"
                            type="password"
                            label="Confirm New Password *"
                            placeholder="Confirm your new password"
                            required
                            class="w-full"
                        />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <div></div>
                <flux:button type="submit" variant="primary">
                    <flux:icon.key class="size-4" />
                    Update Password
                </flux:button>
            </div>
        </form>
    </div>

    <!-- User Settings -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Application Settings
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Customize your scanning and notification preferences.
            </p>
        </div>

        <form wire:submit.prevent="updateSettings" class="p-6 space-y-6">
            @if (session('settings-message'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-green-700 dark:text-green-300">{{ session('settings-message') }}</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600">
                        <div>
                            <label for="notifications" class="text-sm font-medium text-gray-700 dark:text-gray-200 cursor-pointer">
                                Email Notifications
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Receive email updates about your account activity
                            </p>
                        </div>
                        <flux:checkbox 
                            wire:model="notifications"
                            id="notifications"
                            name="notifications"
                        />
                    </div>

                    <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600">
                        <div>
                            <label for="darkMode" class="text-sm font-medium text-gray-700 dark:text-gray-200 cursor-pointer">
                                Dark Mode
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Use dark theme across the application
                            </p>
                        </div>
                        <flux:switch 
                            wire:model="darkMode"
                            id="darkMode"
                            name="darkMode"
                        />
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600">
                        <div>
                            <label for="autoSubmit" class="text-sm font-medium text-gray-700 dark:text-gray-200 cursor-pointer">
                                Auto-Submit Scans
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Automatically submit scans without confirmation
                            </p>
                        </div>
                        <flux:switch 
                            wire:model="autoSubmit"
                            id="autoSubmit"
                            name="autoSubmit"
                        />
                    </div>

                    <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600">
                        <div>
                            <label for="scanSound" class="text-sm font-medium text-gray-700 dark:text-gray-200 cursor-pointer">
                                Scan Sound Effects
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Play sound when scanning barcodes
                            </p>
                        </div>
                        <flux:switch 
                            wire:model="scanSound"
                            id="scanSound"
                            name="scanSound"
                        />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <div></div>
                <flux:button type="submit" variant="primary">
                    <flux:icon.cog class="size-4" />
                    Update Settings
                </flux:button>
            </div>
        </form>
    </div>

    <!-- Current Permissions & Roles -->
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Your Access Level
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                View your current roles and permissions. Contact an administrator to request changes.
            </p>
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Assigned Roles</h4>
                    <div class="space-y-2">
                        @forelse($userRoles as $role)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ ucfirst($role) }}
                            </span>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">No roles assigned</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Effective Permissions</h4>
                    <div class="max-h-40 overflow-y-auto space-y-1">
                        @forelse($userPermissions as $permission)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 mr-1 mb-1">
                                {{ ucwords(str_replace(['_', 'users', 'scans', 'products', 'invites'], [' ', 'user', 'scan', 'product', 'invite'], $permission)) }}
                            </span>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">No permissions assigned</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
