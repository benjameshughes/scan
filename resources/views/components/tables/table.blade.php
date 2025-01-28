<div>
    <div class="space-y-4">
        @include('components.tables.table-header')

        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                @include('components.tables.table-columns')
                @include('components.tables.table-rows')
            </table>
        </div>

        @include('components.tables.table-pagination')
    </div>
</div>