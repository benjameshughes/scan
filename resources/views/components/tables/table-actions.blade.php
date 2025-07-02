<div class="flex items-center justify-end gap-2">
    @foreach($actions as $action)
        @php
            // Determine default variant based on action type
            $defaultVariant = 'ghost';
            if (isset($action['type'])) {
                switch($action['type']) {
                    case 'edit':
                        $defaultVariant = 'primary';
                        break;
                    case 'delete':
                        $defaultVariant = 'danger';
                        break;
                    case 'view':
                        $defaultVariant = 'ghost';
                        break;
                    default:
                        $defaultVariant = 'ghost';
                }
            }
            
            // Check action name/label for additional hints
            $actionLabel = strtolower($action['label'] ?? '');
            if (str_contains($actionLabel, 'delete') || str_contains($actionLabel, 'remove')) {
                $defaultVariant = 'danger';
            } elseif (str_contains($actionLabel, 'edit') || str_contains($actionLabel, 'update')) {
                $defaultVariant = 'primary';
            } elseif (str_contains($actionLabel, 'create') || str_contains($actionLabel, 'add')) {
                $defaultVariant = 'primary';
            }
            
            // Use action's variant if specified, otherwise use default
            $variant = $action['variant'] ?? $defaultVariant;
            $size = $action['size'] ?? 'sm'; // Changed from 'xs' to 'sm' for larger buttons
            
            // Map common action icons to Flux/Heroicon equivalents
            $iconMap = [
                'pencil' => 'pencil',
                'edit' => 'pencil',
                'trash' => 'trash',
                'delete' => 'trash',
                'eye' => 'eye',
                'eye-slash' => 'eye-slash',
                'view' => 'eye',
                'toggle' => 'arrow-path',
                'sync' => 'arrow-path',
                'arrow-path' => 'arrow-path',
                'plus' => 'plus',
                'add' => 'plus',
                'minus' => 'minus',
                'remove' => 'minus',
                'check' => 'check',
                'x' => 'x-mark',
                'x-mark' => 'x-mark',
                'cog' => 'cog-6-tooth',
                'settings' => 'cog-6-tooth',
                'mail' => 'envelope',
                'email' => 'envelope',
                'download' => 'arrow-down-tray',
                'export' => 'arrow-down-tray',
                'upload' => 'arrow-up-tray',
                'import' => 'arrow-up-tray',
                'key' => 'key',
                'password' => 'key',
                'star' => 'star',
                'favorite' => 'star',
            ];
            
            $iconName = $action['icon'] ? ($iconMap[$action['icon']] ?? $action['icon']) : 'cog-6-tooth';
            $customClass = $action['class'] ?? '';
        @endphp

        @if($action['type'] === 'livewire' && isset($action['livewire_method']))
            {{-- Secure Livewire action using wire:click --}}
            @if(isset($action['confirm']))
                <flux:button
                    wire:click="{{ $action['livewire_method'] }}({{ $record->id }})"
                    wire:confirm="{{ $action['confirm'] }}"
                    wire:loading.attr="disabled"
                    wire:target="{{ $action['livewire_method'] }}({{ $record->id }})"
                    variant="{{ $variant }}"
                    size="{{ $size }}"
                    icon="{{ $iconName }}"
                    class="{{ $customClass }}"
                >
                    {{ $action['label'] }}
                </flux:button>
            @else
                <flux:button
                    wire:click="{{ $action['livewire_method'] }}({{ $record->id }})"
                    wire:loading.attr="disabled"
                    wire:target="{{ $action['livewire_method'] }}({{ $record->id }})"
                    variant="{{ $variant }}"
                    size="{{ $size }}"
                    icon="{{ $iconName }}"
                    class="{{ $customClass }}"
                >
                    {{ $action['label'] }}
                </flux:button>
            @endif
        @elseif($action['type'] === 'callback')
            {{-- Secure PHP callback action --}}
            @if(isset($action['confirm']))
                <flux:button
                    wire:click="executeCustomAction({{ $record->id }}, '{{ $action['action_id'] }}')"
                    wire:confirm="{{ $action['confirm'] }}"
                    wire:loading.attr="disabled"
                    wire:target="executeCustomAction({{ $record->id }}, '{{ $action['action_id'] }}')"
                    variant="{{ $variant }}"
                    size="{{ $size }}"
                    icon="{{ $iconName }}"
                    class="{{ $customClass }}"
                >
                    {{ $action['label'] }}
                </flux:button>
            @else
                <flux:button
                    wire:click="executeCustomAction({{ $record->id }}, '{{ $action['action_id'] }}')"
                    wire:loading.attr="disabled"
                    wire:target="executeCustomAction({{ $record->id }}, '{{ $action['action_id'] }}')"
                    variant="{{ $variant }}"
                    size="{{ $size }}"
                    icon="{{ $iconName }}"
                    class="{{ $customClass }}"
                >
                    {{ $action['label'] }}
                </flux:button>
            @endif
        @elseif(isset($action['url']) && $action['url'])
            {{-- URL-based action (links) - for external URLs only --}}
            @if(isset($action['confirm']))
                <flux:button
                    href="{{ $action['url'] }}"
                    wire:confirm="{{ $action['confirm'] }}"
                    variant="{{ $variant }}"
                    size="{{ $size }}"
                    icon="{{ $iconName }}"
                    class="{{ $customClass }}"
                >
                    {{ $action['label'] }}
                </flux:button>
            @else
                <flux:button
                    href="{{ $action['url'] }}"
                    variant="{{ $variant }}"
                    size="{{ $size }}"
                    icon="{{ $iconName }}"
                    class="{{ $customClass }}"
                >
                    {{ $action['label'] }}
                </flux:button>
            @endif
        @endif
    @endforeach
</div>