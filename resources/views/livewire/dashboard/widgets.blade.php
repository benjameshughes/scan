<div>
    <div class="flex grow gap-4 max-sm:flex-col max-sm:gap-2">
        <x-widget :icon="'refresh-ccw'" :title="'Pending'" :stat="$scans->where('submitted', false)->count()"/>
        <x-widget :title="'Completed'" :stat="$scans->where('submitted', true)->count()"/>
        <x-widget :title="'Scans this Week'" :stat="$scans->whereBetween('created_at', [now()->subWeek(), now()])->count()"/>
    </div>
</div>
