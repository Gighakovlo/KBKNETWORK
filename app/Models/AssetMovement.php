<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetMovement extends Model
{
    protected $guarded = [];

    // Relasi: Sejarah mutasi ini milik satu aset tertentu
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}