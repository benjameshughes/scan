<div>

    <div>
        <x-list :items="$scans" :itemName="'Scan'" :itemDescription="'Not submitted'" :routeName="'scan.show'"/>
    </div>
    {{ $scans->links() }}
</div>
