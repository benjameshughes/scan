<?php

namespace App\Http\Controllers;

use App\Services\LinnworksApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LinnworksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get token from cache
        $token = Cache::get('linnworks.session_token');
        // Get profile view
        return view('linnworks.profile', compact('token'));
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
}
