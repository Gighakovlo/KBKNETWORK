<?php

namespace App\Observers;
use App\Models\AssetValue;
use App\Models\AssetHistory;

class AssetValueObserver
{
    public function updated(AssetValue $assetValue)
    {
        // Jika nilainya (value) berubah
        if ($assetValue->isDirty('value')) {
            $fieldName = $assetValue->field->field_name ?? 'Spesifikasi Dinamis';
            
            AssetHistory::create([
                'asset_id' => $assetValue->asset_id,
                'field_changed' => $fieldName,
                'old_value' => $assetValue->getOriginal('value'),
                'new_value' => $assetValue->value,
                'changed_by' => 'Sistem / Admin'
            ]);
        }
    }
}