<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetHistory extends Model
{
    // Membuka gembok mass assignment
    protected $guarded = [];

    // Relasi balik ke model Asset
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
