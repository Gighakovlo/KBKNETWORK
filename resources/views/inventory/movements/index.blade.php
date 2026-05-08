<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Mutasi Barang - KBK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0b1120; }
        .glass-panel { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
</head>
<body class="text-slate-300 font-sans h-screen flex flex-col overflow-hidden">

    <nav class="p-6 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 flex justify-between items-center z-20 shadow-xl">
        <div class="flex items-center gap-6">
            <a href="{{ route('inventory.index') }}" class="text-slate-500 hover:text-white font-bold transition flex items-center gap-2">
                <span class="text-xl">&larr;</span> Kembali
            </a>
            <div class="h-8 w-px bg-slate-700"></div>
            <div>
                <h1 class="text-2xl font-black text-white uppercase tracking-widest drop-shadow-md">Log Mutasi Barang</h1>
                <p class="text-amber-500 text-xs mt-1 font-bold tracking-wide uppercase">Asset Movement Tracker</p>
            </div>
        </div>
    </nav>

    <main class="flex-grow p-8 overflow-y-auto custom-scrollbar">
        <div class="max-w-7xl mx-auto glass-panel rounded-2xl overflow-hidden shadow-2xl border border-slate-700/50">
            <div class="p-4 border-b border-slate-700 flex justify-between items-center bg-slate-900/50">
                <div class="relative group w-64">
                    <input type="text" id="searchInput" placeholder="Cari Kode Aset / Nama..." class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-xl text-sm focus:border-amber-500 focus:outline-none transition-all shadow-inner">
                    <span class="absolute right-3 top-2 text-slate-500">🔍</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <label class="text-[10px] font-bold text-slate-500 uppercase">Show:</label>
                        <select onchange="window.location.href='?per_page=' + this.value" class="bg-slate-900 border border-slate-700 text-white px-2 py-2 rounded-xl text-xs focus:outline-none transition-all cursor-pointer">
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="text-slate-300 uppercase tracking-widest text-[10px] border-b-2 border-slate-600 bg-slate-900">
                        <tr>
                            <th class="p-4 font-bold">Waktu Mutasi</th>
                            <th class="p-4 font-bold">ID / Nama Aset</th>
                            <th class="p-4 font-bold">Perubahan Pengguna (User)</th>
                            <th class="p-4 font-bold">Perubahan Lokasi</th>
                            <th class="p-4 font-bold">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="divide-y divide-slate-700/80">
                        @forelse($movements as $log)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-4 text-xs font-mono text-slate-400">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y - H:i') }}</td>
                                <td class="p-4">
                                    @if($log->asset)
                                        <a href="{{ route('inventory.edit', $log->asset_id) }}" class="font-bold text-amber-500 hover:text-amber-400 transition">{{ $log->asset->asset_code }}</a>
                                        <span class="text-slate-500 text-xs ml-2">({{ $log->asset->name }})</span>
                                    @else
                                        <span class="text-red-500 italic text-xs font-bold">[Aset Telah Dimusnahkan]</span>
                                    @endif
                                </td>
                                <td class="p-4 text-xs">
                                    @if($log->previous_user !== $log->new_user)
                                        <span class="text-red-400 line-through mr-2">{{ $log->previous_user }}</span> 
                                        <span class="text-slate-500">➔</span> 
                                        <span class="text-emerald-400 font-bold ml-2">{{ $log->new_user }}</span>
                                    @else
                                        <span class="text-slate-600 italic">Tidak ada perubahan</span>
                                    @endif
                                </td>
                                <td class="p-4 text-xs">
                                    @if($log->previous_location !== $log->new_location)
                                        <span class="text-red-400 line-through mr-2">{{ $log->previous_location }}</span> 
                                        <span class="text-slate-500">➔</span> 
                                        <span class="text-emerald-400 font-bold ml-2">{{ $log->new_location }}</span>
                                    @else
                                        <span class="text-slate-600 italic">Tidak ada perubahan</span>
                                    @endif
                                </td>
                                <td class="p-4 text-xs text-slate-400">{{ $log->reason }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-10 text-center text-slate-500 font-bold">Belum ada catatan mutasi/perpindahan barang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-800">{{ $movements->links() }}</div>
        </div>
    </main>

    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#tableBody tr');
            rows.forEach(row => {
                if(row.innerText.includes("Belum ada catatan")) return;
                row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>
</html>