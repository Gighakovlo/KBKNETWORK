<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryField extends Model
{
    protected $guarded = [];

    // Relasi balik ke Kategori Induk
    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }
}