<div>
    {{-- Session flash message display --}}
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="updateRoles">
        <div class="mb-4">
            <flux:radio.group wire:model="selectedRole" name="Roles" label="Roles">
                @forelse($roles as $roleName => $roleLabel)
                <flux:radio
                        id="role_{{ $roleName }}" value="{{ $roleName }}"
                        label="{{ Str::ucfirst($roleLabel) }}"
                />
                @empty
                    <p class="text-gray-500">No roles available.</p>
                @endforelse
            </flux:radio.group>
            @error('selectedRole') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror

        </div>

        <div class="flex items-center space-x-4">
            <flux:button type="submit" variant="primary">Update Role</flux:button>
            <flux:button :href="route('admin.users.index')">Cancel</flux:button>
        </div>
    </form>
</div>
