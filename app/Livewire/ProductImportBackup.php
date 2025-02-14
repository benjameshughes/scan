<?php

namespace App\Livewire;

use App\Events\ImportedFile;
use App\Imports\ProductsImport;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ProductImportBackup extends Component
{
    use WithFileUploads;

    public $file;
    public $progress = 0;
    public $totalRows = 100;

    public $csvHeaders = [];
    public $isImporting = false;

    protected $listeners = ['echo:import-progress, ImportProgress' => 'updateProgress'];

    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $this->isImporting = true;

        // Store the file
        $path = $this->file->store('imports');

        $import = new ProductsImport();

        // Get total rows
        $results = Excel::toArray(new ProductsImport(), $this->file->getRealPath());
        $this->csvHeaders = $results[0][0];
        $this->totalRows = count($results[0]);
        $import->setTotalRows($this->totalRows);

        // Dispatch event
        ImportedFile::dispatch($this->progress);

        // Dispatch the import job
        $import->queue($path);
    }

    public function updateProgress($event)
    {
        $this->progress = $event->progress;
        if($this->progress == 100) {
            $this->isImporting = false;
        }
    }

    #[On('importComplete')]
    public function importComplete()
    {
        $this->progress = 0;
        $this->totalRows = 0;
        $this->isImporting = false;
    }

    public function render()
    {
        return view('livewire.product-import', [
            'progress' => $this->progress,
            'totalRows' => $this->totalRows,
            'isImporting' => $this->isImporting,
        ]);
    }
}