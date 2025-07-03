<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scan extends Model
{
    /** @use HasFactory<\Database\Factories\ScanFactory> */
    use HasFactory;

    protected $guarded = [
        'id',
        'created_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        // Standard belongsTo relationship for primary barcode
        return $this->belongsTo(Product::class, 'barcode', 'barcode');
    }

    /**
     * Get the product by checking all barcode fields
     */
    public function getProductAttribute()
    {
        // Check if we already have a cached product
        if (isset($this->relations['product']) && $this->relations['product']) {
            return $this->relations['product'];
        }

        // Search for product by checking all barcode fields
        $product = Product::where('barcode', $this->barcode)
            ->orWhere('barcode_2', $this->barcode)
            ->orWhere('barcode_3', $this->barcode)
            ->first();

        // Cache the result
        $this->setRelation('product', $product);

        return $product;
    }

    /**
     * Get the formatted date for humans.
     * If the year is not this year, return the date in the format of "MMM d, YYYY".
     */
    public function dateForHumans()
    {
        return $this->created_at->format(
            $this->created_at->year === now()->year ? 'MMM d, h:mm a' : 'MMM d, YYYY'
        );
    }
}
