<div class="p-4">
    <flux:text class="mb-4">
        <flux:heading size="lg">
            Complete registration
        </flux:heading>
        <flux:text>
            Set a password to complete registration to use the scanner app
        </flux:text>
    </flux:text>
    <flux:input wire:model="name" label="Name"/>
    <flux:input readonly variant="filled" wire:model="email" label="Email" />
    <flux:input type="password" wire:model="password" label="Password"/>
    <flux:input type="password" wire:model="password_confirmation" label="Confirm Password" />

    <flux:button type="button" wire:click="acceptInvite">Complete Registration</flux:button>
</div>
