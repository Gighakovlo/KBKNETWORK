<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssetCategory;
use App\Models\CategoryField;

class AssetCategorySeeder extends Seeder
{
    public function run()
    {
        // 0. Sapu bersih data sisa dari percobaan gagal sebelumnya agar tidak bentrok
        CategoryField::query()->delete();
        AssetCategory::query()->delete();

        // 1. Kategori: Komputer / PC
        $pc = AssetCategory::create(['name' => 'Komputer', 'prefix' => 'PC']);
        CategoryField::insert([
            ['asset_category_id' => $pc->id, 'field_name' => 'Processor', 'input_type' => 'text', 'is_required' => true],
            ['asset_category_id' => $pc->id, 'field_name' => 'Kapasitas RAM', 'input_type' => 'text', 'is_required' => true],
            ['asset_category_id' => $pc->id, 'field_name' => 'Penyimpanan (Storage)', 'input_type' => 'text', 'is_required' => true],
            ['asset_category_id' => $pc->id, 'field_name' => 'Sistem Operasi', 'input_type' => 'text', 'is_required' => false],
            ['asset_category_id' => $pc->id, 'field_name' => 'Alamat IP (IPv4)', 'input_type' => 'text', 'is_required' => false],
        ]);

        // 2. Kategori: Monitor
        $mon = AssetCategory::create(['name' => 'Monitor', 'prefix' => 'MON']);
        CategoryField::insert([
            ['asset_category_id' => $mon->id, 'field_name' => 'Ukuran Layar (Inch)', 'input_type' => 'number', 'is_required' => true],
            ['asset_category_id' => $mon->id, 'field_name' => 'Resolusi Maksimal', 'input_type' => 'text', 'is_required' => false],
            ['asset_category_id' => $mon->id, 'field_name' => 'Jenis Panel (IPS/TN/VA)', 'input_type' => 'text', 'is_required' => false],
        ]);

        // 3. Kategori: Keyboard & Mouse (Aset Peripheral)
        $key = AssetCategory::create(['name' => 'Keyboard', 'prefix' => 'KEY']);
        CategoryField::create(['asset_category_id' => $key->id, 'field_name' => 'Tipe Koneksi (Wireless/USB)', 'input_type' => 'text', 'is_required' => false]);
        
        $mou = AssetCategory::create(['name' => 'Mouse', 'prefix' => 'MOU']);
        CategoryField::create(['asset_category_id' => $mou->id, 'field_name' => 'Tipe Koneksi (Wireless/USB)', 'input_type' => 'text', 'is_required' => false]);

        // 4. Kategori: Printer
        $prn = AssetCategory::create(['name' => 'Printer', 'prefix' => 'PRN']);
        CategoryField::insert([
            ['asset_category_id' => $prn->id, 'field_name' => 'Tipe Tinta/Laser', 'input_type' => 'text', 'is_required' => true],
            ['asset_category_id' => $prn->id, 'field_name' => 'Fitur Scanner (Ya/Tidak)', 'input_type' => 'text', 'is_required' => false],
            ['asset_category_id' => $prn->id, 'field_name' => 'Alamat IP (Jika Network Printer)', 'input_type' => 'text', 'is_required' => false],
        ]);

        // 5. Kategori: Networking (Switch, Modem, Router, AP)
        $swt = AssetCategory::create(['name' => 'Switch', 'prefix' => 'SWT']);
        CategoryField::insert([
            ['asset_category_id' => $swt->id, 'field_name' => 'Jumlah Port', 'input_type' => 'number', 'is_required' => true],
            ['asset_category_id' => $swt->id, 'field_name' => 'Manageable (Ya/Tidak)', 'input_type' => 'text', 'is_required' => false],
            ['asset_category_id' => $swt->id, 'field_name' => 'Alamat IP', 'input_type' => 'text', 'is_required' => false],
        ]);

        AssetCategory::create(['name' => 'Modem', 'prefix' => 'MDM']);
        AssetCategory::create(['name' => 'Router', 'prefix' => 'RTR']);
        AssetCategory::create(['name' => 'Access Point', 'prefix' => 'AP']);
    }
}