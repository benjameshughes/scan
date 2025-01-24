<div>
    <form wire:submit="import" enctype="multipart/form-data">
        <div class="col-span-full">
            <label for="file" class="block text-sm/6 font-medium text-gray-700">Upload a spreadsheet of product data</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600">
                        <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                            <span>Upload a file</span>
                            <input id="file" name="file" type="file" class="sr-only" wire:model="file">
                        </label>
                        <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">
                        Excel, CSV, XLSX
                    </p>
                </div>
            </div>
        </div>

        @if ($file)
            <div class="my-2">
                <p class="text-sm/6 font-medium text-green-700">File selected: {{ $file->getClientOriginalName() }}</p>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">Import Products</button>
        @endif

    </form>

    @if (session()->has('message'))
        <div class="mt-4 p-4 bg-green-200 text-green-800 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if(count($errors) > 0)
        <div class="mt-4 p-4 bg-red-100 text-red-700 rounded">
            <ul>
                @foreach($errors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
