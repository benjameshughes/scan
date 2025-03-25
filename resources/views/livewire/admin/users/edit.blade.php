<div>
    <form wire:submit="updateRoles">
        <div class="mb-4">
{{--            <label class="block text-gray-700 text-sm font-bold mb-2">Roles</label>--}}
{{--            @foreach($roles as $role)--}}
{{--                <div class="flex items-center mb-2">--}}
{{--                    <input type="radio"--}}
{{--                           wire:model="userRoles"--}}
{{--                           value="{{ $role->name }}"--}}
{{--                           id="role-{{ $role->id }}"--}}
{{--                           {{in_array($role->name, $userRoles) ? 'checked' : ''}}--}}
{{--                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">--}}
{{--                    <label for="role-{{ $role->id }}" class="ml-2 text-sm text-gray-700">--}}
{{--                        {{ ucfirst($role->name) }}--}}
{{--                    </label>--}}
{{--                </div>--}}
{{--            @endforeach--}}

            <flux:radio.group wire:model="selectedRole" name="Roles" label="Roles">
                @foreach($roles as $role)
                    <flux:radio value="{{$role}}" label="{{$role}}" {{ $currentRole === $role ? 'checked' : '' }}/>
                @endforeach
            </flux:radio.group>

        </div>

        <div class="flex items-center">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Update Roles
            </button>
            <a href="{{ route('admin.users.index') }}"
               class="ml-4 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-300 focus:outline-none focus:border-gray-300 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Cancel
            </a>
        </div>
    </form>
</div>
