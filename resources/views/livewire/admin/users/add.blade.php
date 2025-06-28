<div>
    Add User

    <flux:input.group name="addUser">
        <flux:input name="name" label="Name" wire:model="name"/>
        <flux:input name="email" label="Email" wire:model="email"/>
        <flux:button type="submit" wire:click="save">Add User</flux:button>
    </flux:input.group>
</div>
