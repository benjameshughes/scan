<div class="p-3 bg-zinc-50 dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600">
    <div class="mb-3">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
            Vibration Pattern
        </label>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
            Choose the vibration pattern for barcode scanning feedback
        </p>
    </div>
    <div class="grid grid-cols-2 gap-3">
        @foreach(\App\Enums\VibrationPattern::cases() as $pattern)
            <label class="cursor-pointer" title="{{ $pattern->label() }} - {{ $pattern->description() }}">
                <input 
                    type="radio" 
                    name="vibrationPattern" 
                    value="{{ $pattern->value }}" 
                    wire:model.live="vibrationPattern"
                    class="sr-only"
                />
                <div class="p-3 rounded-lg border-2 transition-all duration-200 text-center
                    {{ $vibrationPattern === $pattern->value 
                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 ring-2 ring-blue-500/20' 
                        : 'border-zinc-200 dark:border-zinc-600 hover:border-zinc-300 dark:hover:border-zinc-500 bg-white dark:bg-zinc-800' }}
                ">
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-8 h-8 rounded-full {{ $pattern->cssClasses() }} flex items-center justify-center text-white text-lg">
                            {{ $pattern->icon() }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $pattern->label() }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $pattern->description() }}
                            </p>
                        </div>
                    </div>
                </div>
            </label>
        @endforeach
    </div>
</div>