<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpAddress extends Model
{
    protected $guarded = []; // Buka semua gembok kolom

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}