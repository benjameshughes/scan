<?php

namespace App\Jobs;

use App\Imports\ProductsImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Ramsey\Uuid\Fields\SerializableFieldsTrait;

class ImportFile implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue;

    public $file;
    public $import;

    /**
     * Create a new job instance.
     */
    public function __construct($file, ProductsImport $import)
    {
        $this->file = $file;
        $this->import = $import;
    }

    /**
     * Execute the job.
     * @throws \Exception
     */
    public function handle(): void
    {
        try{
            $import = new ProductsImport();
            $import->queue(Storage::path($this->file));
        } catch (\Exception $e) {
            Log::channel('import')->error($e->getMessage());
            throw $e;
        }
    }
}
