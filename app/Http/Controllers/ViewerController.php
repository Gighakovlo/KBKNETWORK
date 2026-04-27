<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Floor;
use App\Models\SwitchNode;
use App\Models\PcNode; // PENTING: Tambahkan ini
use App\Models\Connection;

class ViewerController extends Controller
{
    public function index()
    {
        $buildings = Building::with('floors')->get();
        return view('viewer-lobby', compact('buildings'));
    }

    public function mapping($floor_id)
    {
        $floor = Floor::with('building')->findOrFail($floor_id);
        $switches = SwitchNode::where('floor_id', $floor_id)->get();
        $pcs = PcNode::where('floor_id', $floor_id)->get(); // Ambil data PC
        
        $switchIds = $switches->pluck('id')->toArray();
        $pcIds = $pcs->pluck('id')->toArray();

        // Logika pembacaan kabel universal
        $connections = Connection::where(function ($query) use ($switchIds, $pcIds) {
            $query->where(function ($q) use ($switchIds) {
                $q->where('from_type', 'switch')->whereIn('from_id', $switchIds);
            })->orWhere(function ($q) use ($pcIds) {
                $q->where('from_type', 'pc')->whereIn('from_id', $pcIds);
            });
        })->get();
        
        return view('viewer-mapping', compact('floor', 'switches', 'pcs', 'connections'));
    }
}