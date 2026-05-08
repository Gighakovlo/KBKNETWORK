<?php

namespace App\Http\Controllers;

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
                'installation_year' => $request->installation_year,
                'building_id' => $request->building_id,
                'floor_id' => $request->floor_id,
            ]);

            // --- LOGIKA PENGAITAN IP ADDRESS ---
            if ($request->filled('ip_address_id')) {
                IpAddress::where('id', $request->ip_address_id)->update([
                    'asset_id' => $asset->id,
                    'status' => 'in_use'
                ]);
            }

            // Menyimpan Spesifikasi Dinamis & Auto-Harvesting IP
            if ($request->has('dynamic_fields')) {
                foreach ($request->dynamic_fields as $fieldId => $value) {
                    if (!empty($value)) {
                        AssetValue::create([
                            'asset_id' => $asset->id,
                            'category_field_id' => $fieldId,
                            'value' => $value,
                        ]);

                        // --- LOGIKA SEDOT IP OTOMATIS ---
                        $fieldDef = CategoryField::find($fieldId);
                        // Jika nama kolom mengandung kata 'IP' atau 'Alamat IP'
                        if ($fieldDef && (stripos($fieldDef->field_name, 'IP') !== false || stripos($fieldDef->field_name, 'Alamat IP') !== false)) {
                            // Logika Baru: Cari IP berdasarkan Aset-nya, bukan angka IP-nya
                            IpAddress::updateOrCreate(
                                ['asset_id' => $asset->id], // Yang dicari adalah ID Aset
                                ['ip_address' => trim($value), 'status' => 'in_use'] // Yang diupdate adalah angka IP-nya
                            );
                        }
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
    public function showCategory(Request $request, $id)
    {
        $category = AssetCategory::with('fields')->findOrFail($id);
        $perPage = (int) $request->query('per_page', 10);

        // Bypass SQL Server 2008 Offset Error: Ambil semua data dulu, lalu paginate via Laravel Collection
        $allAssets = Asset::with(['values.field', 'building', 'floor'])
                        ->where('asset_category_id', $id)
                        ->orderBy('id', 'desc')
                        ->get();

        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $assets = new LengthAwarePaginator(
            $allAssets->forPage($page, $perPage),
            $allAssets->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('inventory.category', compact('category', 'assets', 'perPage'));
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
                'installation_year' => $request->installation_year,
                'building_id' => $request->building_id,
                'floor_id' => $request->floor_id,
            ]);

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

            // --- 4. LOGIKA UPDATE SPESIFIKASI (EAV) & SEDOT IP OTOMATIS ---
            // Lepaskan dulu IP lama yang menempel di aset ini dari IP Manager
            \App\Models\IpAddress::where('asset_id', $asset->id)->update(['asset_id' => null, 'status' => 'available']);

            if ($request->has('dynamic_fields')) {
                foreach ($request->dynamic_fields as $fieldId => $value) {
                    // Update data spesifikasi di tabel AssetValue
                    AssetValue::updateOrCreate(
                        ['asset_id' => $asset->id, 'category_field_id' => $fieldId],
                        ['value' => $value]
                    );

                    // Jika field ini adalah IP dan ada isinya, sedot ke IP Manager!
                    if (!empty($value)) {
                        $fieldDef = CategoryField::find($fieldId);
                        if ($fieldDef && (stripos($fieldDef->field_name, 'IP') !== false || stripos($fieldDef->field_name, 'Alamat IP') !== false)) {
                            \App\Models\IpAddress::updateOrCreate(
                                ['asset_id' => $asset->id], // Cari berdasarkan ID Aset
                                ['ip_address' => trim($value), 'status' => 'in_use'] // Update angka IP-nya
                            );
                        }
                    }
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

        $headers = ['Nama Perangkat', 'Merek/Model', 'Pengguna', 'Tahun', 'Status', 'Nama Gedung', 'Nama Lantai'];
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
        array_shift($rows);

        DB::beginTransaction();
        try {
            $importedCount = 0;
            foreach ($rows as $row) {
                if (empty(trim($row[0]))) continue;

                $buildingId = null;
                $floorId = null;
                $namaGedung = trim($row[5]);
                $namaLantai = trim($row[6]);

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
                    'brand_model' => trim($row[1]),
                    'current_user' => trim($row[2]),
                    'installation_year' => trim($row[3]),
                    'status' => strtolower(trim($row[4])) ?: 'aktif',
                    'building_id' => $buildingId,
                    'floor_id' => $floorId,
                ]);

                // --- 4. INSERT SPESIFIKASI DINAMIS & HARVESTING IP ---
                    $dynIndex = 7; 
                    foreach ($category->fields as $field) {
                        if (isset($row[$dynIndex]) && trim($row[$dynIndex]) !== '') {
                            $val = trim($row[$dynIndex]);
                            
                            AssetValue::create([
                                'asset_id' => $asset->id,
                                'category_field_id' => $field->id,
                                'value' => $val
                            ]);

                            // Tangkap IP dari Excel!
                            if (stripos($field->field_name, 'IP') !== false || stripos($field->field_name, 'Alamat IP') !== false) {
                                // Logika Baru: Cari IP berdasarkan Aset-nya, bukan angka IP-nya
                                IpAddress::updateOrCreate(
                                    ['asset_id' => $asset->id], // Yang dicari adalah ID Aset
                                    ['ip_address' => trim($value), 'status' => 'in_use'] // Yang diupdate adalah angka IP-nya
                                );
                            }
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
}