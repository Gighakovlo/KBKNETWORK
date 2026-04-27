<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackboneConnection extends Model
{
    protected $fillable = ['from_building_id', 'to_building_id', 'color'];

    public function fromBuilding() {
        return $this->belongsTo(Building::class, 'from_building_id');
    }

    public function toBuilding() {
        return $this->belongsTo(Building::class, 'to_building_id');
    }
}