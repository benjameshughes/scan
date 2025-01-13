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
}
