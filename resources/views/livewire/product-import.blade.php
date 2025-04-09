@php
    $classes = 'px-6 py-4 rounded w-full bg-white dark:bg-gray-800';
@endphp
<div>
    @if ($step === 1)
        <div class="{{$classes}}">
            <flux:error class="mb-4" name="csvFile" message="You forgot to choose a file" />
            <div>
                <flux:field>
                    <div class="grid grid-cols-2 gap-4 items-center">
                        <flux:input type="file" wire:model="csvFile"/>
                        <flux:select wire:model.live="importAction" placeholder="Select what you want to do">
                            @foreach(\App\Enums\ImportTypes::toArray() as $importType)
                                <flux:select.option
                                        value="{{$importType['label']}}">{{$importType['label']}}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </flux:field>
            </div>
            <flux:button class="mt-4 justify-end" variant="danger" type="button" wire:click="uploadFile">Next
            </flux:button>

            <p class="text-sm text-gray-400 my-2 dark:text-white/60">10MB max filesize. CSV or Excel Files accepted</p>
        </div>

    @elseif ($step === 2)
        <div class="{{$classes}}">
            <table class="w-full text-sm text-left dark:text-gray-400">
                <thead class="text-sm text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr class="">
                    <th scope="col" class="px-6 py-3">File</th>
                    <th scope="col" class="px-6 py-3">Database</th>
                </tr>
                </thead>
                <tbody>
                @foreach($headers as $index => $header)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                        <th scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $header }}</th>
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

            @foreach($previewRows as $pr)
                {{ $pr['sku'] }}
            @endforeach

{{--            @error('mapping') <span class="error">{{ $message }}</span> @enderror--}}

            <flux:button variant="primary" wire:click="import">Import</flux:button>
        </div>

    @elseif ($step === 3)
        <div class="{{$classes}}">
            <h2 class="text-2xl text-[var(--color-accent)] mb-4">Import Completed!</h2>
            <p>{{$importCount}} rows imported</p>
            @unless($errorCount)
                @foreach($errors as $error)
                {{$error['message']}}
                @endforeach
            @endunless
        </div>
    @endif
</div>