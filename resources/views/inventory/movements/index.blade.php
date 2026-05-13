@extends('layouts.itam')

@section('title', 'Log Mutasi Barang - ITAM KBK')

@section('content')
    <nav class="p-6 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 flex justify-between items-center z-20 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="w-3 h-3 bg-amber-500 rounded-full animate-pulse"></div>
            <div>
                <h2 class="text-lg font-black text-white uppercase tracking-widest">Log Mutasi Barang</h2>
                <p class="text-amber-500 text-[10px] mt-1 font-bold tracking-wide uppercase">Asset Movement Tracker</p>
            </div>
        </div>
    </nav>

    <main class="flex-grow p-8 overflow-hidden flex flex-col">
        <div class="glass-panel rounded-2xl flex-grow overflow-hidden flex flex-col shadow-2xl border border-slate-700/50">
            <div class="p-4 border-b border-slate-700 flex justify-between items-center bg-slate-900/50">
                <div class="relative group w-72">
                    <input type="text" id="searchInput" placeholder="Cari Kode Aset / Nama..." class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-xl text-sm focus:border-amber-500 focus:outline-none transition-all shadow-inner">
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
                            <th class="p-4 font-bold pl-6">Waktu Mutasi</th>
                            <th class="p-4 font-bold">ID / Nama Aset</th>
                            <th class="p-4 font-bold">Perubahan Pengguna (User)</th>
                            <th class="p-4 font-bold">Perubahan Lokasi</th>
                            <th class="p-4 font-bold pr-6">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="divide-y divide-slate-700/80">
                        @forelse($movements as $log)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-4 pl-6 text-xs font-mono text-slate-400">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y - H:i') }}</td>
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
                                <td class="p-4 pr-6 text-xs text-slate-400">{{ $log->reason }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-10 text-center text-slate-500 font-bold border border-dashed border-slate-700 m-4 rounded-xl">Belum ada catatan mutasi/perpindahan barang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-800 bg-slate-900/50 shrink-0">{{ $movements->links() }}</div>
        </div>
    </main>
@endsection

@push('scripts')
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
@endpush