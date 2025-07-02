<div class="w-full">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Edit User</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Update user information and role assignments</p>
        </div>
        
        <form wire:submit.prevent="updateUser" class="p-6 space-y-4">
            {{-- Session flash message display --}}
            @if (session()->has('message'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-green-700 dark:text-green-300">{{ session('message') }}</p>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-4a1 1 0 112 0 1 1 0 01-2 0zm1-9a1 1 0 00-1 1v4a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <flux:input 
                        name="name" 
                        wire:model="form.name" 
                        label="Full Name *"
                        placeholder="Enter full name"
                        required
                        class="w-full"
                    />
                    <flux:error name="form.name"/>
                </div>
                
                <div>
                    <flux:input 
                        name="email" 
                        wire:model="form.email"
                        type="email"
                        label="Email Address *"
                        placeholder="Enter email address"
                        required
                        class="w-full"
                    />
                    <flux:error name="form.email"/>
                </div>
            </div>

            <div>
                <flux:input 
                    type="password" 
                    name="password" 
                    wire:model="form.password" 
                    label="New Password"
                    placeholder="Leave blank to keep current password"
                    viewable
                    class="w-full"
                />
                <flux:error name="form.password"/>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Only enter a password if you want to change it
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">
                    User Role <span class="text-red-500">*</span>
                </label>
                <flux:radio.group wire:model="selectedRole" name="Roles">
                    @forelse($roles as $roleName => $roleLabel)
                        <flux:radio
                            id="role_{{ $roleName }}" 
                            value="{{ $roleName }}"
                            label="{{ Str::ucfirst($roleLabel) }}"
                        />
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No roles available.</p>
                    @endforelse
                </flux:radio.group>
                <flux:error name="selectedRole"/>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">
                    User Permissions
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    Grant specific permissions to this user. Individual permissions can be configured regardless of role.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($allPermissions as $category => $permissions)
                        <div class="space-y-2">
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 capitalize border-b border-zinc-200 dark:border-zinc-600 pb-1">
                                {{ str_replace('_', ' ', $category) }}
                            </h4>
                            @foreach($permissions as $permission)
                                <div class="flex items-center justify-between p-2 bg-zinc-50 dark:bg-zinc-700 rounded border border-zinc-200 dark:border-zinc-600">
                                    <div class="flex-1">
                                        <label for="permission_{{ $permission }}" class="text-xs font-medium text-gray-700 dark:text-gray-200 cursor-pointer">
                                            {{ ucwords(str_replace(['_', 'users', 'scans', 'products', 'invites'], [' ', 'user', 'scan', 'product', 'invite'], $permission)) }}
                                        </label>
                                    </div>
                                    <flux:checkbox 
                                        wire:model="userPermissions.{{ $permission }}"
                                        id="permission_{{ $permission }}"
                                        name="permission_{{ $permission }}"
                                    />
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <flux:error name="userPermissions"/>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button 
                    variant="ghost" 
                    href="{{ route('users.index') }}"
                    wire:navigate
                    type="button"
                >
                    Cancel
                </flux:button>
                
                <flux:button type="submit" variant="primary" class="ml-3" wire:loading.attr="disabled" wire:target="updateUser">
                    <span wire:loading.remove wire:target="updateUser">
                        <flux:icon.check class="size-4" />
                        Update User
                    </span>
                    <span wire:loading wire:target="updateUser" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Updating...
                    </span>
                </flux:button>
            </div>
        </form>
    </div>
</div>
