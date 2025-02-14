<div>
    <div>
        {{--    <form wire:submit.prevent="import">--}}
        {{--        <input type="file" wire:model="file">--}}

        {{--        @error('file') <span class="text-danger">{{ $message }}</span> @enderror--}}

        {{--        <button type="submit" class="bg-green-500 hover:bg-green-700 px-4 py-2 rounded-md text-white">Import</button>--}}
        {{--    </form>--}}

        {{--    @if (session()->has('message'))--}}
        {{--        <div class="alert alert-success">--}}
        {{--            {{ session('message') }}--}}
        {{--        </div>--}}
        {{--    @endif--}}

        {{--    @if ($progress > 0 && ! $importFinished)--}}
        {{--        <div class="progress">--}}
        {{--            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">{{ $progress }}%</div>--}}
        {{--        </div>--}}
        {{--    @endif--}}

        {{--    @if ($isImporting)--}}
        {{--        <div>--}}
        {{--            Import queued. This may take a few minutes.--}}
        {{--        </div>--}}

        {{--        <div>--}}
        {{--            {{$totalRows}} rows--}}
        {{--        </div>--}}
        {{--    <div>--}}
        {{--        @foreach($csvHeaders as $key => $value)--}}
        {{--            <p>{{$key}}</p>--}}
        {{--            @endforeach--}}
        {{--    </div>--}}

        {{--        <div wire:poll.500ms="progress" wire:target="import">--}}
        {{--            <div class="progress">--}}
        {{--                <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">{{ $progress }}%</div>--}}
        {{--            </div>--}}
        {{--        </div>--}}
        {{--    @endif--}}
        {{--    @if ($progress > 0 && $isImporting)--}}
        {{--        <div class="progress">--}}
        {{--            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">{{ $progress }}%</div>--}}
        {{--        </div>--}}
        {{--    @endif--}}

        {{--    @if (!$isImporting && $progress == 100)--}}
        {{--        <div class="alert alert-success">--}}
        {{--            Import finished!--}}
        {{--        </div>--}}
        {{--    @endif--}}
    </div>
    <div>
        @if ($step === 1)
            <h2>Step 1: Upload CSV File</h2>
            <input type="file" wire:model="csvFile">
            @error('csvFile') <span class="error">{{ $message }}</span> @enderror
            <button wire:click="uploadFile">Upload & Process</button>
        @elseif ($step === 2)
            <h2>Step 2: Map CSV Headers</h2>
            <p>Please map CSV columns to model fields:</p>
            <table border="1" cellpadding="5">
                <thead>
                <tr>
                    <th>CSV Column</th>
                    <th>Header</th>
                    <th>Map To (Model Field)</th>
                </tr>
                </thead>
                <tbody>
                @foreach($headers as $index => $header)
                    <tr>
                        <td>{{ $index }}</td>
                        <td>{{ $header }}</td>
                        <td>
                            <select wire:model="mapping.{{ $index }}">
                                <option value="">-- Skip --</option>
                                @foreach($modelColumns as $column)
                                    <option value="{{ $column }}">{{ $column }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @error('mapping') <span class="error">{{ $message }}</span> @enderror
            <button wire:click="import">Import Data</button>
        @elseif ($step === 3)
            <h2>Import Completed!</h2>
            <p>Your CSV data has been imported.</p>
        @endif
    </div>
</div>