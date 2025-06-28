<div>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <div class="flex items-center justify-between flex-column flex-wrap md:flex-row space-y-4 md:space-y-0 p-4 bg-white dark:bg-gray-900">
            <div class="flex grow justify-between">
                <div>
                    <!-- Dropdown menu -->
                    <flux:dropdown>
                        <flux:button icon:trailing="chevron-down">Actions</flux:button>
                        <flux:menu>
                            <flux:menu.item wire:click="bulkDelete">Delete</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>

                    <flux:modal.trigger name="addUser">
                        <flux:button type="button" variant="primary">
                            Add Email
                        </flux:button>
                    </flux:modal.trigger>
                </div>

                <div>
                    <flux:input wire:model="search" name="Search Users"/>
                </div>
            </div>
        </div>
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="p-4">
                    <div class="flex items-center">
                        <flux:checkbox wire:model.live="selectAll" />
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
                <tr wire:key="{{$email->id}}"
                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-500dd dark:hover:bg-gray-700">
                    <td class="w-4 p-4">
                        <div class="flex items-center">
                            <flux:checkbox variant="inline" wire:model.live="selectedItems" value="{{$email->id}}" />
                        </div>
                    </td>
                    <th scope="row" class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white">
                        <img class="w-10 h-10 rounded-full"
                             src="https://ui-avatars.com/api/?name={{urlencode($email->name)}}" alt="{{$email->name}}">
                        <div class="ps-3">
                            <div class="text-base font-semibold">{{$email->name}}</div>
                        </div>
                    </th>
                    <td class="px-6 py-4">
                        {{$email->email}}
                    </td>
                    <td class="px-6 py-4">

                        <flux:modal.trigger name="editUser">
                            <flux:button type="button" variant="primary" wire:click="edit('{{$email->id}}')">
                                Edit
                            </flux:button>
                        </flux:modal.trigger>

                        <flux:button type="button" variant="danger" wire:click="delete('{{$email->id}}')">
                            Delete
                        </flux:button>

                    </td>
                </tr>

            @empty
                None
            @endforelse
            </tbody>
        </table>
    </div>


    <flux:modal name="editUser" class="w-3/4 p-10 space-x-4">
        <div class="space-y-4">
            <flux:heading size="lg">Editing User {{$user->name ?? ''}}</flux:heading>
            <flux:input wire:model="name" label="Name"/>
            <flux:input wire:model="email" label="Email"/>
            <flux:button wire:click="save">Edit User</flux:button>
        </div>
    </flux:modal>

    <flux:modal name="addUser" class="w-3/4 p-10">
        <div class="space-y-4">
            <flux:heading size="lg">Add a new user</flux:heading>
            <flux:input wire:model="name" label="Name"/>
            <flux:input wire:model="email" label="Email"/>
            <flux:button type="button" kbd="enter" wire:keydown.enter="save" wire:click="save">Add User</flux:button>
        </div>
    </flux:modal>

    <flux:modal name="confirmDeleteUser">
    Delete?
    </flux:modal>
    <x-action-message on="user-updated">
        Email has been updated
    </x-action-message>
</div>