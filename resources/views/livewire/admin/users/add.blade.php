<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Add New User</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Create a new user account and optionally send an invitation</p>
        </div>
        
        <form wire:submit="save" class="p-6">
            <flux:input.group name="addUser">
                <div class="space-y-4">
                    <div>
                        <flux:input 
                            name="name" 
                            label="Full Name" 
                            wire:model="name" 
                            placeholder="Enter full name"
                            required
                            class="w-full"
                        />
                    </div>
                    
                    <div>
                        <flux:input 
                            name="email" 
                            label="Email Address" 
                            type="email"
                            wire:model="email" 
                            placeholder="Enter email address"
                            required
                            class="w-full"
                        />
                    </div>
                    
                    <div>
                        <flux:select 
                            name="role" 
                            label="Role" 
                            wire:model="role"
                            required
                            class="w-full"
                        >
                            <option value="">Select a role...</option>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </flux:select>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex items-center space-x-3">
                            <flux:checkbox 
                                name="sendInvite" 
                                wire:model="sendInvite" 
                                label="Send invitation email"
                            />
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 ml-6">
                            User will receive an email to set up their account and choose a password
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between pt-6 mt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button 
                        variant="ghost" 
                        href="{{ route('admin.users.index') }}"
                        type="button"
                    >
                        Cancel
                    </flux:button>
                    
                    <flux:button 
                        type="submit" 
                        variant="primary"
                        class="ml-3"
                    >
                        {{ $sendInvite ? 'Create User & Send Invite' : 'Create User' }}
                    </flux:button>
                </div>
            </flux:input.group>
        </form>
    </div>
</div>
