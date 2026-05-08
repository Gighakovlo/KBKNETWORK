<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetCategory;
use App\Models\CategoryField;
use App\Models\Building;
use App\Models\Floor;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    // 1. Halaman Utama Master Settings
    public function index()
    {
        $categories = AssetCategory::with('fields')->get();
        // Mengambil gedung beserta lantainya
        $buildings = Building::with('floors')->get();
        
        return view('inventory.settings', compact('categories', 'buildings'));
    }

    // 2. Tambah Kategori Baru
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'prefix' => 'required|string|max:10|unique:asset_categories,prefix',
        ]);

        AssetCategory::create([
            'name' => $request->name,
            'prefix' => strtoupper($request->prefix),
        ]);

        return redirect()->back()->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    // 3. Hapus Kategori
    public function destroyCategory($id)
    {
        try {
            // Pelindung: Pastikan tidak ada aset yang memakai kategori ini sebelum dihapus
            $category = AssetCategory::withCount('assets')->findOrFail($id);
            if ($category->assets_count > 0) {
                return redirect()->back()->with('error', 'Kategori ditolak dihapus! Masih ada ' . $category->assets_count . ' aset yang terdaftar di dalamnya.');
            }
            
            $category->delete(); // Ini otomatis menghapus Field di dalamnya karena onDelete('cascade')
            return redirect()->back()->with('success', 'Kategori berhasil dihapus dari sistem!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    // 4. Tambah Spesifikasi (Field) Baru
    public function storeField(Request $request, $categoryId)
    {
        $request->validate([
            'field_name' => 'required|string|max:255',
            'input_type' => 'required|string',
        ]);

        CategoryField::create([
            'asset_category_id' => $categoryId,
            'field_name' => $request->field_name,
            'input_type' => $request->input_type,
            'is_required' => $request->has('is_required') ? true : false,
        ]);

        return redirect()->back()->with('success', 'Kolom spesifikasi baru berhasil disematkan!');
    }

    // 5. Hapus Spesifikasi
    public function destroyField($id)
    {
        CategoryField::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Kolom spesifikasi berhasil dicabut!');
    }

    // 6. Tambah Lokasi (Gedung & Lantai) Tanpa Gambar Map
    public function storeLocation(Request $request)
    {
        $request->validate([
            'building_name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Cek apakah gedung sudah ada, jika belum buat baru
            $building = Building::firstOrCreate(
                ['name' => trim($request->building_name)]
            );

            // Jika admin juga mengetik nama lantai, masukkan ke gedung tersebut
            if ($request->filled('floor_name')) {
                Floor::firstOrCreate([
                    'building_id' => $building->id,
                    'name' => trim($request->floor_name)
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Lokasi baru berhasil didaftarkan ke radar sistem!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mendaftarkan lokasi: ' . $e->getMessage());
        }
    }
}