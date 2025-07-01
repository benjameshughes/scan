<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $guarded = [
        'id',
        'created_at',
    ];

    protected $fillable = [
        'sku',
        'name',
        'barcode',
        'barcode_2',
        'barcode_3',
        'quantity',
        'linnworks_id',
        'last_synced_at',
        'auto_synced',
    ];

    public function scans()
    {
        // This is a bit complex because we need to match scans where the scan's barcode
        // matches any of this product's three barcode fields
        return Scan::where(function ($query) {
            $query->where('barcode', $this->barcode)
                ->when($this->barcode_2, function ($q) {
                    $q->orWhere('barcode', $this->barcode_2);
                })
                ->when($this->barcode_3, function ($q) {
                    $q->orWhere('barcode', $this->barcode_3);
                });
        });
    }
    
    /**
     * Get the pending updates for this product
     */
    public function pendingUpdates()
    {
        return $this->hasMany(PendingProductUpdate::class);
    }
}
