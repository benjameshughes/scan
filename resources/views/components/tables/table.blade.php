<div>
    <div class="space-y-4">
        @include('components.tables.table-header')

        <div class="overflow-hidden shadow-sm rounded-lg border-gray-200 border-1 flex">
            <table class="min-w-full divide-y divide-gray-300 flex-col">
                @include('components.tables.table-columns')
                @include('components.tables.table-rows')
            </table>
        </div>

        @include('components.tables.table-pagination')
    </div>
</div>