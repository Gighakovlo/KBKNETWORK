<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SwitchNode;
use App\Models\PcNode; // Panggil model PC
use App\Models\Floor;
use App\Models\Connection;

class SwitchController extends Controller
{
    // 1. Tampilkan mapping beserta Switch, PC, dan Kabel
    public function index($floor_id)
    {
        $floor = Floor::with('building')->findOrFail($floor_id);
        $switches = SwitchNode::where('floor_id', $floor_id)->get();
        $pcs = PcNode::where('floor_id', $floor_id)->get();
        
        $switchIds = $switches->pluck('id')->toArray();
        $pcIds = $pcs->pluck('id')->toArray();

        $connections = Connection::where(function ($query) use ($switchIds, $pcIds) {
            $query->where(function ($q) use ($switchIds) {
                $q->where('from_type', 'switch')->whereIn('from_id', $switchIds);
            })->orWhere(function ($q) use ($pcIds) {
                $q->where('from_type', 'pc')->whereIn('from_id', $pcIds);
            });
        })->get();
        
        return view('mapping', compact('floor', 'switches', 'pcs', 'connections'));
    }

    // --- FUNGSI CETAK LAPORAN (BARU) ---
    public function printReport($floor_id)
    {
        $floor = Floor::with('building')->findOrFail($floor_id);
        $switches = SwitchNode::where('floor_id', $floor_id)->get();
        $pcs = PcNode::where('floor_id', $floor_id)->get();
        
        $switchIds = $switches->pluck('id')->toArray();
        $pcIds = $pcs->pluck('id')->toArray();

        $connections = Connection::where(function ($query) use ($switchIds, $pcIds) {
            $query->where(function ($q) use ($switchIds) { $q->where('from_type', 'switch')->whereIn('from_id', $switchIds); })
                  ->orWhere(function ($q) use ($pcIds) { $q->where('from_type', 'pc')->whereIn('from_id', $pcIds); });
        })->get();

        // Format data koneksi agar mudah dibaca di tabel PDF
        $connectionDetails = $connections->map(function($conn) use ($switches, $pcs) {
            $fromName = $conn->from_type == 'switch' ? $switches->firstWhere('id', $conn->from_id)->name ?? '-' : $pcs->firstWhere('id', $conn->from_id)->name ?? '-';
            $toName = $conn->to_type == 'switch' ? $switches->firstWhere('id', $conn->to_id)->name ?? '-' : $pcs->firstWhere('id', $conn->to_id)->name ?? '-';

            $typeLabel = 'Switch - Switch';
            if ($conn->from_type == 'pc' && $conn->to_type == 'pc') $typeLabel = 'PC - PC';
            elseif ($conn->from_type != $conn->to_type) $typeLabel = 'Switch - PC';

            return (object) [
                'from' => $fromName . ' (' . strtoupper($conn->from_type) . ')',
                'to' => $toName . ' (' . strtoupper($conn->to_type) . ')',
                'type' => $typeLabel,
                'color' => $conn->color
            ];
        });

        return view('print-report', compact('floor', 'switches', 'pcs', 'connections', 'connectionDetails'));
    }

    // --- AREA SWITCH ---
    public function store(Request $request) {
        // 1. Validasi Ketat (Pertahanan Lapis Baja)
        $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => ['nullable', function ($attribute, $value, $fail) {
                // Jika isinya BUKAN 'belum ada' DAN BUKAN format IPv4, tendang!
                if (strtolower($value) !== 'belum ada' && !filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $fail('Format IP Switch tidak valid! Harus berupa angka (contoh: 192.168.1.1) atau pilih "Belum Ada".');
                }
            }],
            'installation_year' => 'nullable|numeric|digits:4',
        ]);

        // 2. Normalisasi Data IP
        $ip = (strtolower($request->ip_address) === 'Belum Ada' || empty($request->ip_address)) ? 'Belum Ada' : $request->ip_address;

        $switch = SwitchNode::create([
            'floor_id' => $request->floor_id, 
            'name' => $request->name, 
            'ip_address' => $ip, 
            'brand_model' => $request->brand_model, 
            'installation_year' => $request->installation_year,
            'pos_x' => 100, 
            'pos_y' => 100
        ]);
        return response()->json(['success' => true, 'data' => $switch]);
    }

    public function updatePosition(Request $request) {
        $switch = SwitchNode::find($request->id);
        if($switch) { $switch->pos_x = $request->pos_x; $switch->pos_y = $request->pos_y; $switch->save(); return response()->json(['success' => true]); }
    }
    public function destroy($id) {
        $switch = SwitchNode::find($id);
        if($switch) { $switch->delete(); return response()->json(['success' => true]); }
    }

    // --- AREA PC ---
    public function storePc(Request $request) {
        // 1. Validasi Ketat (Pertahanan Lapis Baja)
        $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => ['nullable', function ($attribute, $value, $fail) {
                if (strtolower($value) !== 'belum ada' && !filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $fail('Format IP PC tidak valid! Harus berupa angka (contoh: 192.168.1.1) atau pilih "Belum Ada".');
                }
            }],
            'status' => 'required|in:aktif,tidak digunakan,rusak',
            'installation_year' => 'nullable|numeric|digits:4',
        ]);

        // 2. Normalisasi Data IP
        $ip = (strtolower($request->ip_address) === 'Belum Ada' || empty($request->ip_address)) ? 'Belum Ada' : $request->ip_address;

        $pc = PcNode::create([
            'floor_id' => $request->floor_id, 
            'name' => $request->name, 
            'ip_address' => $ip, 
            'brand_model' => $request->brand_model, 
            'current_user' => $request->current_user, 
            'installation_year' => $request->installation_year,
            'status' => $request->status,
            'pos_x' => 150, 
            'pos_y' => 150
        ]);
        return response()->json(['success' => true, 'data' => $pc]);
    }
    public function updatePcPosition(Request $request) {
        $pc = PcNode::find($request->id);
        if($pc) { $pc->pos_x = $request->pos_x; $pc->pos_y = $request->pos_y; $pc->save(); return response()->json(['success' => true]); }
    }
    public function destroyPc($id) {
        $pc = PcNode::find($id);
        if($pc) { $pc->delete(); return response()->json(['success' => true]); }
    }

    // --- AREA KABEL UNIVERSAL ---
    public function storeConnection(Request $request)
    {
        // Logika Pewarnaan Kabel
        $color = '#ff0000'; // Default Merah (Switch ke Switch)
        if ($request->from_type == 'pc' && $request->to_type == 'pc') {
            $color = '#22c55e'; // Hijau (PC ke PC)
        } elseif ($request->from_type != $request->to_type) {
            $color = '#3b82f6'; // Biru (Switch ke PC)
        }

        $connection = Connection::create([
            'from_type' => $request->from_type,
            'from_id' => $request->from_id,
            'to_type' => $request->to_type,
            'to_id' => $request->to_id,
            'color' => $color,
        ]);

        return response()->json(['success' => true, 'data' => $connection]);
    }

    public function destroyConnection($id) {
        $connection = Connection::find($id);
        if ($connection) { $connection->delete(); return response()->json(['success' => true]); }
    }
}