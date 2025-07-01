<?php

namespace App\Http\Controllers;

use App\Jobs\FetchLinnworksInvetory;
use App\Models\Product;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Cache;

class LinnworksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get token from cache
        $token = Cache::get(config('linnworks.cache.session_token_key'));
        // Get profile view
        $products = Product::all();

        return view('linnworks.inventory', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(LinnworksApiService $linnworks, $stock_id)
    {
        // Get the object from the Linnworks API, decode it to json, pass it to the view
        $data = $linnworks->getStockDetails($stock_id);

        return view('linnworks.show', ['data' => $data]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        //
    }

    /**
     * Get the inventory from Linnworks
     */
    public function fetchInventory(LinnworksApiService $linnworks)
    {
        (int) $entriesPerPage = 200;
        (int) $totalItems = $linnworks->getInventoryCount();
        $totalPages = ceil($totalItems / $entriesPerPage);

        // Dispatch a job for each page to fetch the inventory
        for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++) {
            sleep(1);
            FetchLinnworksInvetory::dispatch($pageNumber, $entriesPerPage)->delay(now()->addMinute());
        }

        return redirect()->route('linnworks.inventory')->with('success', 'Inventory refreshed');
    }
}
