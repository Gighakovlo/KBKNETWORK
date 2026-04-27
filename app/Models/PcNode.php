<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcNode extends Model
{
    // Buka gembok
    protected $guarded = [];

    // Relasi ke Lantai
    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }
}