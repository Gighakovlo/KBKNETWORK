<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\AssetHistory;

class AssetObserver
{
    public function updated(Asset $asset)
    {
        // Cek kolom apa saja yang berubah di tabel assets (selain updated_at dan kordinat peta)
        $changes = $asset->getDirty();
        
        foreach ($changes as $field => $newValue) {
            // Abaikan perubahan jika itu cuma update waktu atau pergeseran icon di peta
            if ($field == 'updated_at' || $field == 'pos_x' || $field == 'pos_y') continue;

            $oldValue = $asset->getOriginal($field);

            // Tembakkan log perubahannya ke tabel asset_histories
            AssetHistory::create([
                'asset_id' => $asset->id,
                'field_changed' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'changed_by' => 'Sistem / Admin' 
            ]);
        }
    }
}