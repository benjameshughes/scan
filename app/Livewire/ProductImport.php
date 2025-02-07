<?php

namespace App\Livewire;

use App\Imports\ProductsImport;
use App\Jobs\ImportFile;
use Illuminate\Http\UploadedFile;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ProductImport extends Component
{
    use WithFileUploads;

    public $file;
    public $progress = 0;
    public $totalRows = 0;
    public $isImporting = false;

    protected $listeners = ['importComplete','updateProgress'];

    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $this->isImporting = true;

        // Store the file
        $path = $this->file->store('imports');

        $import = new ProductsImport();

//        // Get total rows
//        $results = Excel::toArray(new ProductsImport(), $this->file->getRealPath());
//        $this->totalRows = count($results[0]);
//        $import->setTotalRows($this->totalRows);

        // Dispatch the import job
        Excel::queue(new ProductsImport, $path);
    }

    public function getProgress()
    {
        $import = new ProductsImport();

        $this->progress = ($import->importedRows / $this->totalRows) * 100;
    }

    #[On('importComplete')]
    public function importComplete()
    {
        $this->isImporting = false;
        $this->progress = 0;
        $this->totalRows = 0;
        $this->importFinished = true;
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