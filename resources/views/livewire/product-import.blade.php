<div>
    <form wire:submit.prevent="import">
        <input type="file" wire:model="file">

        @error('file') <span class="text-danger">{{ $message }}</span> @enderror

        <button type="submit" class="bg-green-500 hover:bg-green-700 px-4 py-2 rounded-md text-white">Import</button>
    </form>

    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if ($progress > 0 && ! $importFinished)
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">{{ $progress }}%</div>
        </div>
    @endif

    @if ($isImporting)
        <div>
            Import queued. This may take a few minutes.
        </div>

        <div wire:poll.500ms="progress" wire:target="import">
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">{{ $progress }}%</div>
            </div>
        </div>
    @endif


</div>