<!-- Change Password -->
<div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
        <div class="flex items-center gap-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Change Password
            </h3>
            <x-action-message class="text-sm text-green-600 dark:text-green-400 flex items-center gap-1" on="password-updated">
                <flux:icon.check class="w-4 h-4" />
                Updated
            </x-action-message>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Ensure your account is using a long, random password to stay secure.
        </p>
    </div>

    <form wire:submit.prevent="updatePassword" class="p-6 space-y-4">

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