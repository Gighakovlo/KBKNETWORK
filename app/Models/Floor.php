<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    // Buka gembok
    protected $guarded = [];

    // Kasih tahu kalau Lantai ini milik sebuah Gedung
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

   // Relasi Baru: Satu lantai memiliki banyak aset (The Great Merge)
    public function assets()
    {
        return $this->hasMany(\App\Models\Asset::class, 'floor_id');
    }
}