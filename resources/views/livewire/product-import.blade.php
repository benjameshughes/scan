@php
    $classes = 'mb-4 w-full p-10 bg-white border rounded shadow-sm dark:bg-gray border-gray-500/20 shadow-sm';
@endphp
<div>
    <div>
        @if ($step === 1)
            <div class="{{$classes}}">
                @error('csvFile') <span class="error">{{ $message }}</span> @enderror
                <flux:field class="flex justify-between items-center">
                    <flux:input type="file" wire:model="csvFile" label="Upload a file"/>
                    <flux:button variant="danger" type="button" wire:click="uploadFile">Upload</flux:button>
                </flux:field>
            </div>

        @elseif ($step === 2)
            <div class="{{$classes}}">
                <table class="w-full">
                    <thead>
                    <tr>
                        <th>File</th>
                        <th>Database</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($headers as $index => $header)
                        <tr>
                            <td>{{ $header }}</td>
                            <td>
                                <flux:select wire:model="mapping.{{$index}}">
                                    @foreach($modelColumns as $column)
                                        <flux:select.option value="{{ $column }}">{{ $column }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @error('mapping') <span class="error">{{ $message }}</span> @enderror
                <flux:button variant="primary" wire:click="import">Import Data</flux:button>
            </div>
        @elseif ($step === 3)
            <div class="{{$classes}}">
                <h2 class="text-2xl text-[var(--color-accent)] mb-4">Import Completed!</h2>
                <p>Your CSV data has been imported.</p>
            </div>
        @endif
    </div>
</div>