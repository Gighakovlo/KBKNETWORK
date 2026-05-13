<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IpAddress;
use Illuminate\Pagination\LengthAwarePaginator;

class IpAddressController extends Controller
{
    // 1. Tampilkan Dashboard IPAM
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 50);
        
        // Bypass SQL Server 2008 Offset Error: Ambil semua data dulu (get), lalu potong di memori (paginate via Collection)
        $allIps = IpAddress::with('asset')->orderBy('ip_address', 'asc')->get();

        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $ips = new LengthAwarePaginator(
            $allIps->forPage($page, $perPage),
            $allIps->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Statistik untuk Dashboard
        $totalIp = IpAddress::count();
        $ipAvailable = IpAddress::where('status', 'available')->count();
        $ipInUse = IpAddress::where('status', 'in_use')->count();

        return view('inventory.ipam.index', compact('ips', 'perPage', 'totalIp', 'ipAvailable', 'ipInUse'));
    }

    // 2. Simpan IP Baru ke Database
    public function store(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ipv4|unique:ip_addresses,ip_address',
            'gateway' => 'nullable|ipv4',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:available,reserved' // Saat baru buat, hanya bisa available/reserved. in_use otomatis kalau ditempel ke aset.
        ]);

        try {
            IpAddress::create([
                'ip_address' => $request->ip_address,
                'gateway' => $request->gateway,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            return redirect()->back()->with('success', "IP {$request->ip_address} berhasil didaftarkan ke sistem!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan IP: ' . $e->getMessage());
        }
    }

    // 3. Hapus IP
    public function destroy($id)
    {
        $ip = IpAddress::findOrFail($id);
        
        // KITA CABUT LOGIKA PENGECEKAN 'IN_USE' DI SINI
        // Agar Tuan Gigha bebas menghapus IP sampah apapun secara paksa!
        
        $ip->delete();

        return response()->json(['success' => true, 'message' => 'IP Address berhasil dimusnahkan permanen!']);
    }

    // 4. Update Data IP Address
    public function update(Request $request, $id)
    {
        $ip = IpAddress::findOrFail($id);
        
        $request->validate([
            // Validasi unik kecuali untuk ID miliknya sendiri
            'ip_address' => 'required|ipv4|unique:ip_addresses,ip_address,' . $id,
            'gateway' => 'nullable|ipv4',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:available,reserved'
        ]);

        try {
            // Logika Pintar: Jika IP sedang menempel di aset, paksa statusnya tetap 'in_use'
            $finalStatus = $ip->asset_id ? 'in_use' : $request->status;

            $ip->update([
                'ip_address' => $request->ip_address,
                'gateway' => $request->gateway,
                'description' => $request->description,
                'status' => $finalStatus,
            ]);

            return redirect()->back()->with('success', "Data IP {$ip->ip_address} berhasil diperbarui!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui IP: ' . $e->getMessage());
        }
    }

    // 5. Fitur Sapu Jagat: Sinkronisasi Data IP Lama
    public function syncOldData()
    {
        $assets = \App\Models\Asset::with('values.field')->get();
        $count = 0;

        foreach ($assets as $asset) {
            foreach ($asset->values as $valObj) {
                if ($valObj->field && (stripos($valObj->field->field_name, 'IP') !== false || stripos($valObj->field->field_name, 'Alamat IP') !== false)) {
                    $ipValue = trim($valObj->value);
                    if (!empty($ipValue)) {
                        IpAddress::updateOrCreate(
                            ['asset_id' => $asset->id],
                            ['ip_address' => $ipValue, 'status' => 'in_use']
                        );
                        $count++;
                    }
                }
            }
        }
        return redirect()->back()->with('success', "Sapu Jagat berhasil! $count IP dari aset lama telah masuk ke radar IP Manager.");
    }
}