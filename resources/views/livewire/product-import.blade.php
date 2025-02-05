<div>
    <form wire:submit="import">
        <div class="bg-white border border-r border-gray-200 p-8 rounded-lg">
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file"></label>
            <input id="file" type="file" class="block text-sm text-gray-900 border rounded" wire:model="file">
            @error('file') <span class="error">{{ $message }}</span> @enderror
            @if($file)
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Uploaded file: {{ $file->getClientOriginalName() }}
                </p>
            @endif
        </div>

        @if($file)
            <div>
                @foreach ($availableColumns as $column)
                    <div class="form-group">
                        <label for="{{ $column }}">{{ ucfirst($column) }}</label>
                        <select wire:model="mappings.{{ $column }}" id="{{ $column }}" class="form-control">
                            <option value="">Select a column</option>
                            @foreach($fileColumns as $fileColumn)
                                <option value="{{ $fileColumn }}">{{ $fileColumn }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="bg-green-700 px-4 py-2 rounded-md text-white">Import</button>
            <div wire:loading wire:target="import" class="bg-gray-200 px-4 py-2 rounded-md text-white">
                <x-icons.spinner class="w-5 h-5 text-white animate-spin"/>
            </div>
        @endif
    </form>

    @if(!empty($results))
        <div>
            <p>Created: {{ $results['created'] }}</p>
            <p>Updated: {{ $results['updated'] }}</p>
            <p>Failed: {{ $results['failed'] }}</p>
        </div>
    @endif
</div>