<?php

namespace App\Http\Controllers;

use App\Models\Floor; // <--- TAMBAHKAN BARIS INI
use App\Models\IpAddress;
use Illuminate\Http\Request;
use App\Models\AssetCategory;
use App\Models\CategoryField;
use App\Models\Asset;
use App\Models\AssetValue;
use App\Models\Building;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Pagination\LengthAwarePaginator; // Ditambahkan untuk bypass SQL Server 2008 Error

class InventoryController extends Controller
{
    // 1. Halaman Dashboard Dataset
    public function index()
    {
        $categories = AssetCategory::withCount('assets')->get();
        $totalAssets = Asset::count();
        return view('inventory.index', compact('categories', 'totalAssets'));
    }

    // 2. Halaman Tambah Barang Dinamis
    public function create()
    {
        $categories = AssetCategory::all();
        $buildings = Building::with('floors')->get();
        return view('inventory.create', compact('categories', 'buildings'));
    }

    
    // 3. API Untuk Membangkitkan Form Otomatis (AJAX)
    public function getFields($categoryId)
    {
        $fields = CategoryField::where('asset_category_id', $categoryId)->get();
        return response()->json($fields);
    }

    // 4. Inti Mesin: Menyimpan Data & Auto-ID
    public function store(Request $request)
    {
        $request->validate([
            'asset_category_id' => 'required|exists:asset_categories,id',
            'name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $category = AssetCategory::findOrFail($request->asset_category_id);
            $prefix = $category->prefix;
            
            $lastAsset = Asset::where('asset_code', 'like', $prefix . '-%')->orderBy('id', 'desc')->first();
            $newNumber = $lastAsset ? ((int) str_replace($prefix . '-', '', $lastAsset->asset_code)) + 1 : 1;
            $assetCode = $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            // Menyimpan Data Universal
            $asset = Asset::create([
                'asset_category_id' => $request->asset_category_id,
                'asset_code' => $assetCode,
                'name' => $request->name,
                'brand_model' => $request->brand_model,
                'status' => $request->status ?? 'aktif',
                'current_user' => $request->current_user,
                'description' => $request->description, // <--- TAMBAHKAN INI
                'installation_year' => $request->installation_year,
                'building_id' => $request->building_id,
                'floor_id' => $request->floor_id,
            ]);

            // LOGIKA BARU YANG BENAR: Cari IP-nya, Kunci ke Asetnya
            if ($request->filled('ip_address')) {
                $ip = trim($request->ip_address);
                \App\Models\IpAddress::updateOrCreate(
                    ['ip_address' => $ip], // Sistem mencari berdasarkan teks IP-nya
                    ['asset_id' => $asset->id, 'status' => 'in_use'] // Mengunci IP ke aset ini
                );
            }

            // --- LOGIKA PENGAITAN IP ADDRESS ---
            if ($request->filled('ip_address_id')) {
                IpAddress::where('id', $request->ip_address_id)->update([
                    'asset_id' => $asset->id,
                    'status' => 'in_use'
                ]);
            }

            // Menyimpan Spesifikasi Dinamis
            // Sisa-sisa logika penyedotan IP sudah dimusnahkan dari sini!
            if ($request->has('dynamic_fields')) {
                foreach ($request->dynamic_fields as $fieldId => $value) {
                    if (!empty($value)) {
                        AssetValue::create([
                            'asset_id' => $asset->id,
                            'category_field_id' => $fieldId,
                            'value' => $value,
                        ]);
                    }
                }
            }


            DB::commit();
            return response()->json(['success' => true, 'message' => "Aset {$assetCode} berhasil diinput!", 'asset_code' => $assetCode]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan data: ' . $e->getMessage()], 500);
        }
    }
    
    // 5. Halaman Daftar Aset Per Kategori (Tabel Dinamis)
    // 5. Halaman Daftar Aset Per Kategori (Server-Side Search & Sort)
    public function showCategory(Request $request, $id)
    {
        $category = AssetCategory::with('fields')->findOrFail($id);
        $perPage = (int) $request->query('per_page', 10);
        
        // Tangkap parameter pencarian dan sortir dari URL
        $search = $request->query('search');
        $sort = $request->query('sort', 'desc'); // Default: terbaru/terbesar di atas

        // Bangun Query Database
        $query = Asset::with(['values.field', 'building', 'floor', 'ipAddress'])
                      ->where('asset_category_id', $id);

        // Jika ada pencarian, cari di semua kolom relevan
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('brand_model', 'like', "%{$search}%")
                  ->orWhere('current_user', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Terapkan Sortir berdasarkan asset_code
        if ($sort === 'asc') {
            $query->orderBy('asset_code', 'asc');
        } else {
            $query->orderBy('asset_code', 'desc');
        }

        // Bypass SQL Server 2008 Offset Error: Ambil semua hasil query, lalu paginate via Laravel Collection
        $allAssets = $query->get();

        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $assets = new LengthAwarePaginator(
            $allAssets->forPage($page, $perPage),
            $allAssets->count(),
            $perPage,
            $page,
            // Pertahankan parameter search, sort, dan per_page di link paginasi
            ['path' => $request->url(), 'query' => $request->query()] 
        );

        return view('inventory.category', compact('category', 'assets', 'perPage', 'search', 'sort'));
    }

    // 6. Hapus Data Satuan
    public function destroy($id)
    {
        try {
            $asset = Asset::findOrFail($id);
            $code = $asset->asset_code;
            
            // Lepaskan IP agar kembali tersedia
            IpAddress::where('asset_id', $asset->id)->update(['asset_id' => null, 'status' => 'available']);
            
            $asset->delete(); 
            return response()->json(['success' => true, 'message' => "Aset {$code} telah dimusnahkan dari sistem!"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }

    // 7. Halaman Edit Data Aset
    public function edit($id)
    {
        $asset = Asset::with(['values', 'category.fields'])->findOrFail($id);
        $buildings = Building::with('floors')->get();
        return view('inventory.edit', compact('asset', 'buildings'));
    }

    // 8. Proses Update Data ke Database
    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        DB::beginTransaction();
        try {
            // Kita ambil data aset beserta relasi lokasinya untuk dicek
            $asset = Asset::with(['building', 'floor'])->findOrFail($id);

            // --- 1. TANGKAP DATA LAMA (Sebelum diubah) ---
            $oldLocation = trim(($asset->building ? $asset->building->name : 'Belum ditempatkan') . ' ' . ($asset->floor ? $asset->floor->name : ''));
            $oldUser = $asset->current_user ?? '-';

            // --- 2. UPDATE DATA UNIVERSAL ---
            $asset->update([
                'name' => $request->name,
                'brand_model' => $request->brand_model,
                'status' => $request->status ?? 'aktif',
                'current_user' => $request->current_user,
                'description' => $request->description, // <--- TAMBAHKAN INI
                'installation_year' => $request->installation_year,
                'building_id' => $request->building_id,
                'floor_id' => $request->floor_id,
            ]);

            // LOGIKA BARU YANG BENAR: Sistem Pelepasan & Pengikatan IP Cerdas
            if ($request->filled('ip_address')) {
                $ip = trim($request->ip_address);

                // 1. Lepaskan IP lama yang mungkin masih menempel di aset ini (Kembalikan ke IPAM)
                \App\Models\IpAddress::where('asset_id', $asset->id)
                    ->where('ip_address', '!=', $ip)
                    ->update(['asset_id' => null, 'status' => 'available']);

                // 2. Cari IP yang baru diketik, lalu kunci ke aset ini (Otomatis terbuat jika belum ada)
                \App\Models\IpAddress::updateOrCreate(
                    ['ip_address' => $ip],
                    ['asset_id' => $asset->id, 'status' => 'in_use']
                );
            } else {
                // Jika user menekan tombol "Cabut IP" / Belum Ada
                // Jangan hapus IP-nya, tapi kembalikan statusnya ke Available di IPAM
                \App\Models\IpAddress::where('asset_id', $asset->id)
                    ->update(['asset_id' => null, 'status' => 'available']);
            }

            // --- 3. TANGKAP DATA BARU & DETEKSI MUTASI ---
            $newBuildingModel = \App\Models\Building::find($request->building_id);
            $newFloorModel = \App\Models\Floor::find($request->floor_id);
            $newLocation = trim(($newBuildingModel ? $newBuildingModel->name : 'Belum ditempatkan') . ' ' . ($newFloorModel ? $newFloorModel->name : ''));
            $newUser = $request->current_user ?? '-';

            // Jika lokasi atau pengguna berubah, tembakkan ke tabel Sejarah Mutasi!
            if ($oldLocation !== $newLocation || $oldUser !== $newUser) {
                \App\Models\AssetMovement::create([
                    'asset_id' => $asset->id,
                    'previous_location' => $oldLocation,
                    'new_location' => $newLocation,
                    'previous_user' => $oldUser,
                    'new_user' => $newUser,
                    'reason' => 'Perubahan Data Master', // Alasan default otomatis
                    'movement_date' => now()->toDateString(),
                ]);
            }

            // --- 4. LOGIKA UPDATE SPESIFIKASI (EAV) ---
            // Sisa-sisa logika pelepasan dan penyedotan IP sudah dimusnahkan dari sini!
            if ($request->has('dynamic_fields')) {
                foreach ($request->dynamic_fields as $fieldId => $value) {
                    // Update data spesifikasi di tabel AssetValue
                    AssetValue::updateOrCreate(
                        ['asset_id' => $asset->id, 'category_field_id' => $fieldId],
                        ['value' => $value]
                    );
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => "Aset {$asset->asset_code} berhasil diperbarui!"]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }

    // 9. Download Template Excel Kosong (Untuk Import)
    public function downloadTemplate($categoryId)
    {
        $category = AssetCategory::with('fields')->findOrFail($categoryId);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template ' . $category->prefix);

        // Header Dasar
        $headers = ['Nama Perangkat', 'Merek/Model', 'Pengguna', 'Tahun', 'Status', 'Nama Gedung', 'Nama Lantai'];
        
        // LOGIKA BARU: Tambahkan Kolom IP Jika Kategori Membutuhkannya
        if ($category->has_ip) {
            $headers[] = 'IP Address';
        }

        // Header Spesifikasi EAV
        foreach ($category->fields as $field) { $headers[] = $field->field_name; }

        $sheet->fromArray($headers, NULL, 'A1');
        
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastCol . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');

        foreach (range('A', $lastCol) as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

        $fileName = 'Template_Import_' . $category->name . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // 10. Export Data Aset ke Excel
    public function exportExcel($categoryId)
    {
        $category = AssetCategory::with('fields')->findOrFail($categoryId);
        $assets = Asset::with(['values.field', 'building', 'floor'])->where('asset_category_id', $categoryId)->orderBy('id', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data ' . $category->prefix);

        $headers = ['ID Barang', 'Nama Perangkat', 'Merek/Model', 'Pengguna', 'Tahun', 'Status', 'Gedung', 'Lantai'];
        foreach ($category->fields as $field) { $headers[] = $field->field_name; }
        $sheet->fromArray($headers, NULL, 'A1');

        $row = 2;
        foreach ($assets as $asset) {
            $rowData = [
                $asset->asset_code, $asset->name, $asset->brand_model ?? '-', $asset->current_user ?? '-',
                $asset->installation_year ?? '-', strtoupper($asset->status),
                $asset->building ? $asset->building->name : '-', $asset->floor ? $asset->floor->name : '-'
            ];
            foreach ($category->fields as $field) {
                $valObj = $asset->values->firstWhere('category_field_id', $field->id);
                $rowData[] = $valObj ? $valObj->value : '-';
            }
            $sheet->fromArray($rowData, NULL, 'A' . $row++);
        }

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastCol . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');
        foreach (range('A', $lastCol) as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

        $fileName = 'Export_Data_' . $category->name . '_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // 11. Import Excel Ratusan Baris Sekaligus
    public function importExcel(Request $request, $categoryId)
    {
        $request->validate(['excel_file' => 'required|mimes:xlsx,xls']);
        $category = AssetCategory::with('fields')->findOrFail($categoryId);

        $file = $request->file('excel_file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        array_shift($rows); // Buang baris header

        DB::beginTransaction();
        try {
            $importedCount = 0;
            foreach ($rows as $row) {
                if (empty(trim($row[0]))) continue;

                $buildingId = null;
                $floorId = null;
                $namaGedung = trim($row[5] ?? '');
                $namaLantai = trim($row[6] ?? '');

                if (!empty($namaGedung)) {
                    $bldg = Building::where('name', $namaGedung)->first();
                    if ($bldg) {
                        $buildingId = $bldg->id;
                        if (!empty($namaLantai)) {
                            $flr = Floor::where('name', $namaLantai)->where('building_id', $buildingId)->first();
                            if ($flr) $floorId = $flr->id;
                        }
                    }
                }

                $prefix = $category->prefix;
                $lastAsset = Asset::where('asset_code', 'like', $prefix . '-%')->orderBy('id', 'desc')->lockForUpdate()->first();
                $newNumber = $lastAsset ? ((int) str_replace($prefix . '-', '', $lastAsset->asset_code)) + 1 : 1;
                $assetCode = $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

                $asset = Asset::create([
                    'asset_category_id' => $category->id,
                    'asset_code' => $assetCode,
                    'name' => trim($row[0]),
                    'brand_model' => trim($row[1] ?? ''),
                    'current_user' => trim($row[2] ?? ''),
                    'installation_year' => trim($row[3] ?? ''),
                    'status' => strtolower(trim($row[4] ?? '')) ?: 'aktif',
                    'building_id' => $buildingId,
                    'floor_id' => $floorId,
                ]);

                // Index kolom ke-7 (H)
                $dynIndex = 7; 

                // --- LOGIKA BARU: TANGKAP IP NATIVE (Jika Ada) ---
                if ($category->has_ip) {
                    if (isset($row[$dynIndex]) && trim($row[$dynIndex]) !== '') {
                        $ipVal = trim($row[$dynIndex]);
                        \App\Models\IpAddress::updateOrCreate(
                            ['ip_address' => $ipVal],
                            ['asset_id' => $asset->id, 'status' => 'in_use']
                        );
                    }
                    $dynIndex++; // Geser index ke kanan untuk membaca spesifikasi EAV
                }

                // --- INSERT SPESIFIKASI DINAMIS (EAV) ---
                foreach ($category->fields as $field) {
                    if (isset($row[$dynIndex]) && trim($row[$dynIndex]) !== '') {
                        AssetValue::create([
                            'asset_id' => $asset->id,
                            'category_field_id' => $field->id,
                            'value' => trim($row[$dynIndex])
                        ]);
                    }
                    $dynIndex++;
                }
                
                $importedCount++;
            }
            DB::commit();
            return redirect()->back()->with('success', "Misi Selesai! {$importedCount} data aset berhasil di-import ke sistem.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }

    // 12. Bulk Delete (Pemusnah Massal)
    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:assets,id']);

        DB::beginTransaction();
        try {
            // Lepaskan semua IP dari aset-aset yang akan dimusnahkan
            IpAddress::whereIn('asset_id', $request->ids)->update(['asset_id' => null, 'status' => 'available']);
            
            Asset::whereIn('id', $request->ids)->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => count($request->ids) . ' data aset berhasil dimusnahkan secara massal!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus massal: ' . $e->getMessage()], 500);
        }
    }

    // =================================================================
    // 13. FITUR MASTER AUDIT: CETAK LAPORAN (VISUAL & DATA)
    // =================================================================
    public function printReport(Request $request)
    {
        // Tarik semua data Gedung -> Lantai -> Aset -> Kategori, IP, dan Nilai EAV
        $buildings = Building::with(['floors.assets.category', 'floors.assets.ipAddress', 'floors.assets.values.field'])->get();
        
        // Tarik definisi Kategori beserta kolom dinamisnya
        $categories = AssetCategory::with('fields')->get();
        
        return view('inventory.print_report', compact('buildings', 'categories'));
    }

    // =================================================================
    // 14. FITUR MASTER AUDIT: EXPORT EXCEL DINAMIS
    // =================================================================
    public function exportInventory()
    {
        $buildings = Building::with(['floors.assets.category', 'floors.assets.ipAddress', 'floors.assets.values.field'])->get();
        $categories = AssetCategory::with('fields')->get();

        $spreadsheet = new Spreadsheet();
        
        // --- 1. SIAPKAN SHEET GLOBAL (ALL INVENTORY) ---
        $sheetAll = $spreadsheet->getActiveSheet();
        $sheetAll->setTitle('All Inventory');
        
        $headersAll = ['No', 'Gedung', 'Lantai', 'Kategori', 'Kode Aset', 'Hostname/Nama', 'Merek/Model', 'Pengguna', 'IP Address', 'Status Operasional', 'Tahun', 'Keterangan'];
        $sheetAll->fromArray($headersAll, NULL, 'A1');
        $sheetAll->getStyle('A1:L1')->getFont()->setBold(true);
        $sheetAll->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');

        // --- 2. SIAPKAN SHEET KHUSUS PER KATEGORI ---
        $sheets = ['All' => $sheetAll];
        $rowCounters = ['All' => 2];
        $noCounters = ['All' => 1];

        foreach ($categories as $cat) {
            $sheet = $spreadsheet->createSheet();
            $sheetName = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $cat->name), 0, 30); // Bersihkan nama untuk Excel
            $sheet->setTitle($sheetName);
            $sheets[$cat->id] = $sheet;
            $rowCounters[$cat->id] = 2;
            $noCounters[$cat->id] = 1;

            // Header Khusus per Kategori (Menambahkan Spesifikasi EAV di belakang)
            $catHeaders = ['No', 'Gedung', 'Lantai', 'Kode Aset', 'Hostname/Nama', 'Merek/Model', 'Pengguna', 'IP Address', 'Status', 'Tahun', 'Keterangan'];
            foreach ($cat->fields as $field) { $catHeaders[] = $field->field_name; }
            
            $sheet->fromArray($catHeaders, NULL, 'A1');
            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($catHeaders));
            $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
            $sheet->getStyle('A1:' . $lastCol . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2EFDA');
        }

        // --- 3. DISTRIBUSI DATA KE SHEET ---
        foreach ($buildings as $building) {
            foreach ($building->floors as $floor) {
                foreach ($floor->assets as $asset) {
                    
                    $catId = $asset->asset_category_id;
                    $ip = $asset->ipAddress->ip_address ?? '-';
                    
                    // A. Masukkan ke Sheet All
                    $dataAll = [
                        $noCounters['All']++, $building->name, $floor->name, $asset->category->name ?? 'Unknown',
                        $asset->asset_code, $asset->name, $asset->brand_model ?? '-', $asset->current_user ?? '-',
                        $ip, strtoupper($asset->status ?? '-'), $asset->installation_year ?? '-', $asset->description ?? '-'
                    ];
                    $sheets['All']->fromArray($dataAll, NULL, 'A' . $rowCounters['All']++);

                    // B. Masukkan ke Sheet Khusus Kategori (Jika Kategori Valid)
                    if (isset($sheets[$catId])) {
                        $dataCat = [
                            $noCounters[$catId]++, $building->name, $floor->name, $asset->asset_code,
                            $asset->name, $asset->brand_model ?? '-', $asset->current_user ?? '-', $ip,
                            strtoupper($asset->status ?? '-'), $asset->installation_year ?? '-', $asset->description ?? '-'
                        ];
                        
                        // Tambahkan nilai Spesifikasi Khusus (EAV)
                        $category = $categories->firstWhere('id', $catId);
                        if ($category) {
                            foreach ($category->fields as $field) {
                                $valObj = $asset->values->firstWhere('category_field_id', $field->id);
                                $dataCat[] = $valObj ? $valObj->value : '-';
                            }
                        }
                        $sheets[$catId]->fromArray($dataCat, NULL, 'A' . $rowCounters[$catId]++);
                    }
                }
            }
        }

        // --- 4. RAPIKAN KOLOM & DOWNLOAD ---
        foreach ($sheets as $sheet) {
            $highestColumn = $sheet->getHighestColumn();
            foreach (range('A', $highestColumn) as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
        }

        $fileName = 'Master_Audit_Inventory_KBK_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}