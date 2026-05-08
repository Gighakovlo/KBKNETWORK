<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpAddress extends Model
{
    protected $guarded = [];

    // Relasi: Satu IP Address menempel pada SATU Aset (bisa null)
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}