<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $guarded = [];

    // Relasi ke Kategori (Misal: PC-001 ini adalah 'Komputer')
    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    // Relasi ke Nilai Spesifikasi Dinamis (RAM: 16GB, dsb)
    public function values()
    {
        return $this->hasMany(AssetValue::class, 'asset_id');
    }

    // Relasi ke Lokasi Gedung
    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    // Relasi ke Lokasi Lantai
    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    // Relasi: Satu Aset bisa memiliki Satu IP Address dari tabel IPAM
    public function ipAddress()
    {
        return $this->hasOne(IpAddress::class, 'asset_id');
    }

    // Relasi: Satu Aset bisa memiliki banyak sejarah mutasi (pergerakan)
    public function movements()
    {
        return $this->hasMany(AssetMovement::class, 'asset_id')->orderBy('movement_date', 'desc');
    }

    // Relasi ke Tabel History Administrasi (Black Box Observer)
    public function histories()
    {
        return $this->hasMany(AssetHistory::class, 'asset_id');
    }
}