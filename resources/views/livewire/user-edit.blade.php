<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Edit User: {{ $user->name }}
                </h3>
                <div class="flex space-x-3">
                    <button wire:click="cancel" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-600">
                        Cancel
                    </button>
                    <button wire:click="save" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 dark:bg-blue-700 rounded-md hover:bg-blue-700 dark:hover:bg-blue-600">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="save" class="space-y-6 p-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        Full Name
                    </label>
                    <input type="text" 
                           wire:model="name" 
                           id="name"
                           class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        Email Address
                    </label>
                    <input type="email" 
                           wire:model="email" 
                           id="email"
                           class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        Status
                    </label>
                    <select wire:model="status" 
                            id="status"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-md bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    @error('status') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            @if($canEditPermissions)
            <!-- Roles Section -->
            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6">
                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Roles</h4>
                <div class="space-y-2">
                    @foreach($allRoles as $role)
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   wire:click="toggleRole('{{ $role->name }}')"
                                   @if(in_array($role->name, $selectedRoles)) checked @endif
                                   class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-900 dark:text-gray-100 font-medium capitalize">{{ $role->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Roles provide predefined sets of permissions. Admin role grants all permissions.
                </p>
            </div>

            <!-- Permissions Section -->
            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6">
                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Fine-Grained Permissions</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Individual permissions override role-based permissions. Use these for fine-grained control.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($permissionGroups as $groupName => $permissions)
                        <div class="bg-zinc-50 dark:bg-zinc-700 rounded-lg p-4">
                            <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                @switch($groupName)
                                    @case('Users')
                                        <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                        </svg>
                                        @break
                                    @case('Scans')
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"/>
                                        </svg>
                                        @break
                                    @case('Products')
                                        <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                        @break
                                    @case('Invitations')
                                        <svg class="w-4 h-4 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        @break
                                @endswitch
                                {{ $groupName }}
                            </h5>
                            <div class="space-y-2">
                                @foreach($permissions as $permission => $description)
                                    <label class="flex items-start">
                                        <input type="checkbox" 
                                               wire:click="togglePermission('{{ $permission }}')"
                                               @if(in_array($permission, $selectedPermissions)) checked @endif
                                               class="rounded border-gray-300 dark:border-zinc-600 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 mt-0.5">
                                        <div class="ml-2">
                                            <span class="text-sm text-gray-900 dark:text-gray-100 font-medium">{{ ucwords(str_replace(['_', 'users', 'scans', 'products', 'invites'], [' ', 'user', 'scan', 'product', 'invite'], $permission)) }}</span>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $description }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Current Permission Summary -->
            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6">
                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Current Access Summary</h4>
                <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <strong>Roles:</strong> 
                                @if(empty($selectedRoles))
                                    No roles assigned
                                @else
                                    {{ implode(', ', array_map('ucfirst', $selectedRoles)) }}
                                @endif
                            </p>
                            <p class="text-sm text-blue-800 dark:text-blue-200 mt-1">
                                <strong>Individual Permissions:</strong> 
                                @if(empty($selectedPermissions))
                                    No individual permissions
                                @else
                                    {{ count($selectedPermissions) }} permission{{ count($selectedPermissions) === 1 ? '' : 's' }} selected
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
