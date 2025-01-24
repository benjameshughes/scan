<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

//    protected $guarded = [
//        'id',
//        'created_at',
//    ];

    protected $fillable = [
        'sku',
        'name',
        'barcode',
        'quantity',
    ];

    public function scans()
    {
        return $this->belongsToMany(Scan::class, 'scans', 'barcode', 'id');
    }
}
