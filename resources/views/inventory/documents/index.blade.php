@extends('layouts.itam')

@section('title', 'Arsip Dokumen - ITAM KBK')

@section('content')
    <nav class="p-6 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 flex justify-between items-center z-20 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="w-3 h-3 bg-amber-500 rounded-full animate-pulse"></div>
            <div>
                <h2 class="text-lg font-black text-white uppercase tracking-widest">Arsip Dokumen</h2>
                <p class="text-amber-400 text-[10px] mt-1 font-bold tracking-wide uppercase">Pusat Data & Berkas Global</p>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <button onclick="document.getElementById('modalAddDoc').classList.remove('hidden')" class="bg-amber-600/20 border border-amber-500 text-amber-400 hover:bg-amber-600 hover:text-white px-5 py-2.5 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(245,158,11,0.2)]">
                + Upload Dokumen Baru
            </button>
        </div>
    </nav>

    <main class="flex-grow p-8 overflow-y-auto custom-scrollbar flex flex-col">
        <div class="glass-panel rounded-2xl flex-grow overflow-hidden flex flex-col shadow-2xl border border-slate-700/50">
            <div class="p-4 border-b border-slate-700 flex justify-between items-center bg-slate-900/50">
                <form action="{{ route('documents.index') }}" method="GET" class="relative group w-72">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari Dokumen..." class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-xl text-sm focus:border-amber-500 focus:outline-none transition-all shadow-inner">
                    <button type="submit" class="absolute right-3 top-2.5 text-slate-500 hover:text-white">🔍</button>
                </form>
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
                            
                            <th class="p-4 font-bold">Judul Dokumen</th>
                            <th class="p-4 font-bold">Nama Asli File</th>
                            <th class="p-4 font-bold">Kategori</th>
                            <th class="p-4 font-bold">Keterangan</th>
                            <th class="p-4 font-bold">Tanggal Diupload</th>
                            <th class="p-4 font-bold text-right pr-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/80">
                        @forelse($documents as $doc)
                            <tr class="hover:bg-slate-800/40 transition">
                                
                                <td class="p-4 font-bold text-white flex items-center gap-3">
                                    <span class="text-2xl">📄</span>
                                    <a href="{{ asset($doc->file_path) }}" target="_blank" class="hover:text-amber-400 transition">{{ $doc->title }}</a>
                                </td>
                                <td class="p-4 text-xs text-slate-400 font-mono max-w-xs truncate" title="{{ $doc->original_filename }}">{{ $doc->original_filename ?? '-' }}</td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider bg-slate-800 text-amber-400 border border-amber-500/30">{{ $doc->category }}</span>
                                </td>
                                <td class="p-4 text-xs text-slate-400 max-w-xs truncate">{{ $doc->description ?? '-' }}</td>
                                <td class="p-4 text-xs text-slate-400 font-mono">{{ $doc->created_at->format('d M Y - H:i') }}</td>
                                <td class="p-4 pr-6 text-right space-x-2">
                                    <a href="{{ asset($doc->file_path) }}" target="_blank" class="text-emerald-400 hover:text-emerald-300 font-bold px-3 py-1 transition text-xs border border-emerald-900/50 rounded hover:bg-emerald-900/20">Unduh</a>
                                    <button onclick="openEditModal({{ $doc->id }}, '{{ addslashes($doc->title) }}', '{{ addslashes($doc->category) }}', '{{ addslashes($doc->description) }}', '{{ addslashes($doc->original_filename) }}')" class="text-blue-400 hover:text-blue-300 font-bold px-3 py-1 transition text-xs border border-blue-900/50 rounded hover:bg-blue-900/20">Edit</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-10 text-center text-slate-500 font-bold border border-dashed border-slate-700 m-4 rounded-xl">Belum ada dokumen yang diarsipkan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-800 bg-slate-900/50 shrink-0">{{ $documents->links() }}</div>
        </div>
    </main>

    <div id="modalAddDoc" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden flex justify-center items-center z-50">
        <div class="glass-panel p-8 rounded-2xl shadow-2xl border-t-4 border-amber-500 w-full max-w-md relative">
            <button onclick="document.getElementById('modalAddDoc').classList.add('hidden')" class="absolute top-4 right-5 text-slate-500 hover:text-red-400 font-black text-xl transition">&times;</button>
            <h2 class="text-xl font-black text-white mb-6 uppercase tracking-widest">Upload Dokumen</h2>
            
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Judul Dokumen *</label>
                    <input type="text" name="title" required placeholder="Cth: SOP Server Room" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-amber-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Kategori (Opsional)</label>
                    <input type="text" name="category" placeholder="Cth: Manual, Invoice, Lisensi" list="catList" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-amber-500 transition">
                    <datalist id="catList">
                        <option value="SOP">
                        <option value="Manual Book">
                        <option value="Invoice">
                        <option value="Garansi">
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Pilih File * (Max 10MB)</label>
                    <input type="file" name="document_file" required class="w-full bg-slate-950 border border-slate-700 text-slate-300 px-3 py-2 rounded-lg text-xs file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-amber-600 file:text-white hover:file:bg-amber-500 cursor-pointer transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Keterangan Tambahan</label>
                    <textarea name="description" rows="2" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-amber-500 transition"></textarea>
                </div>
                <button type="submit" class="w-full mt-4 bg-amber-600 hover:bg-amber-500 text-white py-3 rounded-lg font-bold uppercase tracking-widest text-sm transition-all shadow-[0_0_15px_rgba(245,158,11,0.3)]">Simpan Dokumen</button>
            </form>
        </div>
    </div>

    <div id="modalEditDoc" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden flex justify-center items-center z-50">
        <div class="glass-panel p-8 rounded-2xl shadow-2xl border-t-4 border-blue-500 w-full max-w-md relative">
            <button onclick="document.getElementById('modalEditDoc').classList.add('hidden')" class="absolute top-4 right-5 text-slate-500 hover:text-red-400 font-black text-xl transition">&times;</button>
            <h2 class="text-xl font-black text-white mb-6 uppercase tracking-widest">Update Dokumen</h2>
            
            <form id="formEditDoc" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Judul Dokumen *</label>
                    <input type="text" id="edit_title" name="title" required class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Kategori</label>
                    <input type="text" id="edit_category" name="category" list="catList" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">File Saat Ini</label>
                    <p id="edit_original_filename" class="w-full bg-slate-950 border border-slate-800 text-slate-400 px-4 py-2 rounded-lg text-xs font-mono truncate">-</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Ganti File (Kosongkan jika tetap)</label>
                    <input type="file" name="document_file" class="w-full bg-slate-950 border border-slate-700 text-slate-300 px-3 py-2 rounded-lg text-xs file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Keterangan</label>
                    <textarea id="edit_description" name="description" rows="2" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-blue-500 transition"></textarea>
                </div>
                <button type="submit" class="w-full mt-4 bg-blue-600 hover:bg-blue-500 text-white py-3 rounded-lg font-bold uppercase tracking-widest text-sm transition-all shadow-[0_0_15px_rgba(59,130,246,0.3)]">Perbarui Dokumen</button>
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
    @if($errors->any()) showToast("Gagal! Periksa file atau inputan Anda.", 'bg-red-600'); @endif

    // Buka Modal Edit
    function openEditModal(id, title, category, description, originalFilename) {
        document.getElementById('formEditDoc').action = `/inventory/documents/${id}`;
        document.getElementById('edit_title').value = title;
        document.getElementById('edit_category').value = category;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_original_filename').innerText = originalFilename || '-';
        document.getElementById('modalEditDoc').classList.remove('hidden');
    }

    // --- LOGIKA BULK DELETE ---
    const checkAll = document.getElementById('checkAll');
    const bulkChecks = document.querySelectorAll('.bulk-check');
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const bulkCount = document.getElementById('bulkCount');

    function updateBulkUI() {
        const checked = document.querySelectorAll('.bulk-check:checked').length;
        bulkCount.innerText = checked;
        if(checked > 0) btnBulkDelete.classList.remove('hidden');
        else btnBulkDelete.classList.add('hidden');
    }

    checkAll.addEventListener('change', (e) => {
        bulkChecks.forEach(cb => cb.checked = e.target.checked);
        updateBulkUI();
    });

    bulkChecks.forEach(cb => cb.addEventListener('change', updateBulkUI));

    
</script>
@endpush