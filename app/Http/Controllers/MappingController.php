<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Floor;
use App\Models\Asset;
use App\Models\AssetCategory;

class MappingController extends Controller
{
    // 1. Tampilkan Halaman Micro Studio (Mapping)
    public function index($floor_id)
    {
        $floor = Floor::with('building')->findOrFail($floor_id);

        // Aset yang sudah diletakkan di kanvas
        $placedAssets = Asset::with(['category.fields', 'ipAddress', 'values.field'])
                             ->where('floor_id', $floor_id)
                             ->whereNotNull('pos_x')
                             ->whereNotNull('pos_y')
                             ->get();

        // Aset yang masih di Laci Gudang
        $unplacedAssets = Asset::with(['category.fields', 'ipAddress', 'values.field'])
                               ->where('floor_id', $floor_id)
                               ->whereNull('pos_x')
                               ->orderBy('created_at', 'desc')
                               ->get();

        $categories = AssetCategory::orderBy('name', 'asc')->get();

        return view('mapping', compact('floor', 'placedAssets', 'unplacedAssets', 'categories'));
    }

    // 2. Fitur Spawn Aset Baru Langsung dari Kanvas
    public function spawnAsset(Request $request)
    {
        // Validasi input dasar
        $request->validate([
            'floor_id' => 'required',
            'asset_category_id' => 'required',
            'name' => 'required|string|max:255',
        ]);

        try {
            // Cari data kategori untuk ambil Prefix
            $category = AssetCategory::findOrFail($request->asset_category_id);
            $prefix = $category->prefix;
            
            // Auto Generate Code
            $lastAsset = Asset::where('asset_code', 'like', $prefix . '-%')->orderBy('id', 'desc')->first();
            $newNumber = $lastAsset ? ((int) str_replace($prefix . '-', '', $lastAsset->asset_code)) + 1 : 1;
            $assetCode = $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            // Cari Building ID berdasarkan Floor ID (Gunakan Full Namespace agar Aman)
            $floor = \App\Models\Floor::find($request->floor_id);
            if (!$floor) {
                return response()->json(['success' => false, 'message' => 'Lantai tidak ditemukan!'], 404);
            }

            // Simpan Data Aset
            $asset = Asset::create([
                'asset_category_id' => $request->asset_category_id,
                'asset_code' => $assetCode,
                'name' => $request->name,
                'brand_model' => $request->brand_model,
                'current_user' => $request->current_user,
                'description' => $request->description,
                'installation_year' => $request->installation_year,
                'status' => $request->status ?? 'aktif',
                'floor_id' => $request->floor_id,
                'building_id' => $floor->building_id,
                'pos_x' => $request->pos_x,
                'pos_y' => $request->pos_y,
            ]);

            // SIMPAN IP ADDRESS (Logika Native Baru)
            if ($request->filled('ip_address')) {
                $ip = trim($request->ip_address);
                \App\Models\IpAddress::updateOrCreate(
                    ['ip_address' => $ip],
                    ['asset_id' => $asset->id, 'status' => 'in_use']
                );
            }

            // SIMPAN SPESIFIKASI DINAMIS (EAV)
            if ($request->has('dynamic_fields')) {
                foreach ($request->dynamic_fields as $fieldId => $value) {
                    if (!empty($value)) {
                        \App\Models\AssetValue::create([
                            'asset_id' => $asset->id,
                            'category_field_id' => $fieldId,
                            'value' => $value,
                        ]);
                    }
                }
            }

            // Muat ulang relasi agar JS di kanvas bisa langsung menggambar ikon & data lengkap
            $asset->load(['category.fields', 'ipAddress', 'values.field']);

            return response()->json(['success' => true, 'data' => $asset]);

        } catch (\Exception $e) {
            // Jika ada error, kirim pesan errornya agar Tuan bisa lihat di Console
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 3. Fitur Update Posisi Aset (Drag & Drop ke Kanvas)
    public function updatePosition(Request $request)
    {
        try {
            $asset = Asset::findOrFail($request->id);
            $asset->update([
                'pos_x' => $request->pos_x,
                'pos_y' => $request->pos_y,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 4. Fitur Cetak Laporan PDF Khusus Per Lantai
    public function printReport($floor_id)
    {
        $floor = \App\Models\Floor::with(['building', 'assets.category', 'assets.ipAddress'])->findOrFail($floor_id);
        return view('print-report', compact('floor'));
    }
}