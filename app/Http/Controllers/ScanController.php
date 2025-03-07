<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScanRequest;
use App\Http\Requests\UpdateScanRequest;
use App\Jobs\SyncBarcode;
use App\Models\Product;
use App\Models\Scan;
use App\Services\LinnworksApiService;
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
        return view('scan.view', compact('scan'));
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
     * Show the scan form publicly
     */

    public function scan()
    {
        $layout = auth()->check() ? 'app' : 'guest';
        return view('scan.index')->layout('layouts.' . $layout);
    }
}
