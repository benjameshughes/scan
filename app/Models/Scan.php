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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        $scanTable = $this->getTable();
        return $this->belongsTo(Product::class, 'barcode', 'barcode')
            ->orWhere(function ($query) use ($scanTable){
                $query->where('barcode_2', $this->barcode);
            })
            ->orWhere(function ($query) use ($scanTable){
                $query->where('barcode_3', $this->barcode);
            });
    }

    /**
     * Due to a SKU having multiple barcodes, we need to be able to get the SKU from one of the barcodes.
     * There is a barcode column, which is the primary barcode, but there is also a barcode_2, column which is the secondary barcode.
     * This will look up the barcode in the primary barcode column, and if it doesn't exist, it will look in the secondary barcode column, and return the SKU.
     */
    public function barcode()
    {
        return $this->belongsTo(Product::class, 'barcode', 'barcode')
            ->orWhere('barcode_2', '=', $this->barcode);
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
