<div class="w-full">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12V6a4 4 0 00-8 0v6M12 18v2m-6-6h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Invite New User</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Send an invitation to create a new user account</p>
                </div>
            </div>
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
                    
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <div class="flex items-start space-x-3">
                            <flux:checkbox 
                                name="sendInvite" 
                                wire:model="sendInvite" 
                                label="Send invitation email"
                                class="mt-0.5"
                            />
                            <div class="flex-1">
                                <p class="text-sm text-blue-900 dark:text-blue-100 font-medium">
                                    Recommended: Send invitation email
                                </p>
                                <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                                    The user will receive an email with a secure link to activate their account and set their password. This is the preferred way to create new users.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between pt-6 mt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button 
                        variant="ghost" 
                        href="{{ route('users.index') }}"
                        wire:navigate
                        type="button"
                    >
                        Cancel
                    </flux:button>
                    
                    <flux:button 
                        type="submit" 
                        variant="primary"
                        class="ml-3"
                        icon="paper-airplane"
                    >
                        {{ $sendInvite ? 'Send Invitation' : 'Create User (No Invite)' }}
                    </flux:button>
                </div>
            </flux:input.group>
        </form>
    </div>
</div>
