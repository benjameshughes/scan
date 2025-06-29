<div>
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg">
        <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">User Invitations</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage pending and accepted invitations</p>
                </div>
                <div class="flex gap-2">
                    <flux:button variant="primary" href="{{ route('admin.invites.create') }}">
                        Send New Invite
                    </flux:button>
                    <flux:button variant="ghost" href="{{ route('admin.invites.bulk') }}">
                        Bulk Invite
                    </flux:button>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <!-- Search and filters -->
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <flux:input 
                        wire:model.live.debounce.300ms="search" 
                        type="search" 
                        placeholder="Search by email or name..."
                        class="w-full"
                    />
                </div>
                <div class="flex gap-2">
                    <flux:select wire:model.live="perPage">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                    </flux:select>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            @foreach($columns as $column)
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    @if($column->sortable)
                                        <button 
                                            wire:click="sortBy('{{ $column->field }}')" 
                                            class="group inline-flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200"
                                        >
                                            {{ $column->label }}
                                            <span class="ml-1">
                                                @if($sortField === $column->field)
                                                    @if($sortDirection === 'asc')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    @endif
                                                @else
                                                    <svg class="w-4 h-4 opacity-0 group-hover:opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                                    </svg>
                                                @endif
                                            </span>
                                        </button>
                                    @else
                                        {{ $column->label }}
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse($items as $item)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                                @foreach($columns as $column)
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {!! $column->getValue($item) !!}
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) }}" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p class="text-lg font-medium mb-1">No invitations found</p>
                                        <p class="text-gray-400">{{ $search ? 'Try adjusting your search terms' : 'Send your first invite to get started' }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($items->hasPages())
                <div class="mt-6">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>