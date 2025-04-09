<div>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <div class="flex items-center justify-between flex-column flex-wrap md:flex-row space-y-4 md:space-y-0 p-4 bg-white dark:bg-gray-900">
            <div class="flex grow justify-between">
                <div>
                    <!-- Dropdown menu -->
                    <flux:dropdown>
                        <flux:button icon:trailing="chevron-down">Options</flux:button>
                        <flux:menu>
                            <flux:menu.item>Something</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </div>

            <div>
                <flux:input wire:model="search" name="Search Users" />
            </div>
            </div>
        </div>
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="p-4">
                    <div class="flex items-center">
                        <input id="checkbox-all-search" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="checkbox-all-search" class="sr-only">checkbox</label>
                    </div>
                </th>
                <th scope="col" class="px-6 py-3">
                    Name
                </th>
                <th scope="col" class="px-6 py-3">
                    Email
                </th>
                <th scope="col" class="px-6 py-3">
                    Action
                </th>
            </tr>
            </thead>
            <tbody>
            @forelse($externalEmails as $email)
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                <td class="w-4 p-4">
                    <div class="flex items-center">
                        <input id="checkbox-table-search-1" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="checkbox-table-search-1" class="sr-only">checkbox</label>
                    </div>
                </td>
                <th scope="row" class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white">
                    <img class="w-10 h-10 rounded-full" src="https://ui-avatars.com/api/?name={{urlencode($email->name)}}" alt="Jese image">
                    <div class="ps-3">
                        <div class="text-base font-semibold">{{$email->name}}</div>
                    </div>
                </th>
                <td class="px-6 py-4">
                    {{$email->email}}
                </td>
                <td class="px-6 py-4">
                    <flux:button type="button" variant="primary" wire:click="edit('{$id}')">Edit user</flux:button>
                </td>
            </tr>
            @empty
            None
            @endforelse

            </tbody>
        </table>
    </div>

    <flux:modal.trigger>

    </flux:modal.trigger>

    <flux:modal name="editUser">
        Hi
    </flux:modal>

</div>