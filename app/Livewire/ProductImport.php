<?php

namespace App\Livewire;

use App\Imports\ProductsImport;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ProductImport extends Component
{
    use WithFileUploads;

    public $file;
    public array $errors;

    public function import()
    {
        $this->validate([
            'file' => 'required|file|mimes:csv|max:10240',
        ]);

        $import = new ProductsImport();

        try{
            $import->import($this->file->getRealPath());
            session()->flash('message', 'Products imported');
        } catch(\Exception $e)
        {
            $this->errors[] = $e->getMessage();
            dd($e);

            // Flash the error message to the session
//            session()->flash('error', 'There was an error importing the file.');
        } finally {
            // Delete temp file
            unlink($this->file->getRealPath());
        }
    }

    public function render()
    {
        return view('livewire.product-import');
    }
}
