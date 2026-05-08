<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $guarded = [];

    // Relasi: Satu Kategori punya BANYAK Field (Spesifikasi)
    public function fields()
    {
        return $this->hasMany(CategoryField::class, 'asset_category_id');
    }

    // Relasi: Satu Kategori punya BANYAK Barang (Aset)
    public function assets()
    {
        return $this->hasMany(Asset::class, 'asset_category_id');
    }
}