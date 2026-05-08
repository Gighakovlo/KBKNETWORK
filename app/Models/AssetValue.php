<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetValue extends Model
{
    protected $guarded = [];

    // Nilai ini milik Aset siapa?
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    // Nilai ini menjawab Field apa? (Misal: Field 'Processor')
    public function field()
    {
        return $this->belongsTo(CategoryField::class, 'category_field_id');
    }
}
