<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScanRequest;
use App\Http\Requests\UpdateScanRequest;
use App\Jobs\SyncBarcode;
use App\Models\Scan;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ScanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // Render the view
        return view('scan.list');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // Render the view
        return view('scan.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScanRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Scan $scan)
    {
        // Render the view
        return view('scan.view', ['scan' => $scan]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Scan $scan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScanRequest $request, Scan $scan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Scan $scan)
    {
        //
    }

    /**
     * Sync the scan with the barcode
     */
    public function sync(Scan $scan)
    {
        $jobId = Str::uuid();
        // Dispatch a syncBarcode job
        SyncBarcode::dispatch($scan)->delay(now()->addMinutes(1));

        // Ternery operator to set the status
        session()->flash('status', $jobId === $scan->job_id ? 'Syncing' : 'Synced');

        // Redirect to the scan view
        return redirect()->route('scan.show', $scan);
    }

    /**
     * Aggregate barcodes and sum quantities
     */
    public function aggregated(): View
    {
        $aggregatedScans = Scan::select('barcode', DB::raw('SUN(quantity as total_quantity'))
            ->groupBy('barcode')
            ->get();

        return view('scan.aggregated', compact('aggregatedScans'));
    }
}
