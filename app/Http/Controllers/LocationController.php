<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Asset; // Pastikan Model Asset dipanggil

class LocationController extends Controller
{
    // 1. Tampilkan halaman Dashboard (Lobby / HUB)
    public function hub()
    {
        // Panggil semua gedung BESERTA data lantainya (Eager Loading)
        $buildings = Building::with('floors')->get(); 
        
        // --- STATISTIK UNTUK DASHBOARD ---
        // Hitung total aset per jenis (opsional, jika dashboard Tuan membutuhkan angka-angka ini)
        $totalSwitches = Asset::whereHas('category', function($q) { $q->where('prefix', 'like', '%SWT%')->orWhere('name', 'like', '%Switch%'); })->count();
        $totalPcs = Asset::whereHas('category', function($q) { $q->where('prefix', 'like', '%PC%'); })->count();
        $totalAssets = Asset::count();
        
        return view('hub', compact('buildings', 'totalSwitches', 'totalPcs', 'totalAssets'));
    }

    // --- 2. Fungsi untuk Live Monitor (Read-Only Full Data) ---
    public function liveMonitor()
    {
        // THE GREAT MERGE APPLIED: Tarik relasi 'assets' beserta kategori dan IP
        $buildings = Building::with(['floors.assets.category', 'floors.assets.ipAddress'])->get();
        
        // Cukup kirimkan variabel buildings saja ke view
        return view('dashboard', compact('buildings')); 
    }

    // --- 3. Fungsi untuk Macro Editor (Polygon Mapping) ---
    public function macroEditor()
    {
        // Editor Makro tidak butuh data Aset, cukup data Gedung saja agar ringan
        $buildings = Building::all(); 
        return view('macro_editor', compact('buildings'));
    }

    // --- 4. Fungsi untuk Micro Studio (Split Screen) ---
    public function microStudio($building_id)
    {
        $building = Building::with('floors')->findOrFail($building_id);
        return view('micro_studio', compact('building'));
    }

    // 5. Simpan data Gedung Baru (Mode Polygon)
    public function storeBuilding(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'polygon_points' => 'required|string'
        ]);

        $points = json_decode($request->polygon_points, true);

        // MATEMATIKA CERDAS: Mencari titik tengah polygon
        $minX = min(array_column($points, 'x')); $maxX = max(array_column($points, 'x'));
        $minY = min(array_column($points, 'y')); $maxY = max(array_column($points, 'y'));
        
        $centerX = $minX + (($maxX - $minX) / 2); $centerY = $minY + (($maxY - $minY) / 2);

        $building = Building::create([
            'name' => $request->name,
            'pos_x' => $centerX, 
            'pos_y' => $centerY, 
            'polygon_points' => $request->polygon_points, 
        ]);

        return response()->json(['success' => true, 'message' => 'Gedung ' . $building->name . ' berhasil dipetakan!', 'building' => $building]);
    }

    public function storeFloor(Request $request)
    {
        $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'box_width' => 'required', 'box_height' => 'required', 'box_left' => 'required', 'box_top' => 'required',
        ]);

        $imageName = time() . '_' . $request->image->getClientOriginalName();
        $request->image->move(public_path('uploads'), $imageName);

        $floor = Floor::create([
            'building_id' => $request->building_id,
            'name' => $request->name,
            'image_path' => '/uploads/' . $imageName,
            'box_width' => $request->box_width, 'box_height' => $request->box_height, 'box_left' => $request->box_left, 'box_top' => $request->box_top,
            'polygon_points' => $request->polygon_points 
        ]);

        return response()->json(['success' => true, 'message' => 'Lantai ' . $floor->name . ' berhasil disimpan!', 'floor' => $floor]);
    }

    public function updatePosition(Request $request)
    {
        try {
            $building = Building::findOrFail($request->id);
            $building->update(['polygon_points' => $request->polygon_points]);
            return response()->json(['success' => true, 'message' => 'Posisi gedung berhasil diupdate!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error Server: ' . $e->getMessage()], 500);
        }
    }

    // --- HALAMAN MANAGEMENT DATA ---
    public function management()
    {
        $buildings = Building::withCount('floors')->orderBy('created_at', 'desc')->get();
        $floors = Floor::with('building')->orderBy('created_at', 'desc')->get();
        return view('management', compact('buildings', 'floors'));
    }

    // --- FITUR UPDATE NAMA GEDUNG ---
    public function updateBuilding(Request $request, $id) 
    {
        $request->validate(['name' => 'required|string|max:255']);
        $building = \App\Models\Building::findOrFail($id);
        $building->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Nama gedung berhasil diperbarui!');
    }

    public function destroyBuilding($id)
    {
        $building = Building::findOrFail($id);
        $building->delete();
        return response()->json(['success' => true, 'message' => 'Gedung berhasil dihapus!']);
    }

    // --- FITUR UPDATE NAMA LANTAI ---
    public function updateFloor(Request $request, $id) 
    {
        $request->validate(['name' => 'required|string|max:255']);
        $floor = \App\Models\Floor::findOrFail($id);
        $floor->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Nama lantai berhasil diperbarui!');
    }

    public function destroyFloor($id)
    {
        $floor = Floor::findOrFail($id);
        $floor->delete();
        return response()->json(['success' => true, 'message' => 'Lantai berhasil dihapus!']);
    }

    public function destroyBuildingBatch(Request $request)
    {
        Building::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true, 'message' => count($request->ids) . ' Gedung berhasil dihanguskan!']);
    }

    public function destroyFloorBatch(Request $request)
    {
        Floor::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true, 'message' => count($request->ids) . ' Lantai berhasil dilenyapkan!']);
    }

    public function editMicroStudio($floor_id)
    {
        $floor = Floor::with('building')->findOrFail($floor_id);
        $building = $floor->building;
        return view('micro_studio', compact('building', 'floor'));
    }

    public function updateFloorVisual(Request $request)
    {
        $floor = Floor::findOrFail($request->floor_id);

        $dataToUpdate = [
            'name' => $request->name,
            'box_width' => $request->box_width, 'box_height' => $request->box_height, 'box_left' => $request->box_left, 'box_top' => $request->box_top,
            'polygon_points' => $request->polygon_points 
        ];

        if ($request->hasFile('image')) {
            $imageName = time() . '_' . $request->image->getClientOriginalName();
            $request->image->move(public_path('uploads'), $imageName);
            $dataToUpdate['image_path'] = '/uploads/' . $imageName;
        }

        $floor->update($dataToUpdate);
        return response()->json(['success' => true, 'message' => 'Visual Lantai berhasil diperbarui (Overwritten)!']);
    }
    
    // (FUNGSI printReport LAMA DIHAPUS, DIGANTIKAN OLEH SWITCH CONTROLLER)

    // Export Inventory sekarang membaca dari Asset
    public function exportInventory()
    {
        $buildings = Building::with(['floors.assets.category', 'floors.assets.ipAddress'])->get();
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        $sheetAll = $spreadsheet->getActiveSheet(); $sheetAll->setTitle('All Inventory');
        
        $setHeaders = function($sheet) {
            $headers = ['No', 'Gedung', 'Lantai', 'Kode Aset', 'Kategori', 'Nama Perangkat', 'Merek/Model', 'IP Address', 'Pengguna', 'Status', 'Tahun', 'Keterangan'];
            $sheet->fromArray($headers, NULL, 'A1');
            $sheet->getStyle('A1:L1')->getFont()->setBold(true);
            $sheet->getStyle('A1:L1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');
        };

        $setHeaders($sheetAll);
        $rowAll = 2; $noAll = 1; 

        foreach ($buildings as $building) {
            foreach ($building->floors as $floor) {
                foreach ($floor->assets as $asset) {
                    $data = [
                        $noAll++, 
                        $building->name, 
                        $floor->name, 
                        $asset->asset_code ?? '-',
                        $asset->category->name ?? 'Unknown',
                        $asset->name, 
                        $asset->brand_model ?? '-',
                        $asset->ipAddress->ip_address ?? '-', 
                        $asset->current_user ?? '-',
                        strtoupper($asset->status ?? '-'),
                        $asset->installation_year ?? '-',
                        $asset->description ?? '-'
                    ];
                    $sheetAll->fromArray($data, NULL, 'A' . $rowAll++);
                }
            }
        }

        foreach (range('A', 'L') as $col) { $sheetAll->getColumnDimension($col)->setAutoSize(true); }

        $fileName = 'Master_Audit_Inventory_KBK_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}