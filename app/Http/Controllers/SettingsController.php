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
        
        // Data Statistik untuk Tab Dashboard
        $totalBuildings = $buildings->count();
        $totalFloors = \App\Models\Floor::count();
        $totalCategories = $categories->count();
        
        return view('inventory.settings', compact('categories', 'buildings', 'totalBuildings', 'totalFloors', 'totalCategories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'prefix' => 'required|string|max:10|unique:asset_categories,prefix',
            'icon_file' => 'nullable|image|mimes:png,svg,jpg,jpeg|max:2048', // Validasi file gambar
            'icon_size' => 'nullable|integer|min:20|max:150',
            'color' => 'nullable|string|max:10'
        ]);

        // Logika Upload File Icon
        $iconPath = null;
        if ($request->hasFile('icon_file')) {
            $file = $request->file('icon_file');
            // Membuat nama file unik dan menyimpannya di folder public/uploads/category_icons
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9.]/', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/category_icons'), $filename);
            $iconPath = 'uploads/category_icons/' . $filename;
        }

        \App\Models\AssetCategory::create([
            'name' => $request->name,
            'prefix' => strtoupper($request->prefix),
            'has_ip' => $request->has('has_ip'),
            'icon_path' => $iconPath,
            'icon_size' => $request->icon_size ?? 40,
            'color' => $request->color ?? '#3b82f6'
        ]);

        return redirect()->back()->with('success', 'Kategori baru dengan custom icon berhasil ditambahkan!');
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

    // 7. Hapus Gedung (Pelindung: Tolak jika ada lantai di dalamnya)
    public function destroyBuilding($id)
    {
        try {
            $building = Building::withCount('floors')->findOrFail($id);
            if ($building->floors_count > 0) {
                return redirect()->back()->with('error', "Gedung {$building->name} tidak bisa dihapus karena masih memiliki {$building->floors_count} lantai di dalamnya!");
            }
            $building->delete();
            return redirect()->back()->with('success', 'Gedung berhasil diratakan dari sistem!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus gedung: ' . $e->getMessage());
        }
    }

    // 8. Hapus Lantai (Pelindung: Tolak jika ada aset di dalamnya)
    public function destroyFloor($id)
    {
        try {
            $floor = Floor::withCount('assets')->findOrFail($id);
            if ($floor->assets_count > 0) {
                return redirect()->back()->with('error', "Lantai {$floor->name} tidak bisa dihapus karena masih ada {$floor->assets_count} perangkat di dalamnya!");
            }
            $floor->delete();
            return redirect()->back()->with('success', 'Lantai berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus lantai: ' . $e->getMessage());
        }
    }

    // --- FITUR UPDATE KATEGORI & VISUAL ICON ---
    public function updateCategory(Request $request, $id) 
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon_file' => 'nullable|image|mimes:png,svg,jpg,jpeg|max:2048',
            'icon_size' => 'required|integer|min:20|max:150',
            'color' => 'required|string|max:10'
        ]);

        $category = \App\Models\AssetCategory::findOrFail($id);
        
        $dataToUpdate = [
            'name' => $request->name,
            'has_ip' => $request->has('has_ip'),
            'icon_size' => $request->icon_size,
            'color' => $request->color,
        ];

        // Jika user mengupload icon baru
        if ($request->hasFile('icon_file')) {
            // Hapus gambar lama dari server (jika ada) agar bersih
            if ($category->icon_path && file_exists(public_path($category->icon_path))) {
                unlink(public_path($category->icon_path));
            }

            // Simpan gambar baru
            $file = $request->file('icon_file');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9.]/', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/category_icons'), $filename);
            $dataToUpdate['icon_path'] = 'uploads/category_icons/' . $filename;
        }

        $category->update($dataToUpdate);
        return redirect()->back()->with('success', 'Konfigurasi Kategori & Visual berhasil diperbarui!');
    }

    // --- FITUR UPDATE KOLOM SPESIFIKASI (EAV) ---
    public function updateField(Request $request, $id) 
    {
        $request->validate([
            'field_name' => 'required|string|max:255',
            'input_type' => 'required|string|in:text,number,date',
        ]);

        $field = \App\Models\CategoryField::findOrFail($id);
        
        $field->update([
            'field_name' => $request->field_name,
            'input_type' => $request->input_type,
            'is_required' => $request->has('is_required') ? 1 : 0
        ]);

        return redirect()->back()->with('success', 'Kolom spesifikasi berhasil diperbarui!');
    }
}