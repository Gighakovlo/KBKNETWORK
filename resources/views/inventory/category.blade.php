@extends('layouts.itam')

@section('title', 'Data ' . $category->name . ' - ITAM KBK')

@section('content')
    <nav class="p-6 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 flex justify-between items-center z-20 shadow-lg">
        <div class="flex items-center gap-4">
            <a href="{{ route('inventory.index') }}" class="text-slate-500 hover:text-white font-bold transition flex items-center gap-2 mr-2">
                <span class="text-xl">&larr;</span> Kembali
            </a>
            <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
            <div>
                <h2 class="text-lg font-black text-white uppercase tracking-widest">Data {{ $category->name }}</h2>
                <p class="text-blue-400 text-[10px] mt-1 font-bold tracking-wide uppercase">Kode Prefix: {{ $category->prefix }}-XXXX</p>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-[10px] font-bold text-slate-500 uppercase">Show:</label>
                <select onchange="window.location.href='?per_page=' + this.value + '&search={{ $search ?? '' }}&sort={{ $sort ?? 'desc' }}'" class="bg-slate-900 border border-slate-700 text-white px-2 py-2 rounded-xl text-xs focus:outline-none transition-all cursor-pointer">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
            
            <form action="{{ route('inventory.category', $category->id) }}" method="GET" class="relative group">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <input type="hidden" name="sort" value="{{ $sort ?? 'desc' }}">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari di kategori ini..." class="bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-xl text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 w-64 transition-all shadow-inner placeholder-slate-500">
                <button type="submit" class="absolute right-3 top-2 text-slate-500 hover:text-blue-500 transition-colors">🔍</button>
            </form>

            <button id="btnBulkDelete" class="hidden bg-red-600/20 border border-red-500 text-red-400 hover:bg-red-600 hover:text-white px-4 py-2 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(220,38,38,0.2)]">
                🗑️ Hapus (<span id="selectedCount">0</span>)
            </button>

            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="bg-amber-600/20 border border-amber-500 text-amber-400 hover:bg-amber-600 hover:text-white px-4 py-2 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(245,158,11,0.2)]">
                📥 Import
            </button>
            <a href="{{ route('inventory.export', $category->id) }}" class="bg-emerald-600/20 border border-emerald-500 text-emerald-400 hover:bg-emerald-600 hover:text-white px-4 py-2 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(16,185,129,0.2)]">
                📤 Export
            </a>
        </div>
    </nav>

    <main class="flex-grow p-8 overflow-hidden flex flex-col">
        <div class="glass-panel rounded-2xl flex-grow overflow-hidden flex flex-col shadow-2xl border border-slate-700/50">
            <div class="overflow-x-auto overflow-y-auto custom-scrollbar flex-grow p-4">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="text-slate-300 uppercase tracking-widest text-[10px] border-b-2 border-slate-600 bg-slate-900 sticky top-0 z-10 shadow-md">
                        <tr>
                            <th class="p-4 rounded-tl-lg w-10"><input type="checkbox" id="selectAll" class="w-4 h-4 rounded bg-slate-900 border-slate-600 text-blue-600 focus:ring-blue-500 cursor-pointer"></th>
                            
                            <th class="p-4 font-bold">
                                <div class="flex items-center gap-2">
                                    ID Barang
                                    <div class="flex flex-col text-[8px]">
                                        <a href="?per_page={{ $perPage }}&search={{ $search ?? '' }}&sort=asc" class="{{ ($sort ?? '') == 'asc' ? 'text-blue-500' : 'text-slate-500 hover:text-white' }} leading-none">▲</a>
                                        <a href="?per_page={{ $perPage }}&search={{ $search ?? '' }}&sort=desc" class="{{ ($sort ?? 'desc') == 'desc' ? 'text-blue-500' : 'text-slate-500 hover:text-white' }} leading-none">▼</a>
                                    </div>
                                </div>
                            </th>
                            
                            <th class="p-4 font-bold">Nama (Hostname)</th>

                            @if($category->has_ip)
                                <th class="p-4 font-bold text-emerald-400">IP Address</th>
                            @endif

                            <th class="p-4 font-bold">Merek / Model</th>
                            <th class="p-4 font-bold">Lokasi</th>
                            <th class="p-4 font-bold">Pengguna</th>
                            <th class="p-4 font-bold">Status</th>
                            
                            @foreach($category->fields as $field)
                                <th class="p-4 font-bold text-blue-400">{{ $field->field_name }}</th>
                            @endforeach
                            
                            <th class="p-4 font-bold text-amber-400">Keterangan</th>
                            <th class="p-4 font-bold text-right rounded-tr-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="divide-y divide-slate-700">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-4">
                                    <input type="checkbox" class="row-checkbox w-4 h-4 rounded bg-slate-900 border-slate-600 text-blue-600 focus:ring-blue-500 cursor-pointer" value="{{ $asset->id }}">
                                </td>
                                <td class="p-4 font-mono font-bold text-emerald-400">{{ $asset->asset_code }}</td>
                                <td class="p-4 font-bold text-white">{{ $asset->name }}</td>

                                @if($category->has_ip)
                                    <td class="p-4 font-mono font-bold text-emerald-300">
                                        {{ $asset->ipAddress->ip_address ?? '-' }}
                                    </td>
                                @endif

                                <td class="p-4">{{ $asset->brand_model ?? '-' }}</td>
                                <td class="p-4 text-xs">
                                    @if($asset->building)
                                        <span class="block font-bold text-slate-300">{{ $asset->building->name }}</span>
                                        <span class="text-slate-500">{{ $asset->floor ? $asset->floor->name : '' }}</span>
                                    @else
                                        <span class="text-slate-500 italic">Belum ditempatkan</span>
                                    @endif
                                </td>
                                <td class="p-4">{{ $asset->current_user ?? '-' }}</td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider
                                        {{ $asset->status === 'aktif' ? 'bg-emerald-900/50 text-emerald-400' : 
                                          ($asset->status === 'rusak' ? 'bg-red-900/50 text-red-400' : 'bg-slate-700 text-slate-300') }}">
                                        {{ $asset->status }}
                                    </span>
                                </td>

                                @foreach($category->fields as $field)
                                    @php $valObj = $asset->values->firstWhere('category_field_id', $field->id); @endphp
                                    <td class="p-4 text-blue-100">{{ $valObj ? $valObj->value : '-' }}</td>
                                @endforeach

                                <td class="p-4 text-xs text-slate-400 max-w-[200px] truncate" title="{{ $asset->description }}">{{ $asset->description ?? '-' }}</td>

                                <td class="p-4 text-right space-x-2">
                                    <a href="{{ route('inventory.edit', $asset->id) }}" class="text-blue-400 hover:text-blue-300 font-bold px-3 py-1 rounded border border-blue-900/50 hover:bg-blue-900/20 transition text-xs inline-block">Edit</a>
                                    <button onclick="confirmDelete('{{ $asset->id }}', '{{ $asset->asset_code }}')" class="text-red-400 hover:text-red-300 font-bold px-3 py-1 rounded border border-red-900/50 hover:bg-red-900/20 transition text-xs">Hapus</button>
                                    <a href="{{ route('inventory.print.single', $asset->id) }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 font-bold px-3 py-1 transition text-xs border border-indigo-900/50 rounded hover:bg-indigo-900/20">
                                        Cetak PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                @php $colCount = 9 + $category->fields->count() + ($category->has_ip ? 1 : 0); @endphp
                                <td colspan="{{ $colCount }}" class="p-10 text-center text-slate-500 font-bold">
                                    @if(!empty($search))
                                        Tidak ada perangkat yang cocok dengan pencarian "{{ $search }}".
                                    @else
                                        Belum ada data perangkat di kategori ini.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-6 p-4 border-t border-slate-800">
                    {{ $assets->links() }}
                </div>
            </div>
        </div>
    </main>

    <div id="importModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden flex justify-center items-center z-50">
        <div class="glass-panel p-8 rounded-2xl shadow-2xl border-t-4 border-amber-500 w-full max-w-md relative">
            <button onclick="document.getElementById('importModal').classList.add('hidden')" class="absolute top-4 right-5 text-slate-500 hover:text-red-400 font-black text-xl transition">&times;</button>
            <h2 class="text-xl font-black text-white mb-2">Import Data {{ $category->name }}</h2>
            <p class="text-xs text-slate-400 mb-6 border-b border-slate-700 pb-4">Unduh template, isi data perangkat, lalu unggah kembali ke sistem.</p>
            <a href="{{ route('inventory.template', $category->id) }}" class="w-full block text-center bg-slate-800 border border-blue-500/50 text-blue-400 hover:bg-blue-600 hover:text-white py-3 rounded-lg font-bold transition text-sm mb-6">
                📄 Download Template Excel Kosong
            </a>
            <form action="{{ route('inventory.import', $category->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-2">Upload File Excel (.xlsx)</label>
                    <input type="file" name="excel_file" required accept=".xlsx, .xls" class="w-full bg-slate-900 border border-slate-700 text-slate-300 px-4 py-2 rounded-lg text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-bold file:bg-amber-600 file:text-white hover:file:bg-amber-500 cursor-pointer">
                </div>
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-500 text-white font-black uppercase tracking-widest py-3 rounded-lg shadow-[0_0_15px_rgba(245,158,11,0.3)] transition-all">
                    Mulai Import Data
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
    @if(session('error')) showToast("{!! addslashes(session('error')) !!}", 'bg-red-600'); @endif

    async function confirmDelete(id, code) {
        if (confirm(`Peringatan Keras! Yakin ingin menghapus aset ${code}? Data spesifikasi di dalamnya juga akan hilang permanen.`)) {
            try {
                const response = await fetch(`/inventory/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
                });
                const r = await response.json();
                if(r.success) { showToast(r.message, 'bg-emerald-600'); setTimeout(() => location.reload(), 1000); } 
                else { showToast(r.message, 'bg-red-600'); }
            } catch (err) { showToast('Gagal terhubung ke server.', 'bg-red-600'); }
        }
    }

    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const selectedCount = document.getElementById('selectedCount');

    function updateBulkDeleteUI() {
        const checkedBoxes = Array.from(rowCheckboxes).filter(cb => cb.checked);
        if (checkedBoxes.length > 0) { btnBulkDelete.classList.remove('hidden'); selectedCount.innerText = checkedBoxes.length; } 
        else { btnBulkDelete.classList.add('hidden'); }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => { cb.checked = selectAll.checked; });
            updateBulkDeleteUI();
        });
    }

    rowCheckboxes.forEach(cb => { cb.addEventListener('change', updateBulkDeleteUI); });

    if (btnBulkDelete) {
        btnBulkDelete.addEventListener('click', async function() {
            const selectedIds = Array.from(rowCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
            if (confirm(`PERINGATAN KERAS! Anda yakin ingin menghapus ${selectedIds.length} aset secara permanen?`)) {
                try {
                    const response = await fetch('{{ route('inventory.bulkDelete') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                        body: JSON.stringify({ ids: selectedIds })
                    });
                    const r = await response.json();
                    if (r.success) { showToast(r.message, 'bg-emerald-600'); setTimeout(() => location.reload(), 1500); } 
                    else { showToast(r.message, 'bg-red-600'); }
                } catch (err) { showToast('Gagal terhubung ke markas.', 'bg-red-600'); }
            }
        });
    }
</script>
@endpush