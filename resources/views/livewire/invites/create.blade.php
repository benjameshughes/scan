<div class="space-y-6">
    <div class="grid grid-cols-2 gap-4">
        <flux:input wire:model="name" label="Name" icon="user"/>
        <flux:input wire:model="email" label="Email" icon="mail"/>
    </div>
    <div class="flex gap-2 justify-end">
        <flux:button icon="send" type="button" variant="primary" wire:click="create">Invite User</flux:button>
        <flux:button type="button" variant="danger" href="{{ route('admin.users.index') }}">Cancel</flux:button>
    </div>
</div>
