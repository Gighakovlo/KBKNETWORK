<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Floor;
use App\Models\BackboneConnection;


class LocationController extends Controller
{
    // 1. Tampilkan halaman Dashboard (Lobby)
    public function hub()
    {
        // Panggil semua gedung BESERTA data lantainya (Eager Loading)
        $buildings = Building::with('floors')->get(); 
        
        return view('hub', compact('buildings'));
    }

    // --- 2. Fungsi untuk Live Monitor (Read-Only Full Data) ---
    public function liveMonitor()
    {
        // Tetap menggunakan Eager Loading yang super lengkap
        $buildings = Building::with(['floors.switchNodes', 'floors.pcNodes'])->get();
        $backbones = BackboneConnection::all();
        
        return view('dashboard', compact('buildings', 'backbones')); 
        // (Sementara kita arahkan ke view dashboard lama Tuan dulu untuk Live Monitor)
    }

    // --- 3. Fungsi untuk Macro Editor (Polygon Mapping) ---
    public function macroEditor()
    {
        // Editor Makro tidak butuh data Switch/PC, cukup data Gedung saja agar ringan
        $buildings = Building::all(); 
        
        return view('macro_editor', compact('buildings'));
    }

    // --- 4. Fungsi untuk Micro Studio (Split Screen) ---
    public function microStudio($building_id)
    {
        // Cari gedung berdasarkan ID, dan bawa serta data lantainya
        $building = Building::with('floors')->findOrFail($building_id);
        
        return view('micro_studio', compact('building'));
    }

    // 2. Simpan data Gedung Baru
    // 2. Simpan data Gedung Baru (Mode Polygon)
    public function storeBuilding(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'name' => 'required|string|max:255',
            'polygon_points' => 'required|string' // Wajib ada data polygon-nya
        ]);

        // 2. Konversi JSON titik polygon menjadi Array PHP
        $points = json_decode($request->polygon_points, true);

        // 3. MATEMATIKA CERDAS: Mencari titik tengah (Center Point) dari Polygon
        // Ini berguna untuk menaruh Label Nama Gedung persis di tengah-tengah bentuk
        $minX = min(array_column($points, 'x'));
        $maxX = max(array_column($points, 'x'));
        $minY = min(array_column($points, 'y'));
        $maxY = max(array_column($points, 'y'));
        
        $centerX = $minX + (($maxX - $minX) / 2);
        $centerY = $minY + (($maxY - $minY) / 2);

        // 4. Simpan ke database
        $building = Building::create([
            'name' => $request->name,
            'pos_x' => $centerX, // Otomatis di tengah polygon
            'pos_y' => $centerY, // Otomatis di tengah polygon
            'polygon_points' => $request->polygon_points, // Simpan bentuk aslinya
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gedung ' . $building->name . ' berhasil dipetakan!',
            'building' => $building
        ]);
    }
    public function storeFloor(Request $request)
    {
        $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'box_width' => 'required',
            'box_height' => 'required',
            'box_left' => 'required',
            'box_top' => 'required',
        ]);

        // 1. Simpan Gambar ke folder public/uploads
        $imageName = time() . '_' . $request->image->getClientOriginalName();
        $request->image->move(public_path('uploads'), $imageName);

        // 2. Simpan Data Lantai ke SQL Server
        $floor = Floor::create([
            'building_id' => $request->building_id,
            'name' => $request->name,
            'image_path' => '/uploads/' . $imageName,
            'box_width' => $request->box_width,
            'box_height' => $request->box_height,
            'box_left' => $request->box_left,
            'box_top' => $request->box_top,
            'polygon_points' => $request->polygon_points // <--- TAMBAHKAN BARIS INI
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lantai ' . $floor->name . ' berhasil disimpan!',
            'floor' => $floor
        ]);
    }
    // Fungsi untuk menyimpan kordinat saat gedung digeser (Drag & Drop)
    public function updatePosition(Request $request)
    {
        try {
            // Cari gedung berdasarkan ID yang dikirim dari JS
            $building = Building::findOrFail($request->id);
            
            // Timpa kordinat lama dengan kordinat baru
            $building->update([
                'polygon_points' => $request->polygon_points
            ]);

            // Kembalikan JSON sukses
            return response()->json([
                'success' => true, 
                'message' => 'Posisi gedung berhasil diupdate!'
            ]);

        } catch (\Exception $e) {
            // JIKA CRASH, JANGAN KIRIM HTML! Kirim JSON berisi pesan error aslinya
            return response()->json([
                'success' => false, 
                'message' => 'Error Server: ' . $e->getMessage()
            ], 500);
        }
    }

    // --- HALAMAN MANAGEMENT DATA ---
    public function management()
    {
        // Ambil semua gedung beserta jumlah lantainya
        $buildings = Building::withCount('floors')->orderBy('created_at', 'desc')->get();
        // Ambil semua lantai beserta nama gedung induknya
        $floors = Floor::with('building')->orderBy('created_at', 'desc')->get();
        
        return view('management', compact('buildings', 'floors'));
    }

    // --- FUNGSI UPDATE & DELETE GEDUNG ---
    public function updateBuilding(Request $request, $id)
    {
        $building = Building::findOrFail($id);
        $building->update(['name' => $request->name]);
        return response()->json(['success' => true, 'message' => 'Nama gedung diperbarui!']);
    }

    public function destroyBuilding($id)
    {
        $building = Building::findOrFail($id);
        // Lantai dan perangkat di dalamnya otomatis akan terhapus jika di migration Tuan menggunakan onDelete('cascade')
        $building->delete();
        return response()->json(['success' => true, 'message' => 'Gedung berhasil dihapus!']);
    }

    // --- FUNGSI UPDATE & DELETE LANTAI ---
    public function updateFloor(Request $request, $id)
    {
        $floor = Floor::findOrFail($id);
        $floor->update(['name' => $request->name]);
        return response()->json(['success' => true, 'message' => 'Nama lantai diperbarui!']);
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

    // Buka Micro Studio dalam Mode EDIT
    public function editMicroStudio($floor_id)
    {
        $floor = Floor::with('building')->findOrFail($floor_id);
        $building = $floor->building;
        return view('micro_studio', compact('building', 'floor'));
    }

    // Overwrite Database Lantai
    public function updateFloorVisual(Request $request)
    {
        $floor = Floor::findOrFail($request->floor_id);

        $dataToUpdate = [
            'name' => $request->name,
            'box_width' => $request->box_width,
            'box_height' => $request->box_height,
            'box_left' => $request->box_left,
            'box_top' => $request->box_top,
            'polygon_points' => $request->polygon_points // <--- TAMBAHKAN BARIS INI
        ];

        // Jika Tuan mengupload gambar denah baru, timpa yang lama
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . $request->image->getClientOriginalName();
            $request->image->move(public_path('uploads'), $imageName);
            $dataToUpdate['image_path'] = '/uploads/' . $imageName;
        }

        $floor->update($dataToUpdate);

        return response()->json(['success' => true, 'message' => 'Visual Lantai berhasil diperbarui (Overwritten)!']);
    }
    
    public function printReport()
    {
        // Ambil SEMUA data hierarki dari ujung atas sampai ujung bawah
        $buildings = Building::with(['floors.switchNodes', 'floors.pcNodes'])->get();
        
        return view('print_report', compact('buildings'));
    }
    
    public function exportInventory()
    {
        // 1. Ambil Data
        $buildings = Building::with(['floors.switchNodes', 'floors.pcNodes'])->get();

        // 2. Siapkan File Excel (.xlsx)
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Buat 3 Sheet yang Tuan minta
        $sheetAll = $spreadsheet->getActiveSheet();
        $sheetAll->setTitle('All Inventory');
        
        $sheetSwitch = $spreadsheet->createSheet();
        $sheetSwitch->setTitle('Switch');
        
        $sheetPC = $spreadsheet->createSheet();
        $sheetPC->setTitle('PC');

        // Fungsi Bantuan untuk Mewarnai Header Tabel (Dengan Tambahan 2 Kolom Baru)
        $setHeaders = function($sheet) {
            $headers = ['No', 'Gedung', 'Lantai', 'Jenis Perangkat', 'Hostname', 'IP Address', 'Spesifikasi / User', 'Status Operasional', 'Tahun Pemasangan'];
            $sheet->fromArray($headers, NULL, 'A1');
            $sheet->getStyle('A1:I1')->getFont()->setBold(true);
            $sheet->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2'); // Warna Biru Lembut
        };

        // Terapkan header ke ketiga sheet
        $setHeaders($sheetAll);
        $setHeaders($sheetSwitch);
        $setHeaders($sheetPC);

        // 3. Masukkan Data ke Masing-Masing Sheet
        $rowAll = 2; $rowSwitch = 2; $rowPC = 2;
        $noAll = 1; $noSwitch = 1; $noPC = 1;

        foreach ($buildings as $building) {
            foreach ($building->floors as $floor) {
                
                // Distribusi Data Switch
                foreach ($floor->switchNodes as $switch) {
                    $data = [
                        '', 
                        $building->name, 
                        $floor->name, 
                        'Switch / Router', 
                        $switch->name, 
                        $switch->ip_address ?? '-', 
                        $switch->brand_model ?? '-',
                        '-', // Switch tidak punya status aktif/rusak di DB kita
                        $switch->installation_year ?? '-'
                    ];
                    
                    // Masuk ke Sheet All
                    $data[0] = $noAll++;
                    $sheetAll->fromArray($data, NULL, 'A' . $rowAll++);
                    
                    // Masuk ke Sheet Khusus Switch
                    $data[0] = $noSwitch++;
                    $sheetSwitch->fromArray($data, NULL, 'A' . $rowSwitch++);
                }
                
                // Distribusi Data PC
                foreach ($floor->pcNodes as $pc) {
                    $data = [
                        '', 
                        $building->name, 
                        $floor->name, 
                        'PC / Client', 
                        $pc->name, 
                        $pc->ip_address ?? '-', 
                        $pc->current_user ?? '-',
                        strtoupper($pc->status ?? '-'),
                        $pc->installation_year ?? '-'
                    ];
                    
                    // Masuk ke Sheet All
                    $data[0] = $noAll++;
                    $sheetAll->fromArray($data, NULL, 'A' . $rowAll++);
                    
                    // Masuk ke Sheet Khusus PC
                    $data[0] = $noPC++;
                    $sheetPC->fromArray($data, NULL, 'A' . $rowPC++);
                }
            }
        }

        // 4. Rapihkan Lebar Kolom Otomatis di Semua Sheet (Sampai Kolom I)
        foreach ([$sheetAll, $sheetSwitch, $sheetPC] as $sheet) {
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        // 5. Eksekusi Download
        $fileName = 'Master_Audit_Inventory_KBK_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}