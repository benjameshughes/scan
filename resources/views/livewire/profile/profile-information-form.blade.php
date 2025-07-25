<!-- Profile Information -->
<div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
        <div class="flex items-center gap-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Profile Information
            </h3>
            <x-action-message class="text-sm text-green-600 dark:text-green-400 flex items-center gap-1" on="profile-updated">
                <flux:icon.check class="w-4 h-4" />
                Updated
            </x-action-message>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Update your account's profile information and email address.
        </p>
    </div>

    <form wire:submit.prevent="updateProfile" class="p-6 space-y-4">

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