<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class AssetRequestController extends Controller
{
    // 1. Tampilkan Halaman Ticketing
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 50);
        
        // Tarik semua request, yang pending di atas, lalu urutkan dari yang terbaru
        $allRequests = AssetRequest::orderBy('status', 'desc') // 'pending' > 'completed' secara alfabet, jadi pending naik ke atas
                                   ->orderBy('created_at', 'desc')
                                   ->get();

        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $requests = new LengthAwarePaginator(
            $allRequests->forPage($page, $perPage),
            $allRequests->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $pendingCount = AssetRequest::where('status', 'pending')->count();
        $completedCount = AssetRequest::where('status', 'completed')->count();

        return view('inventory.requests.index', compact('requests', 'perPage', 'pendingCount', 'completedCount'));
    }

    // 2. Simpan Request Baru
    public function store(Request $request)
    {
        $request->validate([
            'requester_name' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'request_type' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        AssetRequest::create($request->all());

        return redirect()->back()->with('success', 'Permintaan berhasil dikirim ke ruang kendali IT!');
    }

    // 3. Eksekusi Request (Ubah Status ke Completed)
    public function markAsCompleted($id)
    {
        try {
            $ticket = AssetRequest::findOrFail($id);
            $ticket->update(['status' => 'completed']);
            return response()->json(['success' => true, 'message' => "Tiket dari {$ticket->requester_name} berhasil ditandai selesai!"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate status: ' . $e->getMessage()], 500);
        }
    }

    // 4. Hapus Request
    public function destroy($id)
    {
        try {
            AssetRequest::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => "Tiket telah dimusnahkan!"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus tiket.'], 500);
        }
    }
}