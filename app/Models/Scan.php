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
        // This creates a custom relationship that checks all barcode columns
        return $this->belongsTo(Product::class, 'barcode', 'barcode')
            ->orWhere(function($query) {
                $query->where('barcode_2', $this->barcode)
                      ->orWhere('barcode_3', $this->barcode);
            });
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
