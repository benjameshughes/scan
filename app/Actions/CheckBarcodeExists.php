<?php

namespace App\Actions;

use App\Actions\Contracts\Action;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NoSkuFound;

final class CheckBarcodeExists implements Action
{
    public int $barcode;

    public function __construct(int $barcode)
    {
        $this->barcode = $barcode;
    }

    /*
     * Takes a barcode and checks if it exists in the database.
     * If it does, return the product.
     * If it doesn't, throw an exception.
     * @throws \Exception
     * @return Product
     */
    public function handle()
    {
        $product = Product::where('barcode', $this->barcode)->first();
        if (!$product) {
            // Notify users of no SKU found for a barcode
            $users = collect(User::all());
            $users->each(function ($user) {
                $user->notify(new NoSkuFound($this->barcode));
            });
        }
        return $product;
    }

}