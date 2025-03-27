<div>
    <flux:modal.trigger name="addEmail">
        <flux:button>Add Email</flux:button>
    </flux:modal.trigger>

    <flux:modal class="w-96" name="addEmail">
        <flux:heading size="lg">Add External Email</flux:heading>
        <flux:input label="Name" wire:model="name" placeholder="Name"/>
        <flux:input label="Email" wire:model="email" placeholder="Email Address"/>
        <flux:button type="button" wire:click="save">Add Email</flux:button>
    </flux:modal>

    <!-- External email list -->
    <ul role="list">
        @forelse($externalEmails as $email)
            <li class="flex justify-between gap-x-6 py-5">
                <div class="flex min-0-w gap-x-4">
                    <p class="text-sm/6 font-semibold text-gray-900">{{$email->name}}</p>
                    <p class="text-sm/6 font-semibold text-gray-900">{{$email->email}}</p>
                </div>
            </li>
        @empty
            No emails
        @endforelse
    </ul>
</div>