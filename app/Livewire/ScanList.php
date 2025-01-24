<?php

namespace App\Livewire;

use App\Models\Scan;
use Livewire\Component;
use Livewire\WithPagination;

class ScanList extends Component
{
    use WithPagination;
    public int $perPage = 10;
    public $search = '';
    public $sortField = 'barcode';
    public $sortDirection = 'asc';
    protected $queryString = ['search', 'sortField', 'sortDirection'];
    public array $perPageOptions = [10, 25, 50, 100];
    public function sortBy($field)
    {
        if ($this->sortField = $field)
        {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

        } else {
            $this->sortDirection = 'asc;';
        }
    }

    public function render()
    {

        $scans = Scan::search(['barcode', 'submitted'], $this->search)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.scan-list',[
            'scans' => $scans,
            'actions' => $this->getActions(),
            'columns' => $this->getColumns(),
            'perPageOptions' => $this->perPageOptions,
            'sortDirection' => $this->sortDirection,
        ]);
    }

    private function getActions()
    {
        return [
            ['url' => route('scan.show', ''), 'label' => 'View', 'button-colour' => 'blue'],
            ['url' => route('scan.edit', '' ), 'label' => 'Edit', 'button-colour' => 'orange'],
        ];
    }

    private function getColumns()
    {
        return [
            ['key' => 'barcode', 'label' => 'Barcode'],
            ['key' => 'submitted', 'label' => 'Submitted'],
            ['key' => 'submitted_at', 'label' => 'Submit Date'],
            ['key' => 'quantity', 'label' => 'Quantity'],
        ];
    }
}
