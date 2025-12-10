<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScanRequest;
use App\Http\Requests\UpdateScanRequest;
use App\Models\Scan;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use function class_exists;

class ScanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Scan::class);

        // Render the view
        return view('scan.list');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // $this->authorize('create', Scan::class);
        // Redirect to refactored scanner route (feature-gated)
        return redirect()->route('scanner.refactored');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScanRequest $request)
    {
        $this->authorize('create', Scan::class);

        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Scan $scan)
    {
        $this->authorize('view', $scan);

        // Render the view
        return view('scan.view', compact('scan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Scan $scan)
    {
        $this->authorize('update', $scan);

        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScanRequest $request, Scan $scan)
    {
        $this->authorize('update', $scan);

        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Scan $scan)
    {
        $this->authorize('delete', $scan);

        //
    }

    /**
     * Show the scan form for authenticated users.
     * Shows refactored scanner if feature flag is active, otherwise legacy scanner.
     */
    public function scan()
    {
        $this->authorize('viewAny', Scan::class);

        // Check if Pennant feature flag is active for this user
        $useRefactored = false;
        if (class_exists(\Laravel\Pennant\Feature::class)) {
            $useRefactored = \Laravel\Pennant\Feature::for(Auth::user())->active('scanner_refactor');
        }

        if ($useRefactored) {
            return view('scanner-refactored');
        }

        // Default to legacy scanner
        return view('scan.index');
    }
}
