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

    // Kasih tahu kalau 1 Lantai punya banyak Switch
    public function switchNodes()
    {
        return $this->hasMany(SwitchNode::class);
    }

    // Tambahkan ini di bagian bawah sebelum kurung kurawal penutup
    public function pcNodes()
    {
        return $this->hasMany(PcNode::class);
    }
}