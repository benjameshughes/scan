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