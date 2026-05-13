@extends('layouts.itam')

@section('title', 'IT Ticketing System - ITAM KBK')

@section('content')
    <nav class="p-6 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 flex justify-between items-center z-20 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="w-3 h-3 bg-fuchsia-500 rounded-full animate-pulse"></div>
            <div>
                <h2 class="text-lg font-black text-white uppercase tracking-widest">IT Ticketing System</h2>
                <p class="text-fuchsia-400 text-[10px] mt-1 font-bold tracking-wide uppercase">Asset & Network Requests</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <button onclick="document.getElementById('modalRequest').classList.remove('hidden')" class="bg-fuchsia-600/20 border border-fuchsia-500 text-fuchsia-400 hover:bg-fuchsia-600 hover:text-white px-5 py-2.5 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(217,70,239,0.2)]">
                + Buat Tiket Permintaan Baru
            </button>
        </div>
    </nav>

    <main class="flex-grow p-8 overflow-y-auto custom-scrollbar flex flex-col gap-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 shrink-0">
            <div class="glass-panel p-6 rounded-2xl border-t-4 border-amber-500 relative overflow-hidden shadow-xl">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-500/10 rounded-full blur-xl"></div>
                <p class="text-xs text-amber-400 uppercase tracking-widest font-bold mb-1">Tiket Pending (Menunggu Tindakan)</p>
                <p class="text-4xl font-black text-amber-400">{{ $pendingCount }}</p>
            </div>
            <div class="glass-panel p-6 rounded-2xl border-t-4 border-emerald-500 relative overflow-hidden shadow-xl">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl"></div>
                <p class="text-xs text-emerald-400 uppercase tracking-widest font-bold mb-1">Tiket Selesai (Tereksekusi)</p>
                <p class="text-4xl font-black text-emerald-400">{{ $completedCount }}</p>
            </div>
        </div>

        <div class="glass-panel rounded-2xl flex-grow overflow-hidden flex flex-col shadow-2xl border border-slate-700/50">
            <div class="p-4 border-b border-slate-700 flex justify-between items-center bg-slate-900/50">
                <div class="relative group w-72">
                    <input type="text" id="searchInput" placeholder="Cari Nama / Departemen..." class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-xl text-sm focus:border-fuchsia-500 focus:outline-none transition-all shadow-inner">
                    <span class="absolute right-3 top-2.5 text-slate-500">🔍</span>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase">Show:</label>
                    <select onchange="window.location.href='?per_page=' + this.value" class="bg-slate-900 border border-slate-700 text-white px-2 py-2 rounded-xl text-xs focus:outline-none transition-all cursor-pointer">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>
            
            <div class="overflow-x-auto overflow-y-auto custom-scrollbar flex-grow">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="text-slate-300 uppercase tracking-widest text-[10px] border-b-2 border-slate-600 bg-slate-900 sticky top-0">
                        <tr>
                            <th class="p-4 font-bold pl-6">Waktu Masuk</th>
                            <th class="p-4 font-bold">Pemohon</th>
                            <th class="p-4 font-bold">Kategori Permintaan</th>
                            <th class="p-4 font-bold">Deskripsi</th>
                            <th class="p-4 font-bold">Status</th>
                            <th class="p-4 font-bold text-right pr-6">Aksi Eksekusi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="divide-y divide-slate-700/80">
                        @forelse($requests as $req)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-4 pl-6 text-xs font-mono text-slate-400">{{ \Carbon\Carbon::parse($req->created_at)->format('d M Y H:i') }}</td>
                                <td class="p-4">
                                    <p class="font-bold text-fuchsia-400">{{ $req->requester_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $req->department }}</p>
                                </td>
                                <td class="p-4 font-bold">{{ $req->request_type }}</td>
                                <td class="p-4 text-xs text-slate-400 whitespace-normal min-w-[200px]">{{ $req->description }}</td>
                                <td class="p-4">
                                    @if($req->status == 'pending')
                                        <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider bg-amber-900/50 text-amber-400 border border-amber-500/30 animate-pulse">Menunggu</span>
                                    @else
                                        <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-900/50 text-emerald-400 border border-emerald-500/30">Selesai</span>
                                    @endif
                                </td>
                                <td class="p-4 pr-6 text-right space-x-2">
                                    @if($req->status == 'pending')
                                        <button onclick="markCompleted('{{ $req->id }}')" class="text-emerald-400 hover:text-white bg-emerald-900/20 hover:bg-emerald-600 border border-emerald-900/50 font-bold px-3 py-1 rounded transition text-xs">Selesaikan</button>
                                    @endif
                                    <button onclick="confirmDelete('{{ $req->id }}')" class="text-slate-500 hover:text-red-400 font-bold px-3 py-1 transition text-xs border border-transparent hover:border-red-900/50 rounded">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-10 text-center text-slate-500 font-bold">Semua bersih! Tidak ada antrean permintaan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-800 bg-slate-900/50 shrink-0">{{ $requests->links() }}</div>
        </div>
    </main>

    <div id="modalRequest" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden flex justify-center items-center z-50">
        <div class="glass-panel p-8 rounded-2xl shadow-2xl border-t-4 border-fuchsia-500 w-full max-w-md relative">
            <button onclick="document.getElementById('modalRequest').classList.add('hidden')" class="absolute top-4 right-5 text-slate-500 hover:text-red-400 font-black text-xl transition">&times;</button>
            <h2 class="text-xl font-black text-white mb-6 uppercase tracking-widest">Buat Tiket Permintaan</h2>
            
            <form action="{{ route('requests.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">Nama Pemohon *</label>
                        <input type="text" name="requester_name" required class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-fuchsia-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">Divisi / Dept *</label>
                        <input type="text" name="department" required class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-fuchsia-500 transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Kategori Permintaan *</label>
                    <select name="request_type" required class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-fuchsia-500 transition">
                        <option value="Permintaan Barang Baru">📦 Permintaan Barang Baru</option>
                        <option value="Perbaikan Aset (Service)">🔧 Perbaikan Aset (Service)</option>
                        <option value="Kebutuhan Jaringan / Kabel">🌐 Kebutuhan Jaringan / Kabel</option>
                        <option value="Lainnya">Lainnya...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Penjelasan Detail *</label>
                    <textarea name="description" required rows="3" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-fuchsia-500 transition" placeholder="Cth: Butuh kabel LAN 5 meter untuk ruang meeting B..."></textarea>
                </div>
                <button type="submit" class="w-full mt-4 bg-fuchsia-600/20 hover:bg-fuchsia-600 text-fuchsia-400 hover:text-white border border-fuchsia-500/50 py-3 rounded-lg font-bold uppercase tracking-widest text-sm transition-all shadow-[0_0_15px_rgba(217,70,239,0.3)]">
                    Kirim Tiket
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showToast(msg, colorClass) {
        const t = document.getElementById('toast');
        if(t) {
            t.innerText = msg;
            t.className = `fixed top-6 right-6 px-6 py-4 rounded-xl shadow-2xl transition-all duration-300 z-[9999] font-bold text-sm text-white transform translate-y-0 opacity-100 ${colorClass}`;
            setTimeout(() => { t.classList.remove('translate-y-0', 'opacity-100'); t.classList.add('-translate-y-4', 'opacity-0'); }, 4000);
        }
    }

    @if(session('success')) showToast("{{ session('success') }}", 'bg-emerald-600'); @endif

    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#tableBody tr');
        rows.forEach(row => {
            if(row.innerText.includes("Semua bersih")) return;
            row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
        });
    });

    async function markCompleted(id) {
        if(confirm('Tandai permintaan ini sudah diselesaikan?')) {
            try {
                const res = await fetch(`/inventory/requests/${id}/complete`, {
                    method: 'PUT',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
                });
                const r = await res.json();
                if(r.success) { showToast(r.message, 'bg-emerald-600'); setTimeout(() => location.reload(), 1000); }
            } catch(err) { showToast('Gagal terhubung ke server.', 'bg-red-600'); }
        }
    }

    async function confirmDelete(id) {
        if(confirm('Hapus tiket ini permanen?')) {
            try {
                const res = await fetch(`/inventory/requests/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
                });
                const r = await res.json();
                if(r.success) { showToast(r.message, 'bg-emerald-600'); setTimeout(() => location.reload(), 1000); }
            } catch(err) { showToast('Gagal menghapus.', 'bg-red-600'); }
        }
    }
</script>
@endpush