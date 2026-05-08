<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Settings - KBK Inventory</title>
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
                <h1 class="text-2xl font-black text-white uppercase tracking-widest drop-shadow-md">Master Settings</h1>
                <p class="text-amber-400 text-xs mt-1 font-bold tracking-wide uppercase">Pusat Kendali Inventaris & Kategori</p>
            </div>
        </div>
    </nav>

    <main class="flex-grow p-8 overflow-y-auto custom-scrollbar">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="space-y-8">
                
                <div class="glass-panel p-6 rounded-2xl shadow-xl border-t-4 border-amber-500 relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-24 h-24 bg-amber-500/20 rounded-full blur-2xl"></div>
                    <h2 class="text-lg font-black text-white mb-4 uppercase tracking-wider">📍 Tambah Lokasi</h2>
                    <form action="{{ route('settings.location.store') }}" method="POST" class="space-y-4 relative z-10">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Nama Gedung *</label>
                            <input type="text" name="building_name" required placeholder="Cth: Gedung Utama" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-amber-500 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Nama Lantai (Opsional)</label>
                            <input type="text" name="floor_name" placeholder="Cth: Lantai 2" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-amber-500 focus:outline-none transition">
                        </div>
                        <button type="submit" class="w-full bg-amber-600/20 hover:bg-amber-600 text-amber-400 hover:text-white border border-amber-500/50 py-2 rounded-lg font-bold text-sm transition-all shadow-[0_0_10px_rgba(245,158,11,0.2)]">
                            Simpan Lokasi
                        </button>
                    </form>
                    
                    <div class="mt-6 border-t border-slate-700/50 pt-4 max-h-32 overflow-y-auto custom-scrollbar">
                        <p class="text-[10px] text-slate-500 font-bold uppercase mb-2">Gedung Terdaftar:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($buildings as $b)
                                <span class="bg-slate-800 text-xs px-2 py-1 rounded text-slate-400">{{ $b->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="glass-panel p-6 rounded-2xl shadow-xl border-t-4 border-blue-500 relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-24 h-24 bg-blue-500/20 rounded-full blur-2xl"></div>
                    <h2 class="text-lg font-black text-white mb-4 uppercase tracking-wider">📦 Tambah Kategori</h2>
                    <form action="{{ route('settings.category.store') }}" method="POST" class="space-y-4 relative z-10">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Nama Kategori *</label>
                            <input type="text" name="name" required placeholder="Cth: CCTV, Proyektor" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-blue-500 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Kode Prefix (Maks 10 Huruf) *</label>
                            <input type="text" name="prefix" required placeholder="Cth: CTV, PRJ" maxlength="10" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-blue-500 focus:outline-none transition uppercase">
                        </div>
                        <button type="submit" class="w-full bg-blue-600/20 hover:bg-blue-600 text-blue-400 hover:text-white border border-blue-500/50 py-2 rounded-lg font-bold text-sm transition-all shadow-[0_0_10px_rgba(59,130,246,0.2)]">
                            Buat Kategori
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="flex justify-between items-end mb-4 border-b border-slate-800 pb-2">
                    <h2 class="text-xl font-black text-white uppercase tracking-widest">Manajemen Spesifikasi Kategori</h2>
                    <p class="text-xs text-slate-500">Total: {{ $categories->count() }} Kategori</p>
                </div>

                @forelse($categories as $cat)
                <div class="bg-slate-800/40 border border-slate-700 rounded-2xl p-6 hover:border-slate-500 transition-colors">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-xl font-black text-white flex items-center gap-3">
                                {{ $cat->name }} 
                                <span class="bg-blue-900/50 text-blue-400 border border-blue-500/30 text-[10px] px-2 py-1 rounded tracking-widest">{{ $cat->prefix }}</span>
                            </h3>
                            <p class="text-xs text-slate-400 mt-1">Konfigurasi kolom spesifikasi khusus untuk perangkat ini.</p>
                        </div>
                        
                        <form action="{{ route('settings.category.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Peringatan! Menghapus kategori akan gagal jika masih ada barang di dalamnya. Lanjutkan?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-white bg-red-900/20 hover:bg-red-600 px-3 py-1.5 rounded border border-red-900/50 transition text-xs font-bold">Hapus Kategori</button>
                        </form>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase mb-3">Kolom Spesifikasi Terdaftar:</p>
                            <ul class="space-y-2">
                                @forelse($cat->fields as $field)
                                <li class="bg-slate-900 border border-slate-700/50 p-3 rounded-lg flex justify-between items-center group hover:border-emerald-500/50 transition">
                                    <div>
                                        <p class="text-sm font-bold text-slate-300">{{ $field->field_name }}</p>
                                        <p class="text-[10px] text-slate-500 uppercase">{{ $field->input_type }} • {{ $field->is_required ? 'Wajib' : 'Opsional' }}</p>
                                    </div>
                                    <form action="{{ route('settings.field.destroy', $field->id) }}" method="POST" onsubmit="return confirm('Hapus kolom spesifikasi ini? Data aset yang sudah terisi di kolom ini akan ikut terhapus!');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-slate-600 hover:text-red-500 font-black text-lg opacity-0 group-hover:opacity-100 transition-opacity">&times;</button>
                                    </form>
                                </li>
                                @empty
                                <li class="text-sm text-slate-500 italic p-3 border border-dashed border-slate-700 rounded-lg text-center">Belum ada spesifikasi khusus.</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="bg-slate-900/50 p-5 rounded-xl border border-slate-700/50">
                            <p class="text-xs font-bold text-emerald-500 uppercase mb-3">+ Tambah Kolom Baru</p>
                            <form action="{{ route('settings.field.store', $cat->id) }}" method="POST" class="space-y-3">
                                @csrf
                                <input type="text" name="field_name" required placeholder="Nama Kolom (Cth: Resolusi)" class="w-full bg-slate-800 border border-slate-600 text-white px-3 py-2 rounded text-sm focus:border-emerald-500 focus:outline-none transition">
                                <div class="flex gap-2">
                                    <select name="input_type" required class="flex-grow bg-slate-800 border border-slate-600 text-white px-3 py-2 rounded text-sm focus:border-emerald-500 focus:outline-none transition">
                                        <option value="text">Teks Bebas</option>
                                        <option value="number">Angka (Number)</option>
                                        <option value="date">Tanggal (Date)</option>
                                    </select>
                                    <label class="flex items-center gap-2 bg-slate-800 border border-slate-600 px-3 py-2 rounded cursor-pointer">
                                        <input type="checkbox" name="is_required" value="1" class="rounded bg-slate-900 border-slate-600 text-emerald-500 focus:ring-emerald-500 w-4 h-4">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Wajib</span>
                                    </label>
                                </div>
                                <button type="submit" class="w-full bg-emerald-600/20 hover:bg-emerald-600 text-emerald-400 hover:text-white border border-emerald-500/50 py-2 rounded font-bold text-xs uppercase tracking-widest transition-all">Tambahkan Kolom</button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center p-12 border border-dashed border-slate-700 rounded-2xl">
                    <p class="text-slate-500 font-bold text-lg">Belum ada kategori yang dikonfigurasi di sistem.</p>
                    <p class="text-sm text-slate-600 mt-2">Silakan buat kategori pertama Anda melalui panel sebelah kiri.</p>
                </div>
                @endforelse
            </div>

        </div>
    </main>

    <div id="toast" class="fixed top-4 right-4 px-6 py-3 rounded-lg shadow-2xl transition-all duration-300 opacity-0 z-[9999] font-bold text-sm text-white transform -translate-y-4 pointer-events-none"></div>

    <script>
        function showToast(msg, colorClass) {
            const t = document.getElementById('toast');
            t.innerText = msg;
            t.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-2xl transition-all duration-300 z-[9999] font-bold text-sm text-white transform translate-y-0 opacity-100 ${colorClass}`;
            setTimeout(() => { t.classList.remove('translate-y-0', 'opacity-100'); t.classList.add('-translate-y-4', 'opacity-0'); }, 4000);
        }

        @if(session('success')) showToast("{{ session('success') }}", 'bg-emerald-600'); @endif
        @if(session('error')) showToast("{!! addslashes(session('error')) !!}", 'bg-red-600'); @endif
    </script>
</body>
</html>