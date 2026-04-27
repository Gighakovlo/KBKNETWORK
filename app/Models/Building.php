<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    // Tambahkan 'polygon_points' di barisan ini:
    protected $fillable = ['name', 'pos_x', 'pos_y', 'polygon_points'];
    // Buka gembok
    protected $guarded = [];

    // Kasih tahu kalau 1 Gedung punya banyak Lantai
    public function floors()
    {
        return $this->hasMany(Floor::class);
    }
}