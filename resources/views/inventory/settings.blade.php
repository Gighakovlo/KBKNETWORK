@extends('layouts.itam')

@section('title', 'Master Settings - ITAM KBK')

@push('styles')
<style>
    /* Animasi Tab Transisi yang Diperbaiki */
    .tab-content { display: none; opacity: 0; }
    .tab-content.active { display: block; opacity: 1; animation: slideUp 0.4s ease-out forwards; }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* Efek Tombol Tab Aktif */
    .tab-btn.active { border-bottom: 2px solid #3b82f6; color: #60a5fa; background: rgba(59, 130, 246, 0.1); }
</style>
@endpush

@section('content')
    <nav class="bg-slate-900/80 backdrop-blur-md border-b border-slate-800 z-20 shadow-xl flex flex-col">
        <div class="p-6 flex justify-between items-center pb-4">
            <div class="flex items-center gap-4">
                <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                <div>
                    <h2 class="text-lg font-black text-white uppercase tracking-widest">Master Settings</h2>
                    <p class="text-blue-400 text-[10px] mt-1 font-bold tracking-wide uppercase">Pusat Kendali Inventaris & Kategori</p>
                </div>
            </div>
        </div>

        <div class="flex justify-center px-6 gap-2 w-full border-t border-slate-800 pt-2">
            <button onclick="switchTab('dashboard')" id="btn-dashboard" class="tab-btn active px-8 py-3 text-sm font-black uppercase tracking-widest text-slate-400 hover:text-blue-400 transition-all rounded-t-lg">
                📊 Dashboard
            </button>
            <button onclick="switchTab('lokasi')" id="btn-lokasi" class="tab-btn px-8 py-3 text-sm font-black uppercase tracking-widest text-slate-400 hover:text-amber-400 transition-all rounded-t-lg">
                📍 Atur Lokasi
            </button>
            <button onclick="switchTab('kategori')" id="btn-kategori" class="tab-btn px-8 py-3 text-sm font-black uppercase tracking-widest text-slate-400 hover:text-emerald-400 transition-all rounded-t-lg">
                📦 Atur Kategori
            </button>
        </div>
    </nav>

    <main class="flex-grow p-8 overflow-y-auto custom-scrollbar relative">
        <div class="fixed top-[20%] right-[-5%] w-96 h-96 bg-blue-900/20 rounded-full blur-[100px] pointer-events-none"></div>

        <div id="tab-dashboard" class="tab-content active">
            <div class="max-w-7xl mx-auto space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="glass-panel p-6 rounded-2xl shadow-xl border-t-4 border-amber-500 relative overflow-hidden flex flex-col items-center justify-center">
                        <div class="absolute -top-10 -right-10 w-24 h-24 bg-amber-500/20 rounded-full blur-2xl"></div>
                        <span class="text-5xl font-black text-white mb-2">{{ $totalBuildings }}</span>
                        <span class="text-xs font-bold text-amber-500 uppercase tracking-widest">Total Gedung</span>
                    </div>
                    <div class="glass-panel p-6 rounded-2xl shadow-xl border-t-4 border-indigo-500 relative overflow-hidden flex flex-col items-center justify-center">
                        <div class="absolute -top-10 -right-10 w-24 h-24 bg-indigo-500/20 rounded-full blur-2xl"></div>
                        <span class="text-5xl font-black text-white mb-2">{{ $totalFloors }}</span>
                        <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Total Lantai Terdaftar</span>
                    </div>
                    <div class="glass-panel p-6 rounded-2xl shadow-xl border-t-4 border-emerald-500 relative overflow-hidden flex flex-col items-center justify-center">
                        <div class="absolute -top-10 -right-10 w-24 h-24 bg-emerald-500/20 rounded-full blur-2xl"></div>
                        <span class="text-5xl font-black text-white mb-2">{{ $totalCategories }}</span>
                        <span class="text-xs font-bold text-emerald-400 uppercase tracking-widest">Kategori Perangkat</span>
                    </div>
                </div>

                <div class="glass-panel p-8 rounded-2xl shadow-xl border border-slate-700/50">
                    <h2 class="text-lg font-black text-white mb-6 uppercase tracking-wider text-center border-b border-slate-700/50 pb-4">Distribusi Lantai per Gedung</h2>
                    <div class="h-[300px] w-full flex justify-center">
                        <canvas id="buildingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-lokasi" class="tab-content">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="glass-panel p-6 rounded-2xl shadow-xl border-t-4 border-amber-500 relative overflow-hidden h-fit">
                    <div class="absolute -top-10 -right-10 w-24 h-24 bg-amber-500/20 rounded-full blur-2xl"></div>
                    <h2 class="text-lg font-black text-white mb-4 uppercase tracking-wider flex items-center gap-2"><span class="text-2xl">📍</span> Tambah Lokasi</h2>
                    <p class="text-xs text-slate-400 mb-6 leading-relaxed">Daftarkan bangunan baru atau tambahkan lantai pada gedung yang sudah ada.</p>
                    
                    <form action="{{ route('settings.location.store') }}" method="POST" class="space-y-4 relative z-10">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Nama Gedung *</label>
                            <input type="text" name="building_name" required placeholder="Cth: Gedung Utama" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-3 rounded-xl text-sm focus:border-amber-500 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Nama Lantai (Opsional)</label>
                            <input type="text" name="floor_name" placeholder="Cth: Lantai 2" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-3 rounded-xl text-sm focus:border-amber-500 focus:outline-none transition">
                        </div>
                        <button type="submit" class="w-full mt-4 bg-amber-600 border border-amber-500 text-white py-3 rounded-xl font-bold uppercase tracking-widest text-sm hover:bg-amber-500 transition-all shadow-[0_0_15px_rgba(245,158,11,0.3)]">
                            Simpan Lokasi
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-2 glass-panel p-6 rounded-2xl shadow-xl border border-slate-700/50">
                    <h2 class="text-lg font-black text-white mb-6 uppercase tracking-wider border-b border-slate-700 pb-4">Daftar Lokasi Terdaftar</h2>
                    
                    <div class="space-y-4 max-h-[600px] overflow-y-auto custom-scrollbar pr-2">
                        @forelse($buildings as $b)
                            <div class="bg-slate-800/50 border border-slate-700/80 rounded-xl p-5 hover:border-amber-500/50 transition">
                                <div class="flex justify-between items-center mb-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-black text-white text-lg">🏢 {{ $b->name }}</span>
                                            <button type="button" onclick="openEditLocation('{{ $b->id }}', '{{ addslashes($b->name) }}', 'building')" class="text-slate-500 hover:text-amber-400 transition text-sm">✏️</button>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1 block">Total: {{ $b->floors->count() }} Lantai</span>
                                    </div>
                                    <form action="{{ route('settings.building.destroy', $b->id) }}" method="POST" onsubmit="return confirm('Peringatan: Gedung tidak bisa dihapus jika masih ada lantai di dalamnya. Hapus gedung?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-white bg-red-900/20 hover:bg-red-600 px-3 py-1.5 rounded border border-red-900/50 transition text-xs font-bold uppercase tracking-widest">Hapus Gedung</button>
                                    </form>
                                </div>
                                
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    @forelse($b->floors as $f)
                                        <div class="flex items-center justify-between bg-slate-900 border border-slate-700 px-3 py-2 rounded-lg group hover:border-blue-500 transition">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-bold text-slate-300">{{ $f->name }}</span>
                                                <button type="button" onclick="openEditLocation('{{ $f->id }}', '{{ addslashes($f->name) }}', 'floor')" class="text-slate-500 hover:text-amber-400 transition text-xs opacity-0 group-hover:opacity-100">✏️</button>
                                            </div>
                                            <form action="{{ route('settings.floor.destroy', $f->id) }}" method="POST" onsubmit="return confirm('Hapus lantai ini? (Akan gagal jika ada aset di dalamnya)');" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="opacity-0 group-hover:opacity-100 text-slate-500 hover:text-red-500 transition font-black text-lg">&times;</button>
                                            </form>
                                        </div>
                                    @empty
                                        <span class="text-xs text-slate-500 italic col-span-full bg-slate-900/50 py-2 px-3 rounded-lg border border-slate-800 border-dashed">Belum ada lantai terdaftar di gedung ini.</span>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 border border-dashed border-slate-700 rounded-xl">
                                <p class="text-slate-500 font-bold text-sm">Belum ada data gedung yang dipetakan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-kategori" class="tab-content">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="glass-panel p-6 rounded-2xl shadow-xl border-t-4 border-emerald-500 relative overflow-hidden h-fit">
                    <div class="absolute -top-10 -right-10 w-24 h-24 bg-emerald-500/20 rounded-full blur-2xl"></div>
                    <h2 class="text-lg font-black text-white mb-4 uppercase tracking-wider flex items-center gap-2"><span class="text-2xl">📦</span> Buat Kategori</h2>
                    <p class="text-xs text-slate-400 mb-6 leading-relaxed">Buat kategori aset baru dan tentukan prefix unik (seperti SWT untuk Switch).</p>
                    
                    <form action="{{ route('settings.category.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 relative z-10">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Nama Kategori *</label>
                            <input type="text" name="name" required placeholder="Cth: CCTV, Proyektor" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-3 rounded-xl text-sm focus:border-emerald-500 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Kode Prefix (Maks 10 Huruf) *</label>
                            <input type="text" name="prefix" required placeholder="Cth: CTV, PRJ" maxlength="10" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-3 rounded-xl text-sm focus:border-emerald-500 focus:outline-none transition uppercase">
                        </div>
                        <label class="flex items-center gap-3 bg-slate-900/80 border border-slate-700 p-3 rounded-xl cursor-pointer hover:border-emerald-500 transition mt-2">
                            <input type="checkbox" name="has_ip" value="1" class="w-5 h-5 rounded bg-slate-950 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                            <div>
                                <p class="text-xs font-bold text-emerald-400 uppercase tracking-widest mb-0.5">Aktifkan Kapabilitas IP</p>
                                <p class="text-[10px] text-slate-500 leading-tight">Centang jika perangkat di kategori ini terhubung ke jaringan (IPv4).</p>
                            </div>
                        </label>

                        <div class="mt-4 p-4 bg-slate-900 border border-slate-700 rounded-xl space-y-4">
                            <h4 class="text-[10px] font-black text-blue-400 uppercase tracking-widest border-b border-slate-700 pb-2">Kustomisasi Visual Kategori</h4>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Upload Icon (PNG / SVG) *</label>
                                <input type="file" name="icon_file" accept=".png, .svg, .jpg, .jpeg" required class="w-full bg-slate-950 border border-slate-700 text-slate-300 px-3 py-2 rounded-lg text-xs file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer transition">
                                <p class="text-[9px] text-slate-500 mt-1">Disarankan menggunakan icon transparan.</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 mb-1">Ukuran Icon (Pixel)</label>
                                    <input type="number" name="icon_size" value="40" min="20" max="150" class="w-full bg-slate-950 border border-slate-700 text-white px-3 py-2 rounded-lg text-sm focus:border-emerald-500 focus:outline-none transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-400 mb-1">Warna Aksen</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" name="color" value="#3b82f6" class="h-9 w-12 rounded cursor-pointer bg-slate-950 border border-slate-700">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full mt-4 bg-emerald-600 border border-emerald-500 text-white py-3 rounded-xl font-bold uppercase tracking-widest text-sm hover:bg-emerald-500 transition-all shadow-[0_0_15px_rgba(16,185,129,0.3)]">
                            Buat Kategori
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="flex justify-between items-end mb-4 border-b border-slate-800 pb-2">
                        <h2 class="text-xl font-black text-white uppercase tracking-widest">Spesifikasi Kategori</h2>
                        <p class="text-xs text-slate-500 font-bold uppercase">Total: {{ $categories->count() }} Kategori</p>
                    </div>

                    @forelse($categories as $cat)
                    <div class="glass-panel border border-slate-700 rounded-2xl p-6 hover:border-slate-500 transition-colors relative">
                        <div class="absolute top-0 left-0 w-2 h-full rounded-l-2xl" style="background-color: {{ $cat->color ?? '#3b82f6' }};"></div>
                        
                        <div class="flex justify-between items-start mb-6 ml-4">
                            <div class="flex items-center gap-4">
                                @if($cat->icon_path)
                                    <div class="bg-slate-900 p-2 rounded-lg border border-slate-700 shadow-inner">
                                        <img src="{{ asset($cat->icon_path) }}" alt="icon" class="w-10 h-10 object-contain">
                                    </div>
                                @else
                                    <div class="w-12 h-12 bg-slate-800 rounded-lg flex items-center justify-center text-xl">📦</div>
                                @endif
                                
                                <div>
                                    <h3 class="text-xl font-black text-white flex items-center gap-3">
                                        {{ $cat->name }} 
                                        <span class="bg-emerald-900/50 text-emerald-400 border border-emerald-500/30 text-[10px] px-2 py-1 rounded tracking-widest">{{ $cat->prefix }}</span>
                                    </h3>
                                    <button type="button" onclick="openEditCategory('{{ $cat->id }}', '{{ addslashes($cat->name) }}', {{ $cat->has_ip ? 'true' : 'false' }}, '{{ $cat->icon_size }}', '{{ $cat->color }}')" class="mt-2 bg-blue-600/20 text-blue-400 hover:bg-blue-600 hover:text-white px-3 py-1 rounded text-[10px] font-bold transition border border-blue-500/30">
                                        ✏️ EDIT VISUAL & KATEGORI
                                    </button>
                                </div>
                            </div>
                            
                            <form action="{{ route('settings.category.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Peringatan! Menghapus kategori akan gagal jika masih ada barang di dalamnya. Lanjutkan?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-slate-500 hover:text-red-400 font-black text-2xl px-2 transition">&times;</button>
                            </form>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 ml-4">
                            <div>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Kolom Terdaftar:</p>
                                <ul class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar pr-2">
                                    @forelse($cat->fields as $field)
                                    <li class="bg-slate-900 border border-slate-700/50 p-3 rounded-xl flex justify-between items-center group hover:border-blue-500 transition">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-bold text-white">{{ $field->field_name }}</p>
                                                <button type="button" onclick="openEditField('{{ $field->id }}', '{{ addslashes($field->field_name) }}', '{{ $field->input_type }}', {{ $field->is_required ? 'true' : 'false' }})" class="text-slate-500 hover:text-blue-400 transition text-xs opacity-0 group-hover:opacity-100">✏️</button>
                                            </div>
                                            <p class="text-[10px] text-slate-400 uppercase tracking-wider mt-1">{{ $field->input_type }} • <span class="{{ $field->is_required ? 'text-emerald-400' : 'text-slate-500' }}">{{ $field->is_required ? 'Wajib' : 'Opsional' }}</span></p>
                                        </div>
                                        <form action="{{ route('settings.field.destroy', $field->id) }}" method="POST" onsubmit="return confirm('Hapus kolom spesifikasi ini? Data aset yang sudah terisi di kolom ini akan ikut hilang!');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-slate-600 hover:text-red-500 font-black text-xl opacity-0 group-hover:opacity-100 transition-opacity">&times;</button>
                                        </form>
                                    </li>
                                    @empty
                                    <li class="text-xs text-slate-500 italic p-4 border border-dashed border-slate-700 rounded-xl text-center">Belum ada spesifikasi khusus.</li>
                                    @endforelse
                                </ul>
                            </div>

                            <div class="bg-slate-900/50 p-5 rounded-xl border border-slate-700/50 h-fit">
                                <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest mb-3">+ Tambah Kolom Form</p>
                                <form action="{{ route('settings.field.store', $cat->id) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <input type="text" name="field_name" required placeholder="Nama Kolom (Cth: Resolusi)" class="w-full bg-slate-950 border border-slate-700 text-white px-3 py-2.5 rounded-lg text-sm focus:border-emerald-500 focus:outline-none transition">
                                    <div class="flex gap-2">
                                        <select name="input_type" required class="flex-grow bg-slate-950 border border-slate-700 text-white px-3 py-2.5 rounded-lg text-sm focus:border-emerald-500 focus:outline-none transition">
                                            <option value="text">Teks Bebas</option>
                                            <option value="number">Angka (Number)</option>
                                            <option value="date">Tanggal (Date)</option>
                                        </select>
                                        <label class="flex items-center gap-2 bg-slate-950 border border-slate-700 px-3 py-2.5 rounded-lg cursor-pointer">
                                            <input type="checkbox" name="is_required" value="1" class="rounded bg-slate-900 border-slate-600 text-emerald-500 focus:ring-emerald-500 w-4 h-4">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Wajib</span>
                                        </label>
                                    </div>
                                    <button type="submit" class="w-full bg-emerald-600/20 hover:bg-emerald-600 text-emerald-400 hover:text-white border border-emerald-500/50 py-2.5 rounded-lg font-bold text-xs uppercase tracking-widest transition-all">Tambahkan Kolom</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center p-16 border border-dashed border-slate-700 rounded-2xl">
                        <p class="text-slate-400 font-black text-xl mb-2">Sistem Kosong</p>
                        <p class="text-sm text-slate-500">Belum ada kategori aset yang dikonfigurasi di sistem EAM ini.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div id="modalEditLocation" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden flex justify-center items-center z-50">
            <div class="glass-panel p-8 rounded-2xl border-t-4 border-amber-500 w-full max-w-sm relative shadow-2xl">
                <button onclick="document.getElementById('modalEditLocation').classList.add('hidden')" class="absolute top-4 right-5 text-slate-500 hover:text-red-400 font-black text-xl transition">&times;</button>
                <h2 id="editLocationTitle" class="text-xl font-black text-white mb-4 uppercase tracking-widest">Edit Lokasi</h2>
                <form id="formEditLocation" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-400 mb-2">Nama Baru</label>
                        <input type="text" id="edit_location_name" name="name" required class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-amber-500 transition text-sm">
                    </div>
                    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-500 text-white py-3 rounded-lg font-bold uppercase tracking-widest text-sm transition-all shadow-[0_0_15px_rgba(245,158,11,0.3)]">Simpan Perubahan</button>
                </form>
            </div>
        </div>

        <div id="modalEditCategory" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden flex justify-center items-center z-50">
            <div class="glass-panel p-8 rounded-2xl border-t-4 border-blue-500 w-full max-w-lg relative shadow-2xl">
                <button onclick="document.getElementById('modalEditCategory').classList.add('hidden')" class="absolute top-4 right-5 text-slate-500 hover:text-red-400 font-black text-xl transition">&times;</button>
                <h2 class="text-xl font-black text-white mb-6 uppercase tracking-widest">Edit Kategori Visual</h2>
                
                <form id="formEditCategory" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">Nama Kategori *</label>
                        <input type="text" id="edit_cat_name" name="name" required class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-blue-500 transition text-sm">
                    </div>
                    
                    <label class="flex items-center gap-3 bg-slate-900/80 border border-slate-700 p-3 rounded-xl cursor-pointer hover:border-emerald-500 transition mt-2">
                        <input type="checkbox" id="edit_cat_has_ip" name="has_ip" value="1" class="w-5 h-5 rounded bg-slate-950 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                        <div>
                            <p class="text-xs font-bold text-emerald-400 uppercase tracking-widest mb-0.5">Aktifkan Kapabilitas IP</p>
                            <p class="text-[10px] text-slate-500 leading-tight">Centang jika butuh Alamat IP.</p>
                        </div>
                    </label>

                    <div class="bg-slate-900 border border-slate-700 p-4 rounded-xl space-y-4 mt-4">
                        <h4 class="text-[10px] font-black text-blue-400 uppercase tracking-widest border-b border-slate-700 pb-2">Kustomisasi Ikon Kanvas</h4>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Ganti Icon Baru (Kosongkan jika tetap)</label>
                            <input type="file" name="icon_file" accept=".png, .svg, .jpg, .jpeg" class="w-full bg-slate-950 border border-slate-700 text-slate-300 px-3 py-2 rounded-lg text-xs file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer transition">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Ukuran Icon (Pixel)</label>
                                <input type="number" id="edit_cat_size" name="icon_size" min="20" max="150" required class="w-full bg-slate-950 border border-slate-700 text-white px-3 py-2 rounded-lg text-sm focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Warna Aksen Kategori</label>
                                <div class="flex items-center gap-2 mt-1">
                                    <input type="color" id="edit_cat_color" name="color" class="h-10 w-16 rounded cursor-pointer bg-slate-950 border border-slate-700">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full mt-4 bg-blue-600 hover:bg-blue-500 text-white py-3 rounded-lg font-bold uppercase tracking-widest text-sm transition-all shadow-[0_0_15px_rgba(37,99,235,0.3)]">Simpan Pembaruan Kategori</button>
                </form>
            </div>
        </div>


        <div id="modalEditField" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden flex justify-center items-center z-50">
            <div class="glass-panel p-8 rounded-2xl border-t-4 border-emerald-500 w-full max-w-sm relative shadow-2xl">
                <button onclick="document.getElementById('modalEditField').classList.add('hidden')" class="absolute top-4 right-5 text-slate-500 hover:text-red-400 font-black text-xl transition">&times;</button>
                <h2 class="text-xl font-black text-white mb-4 uppercase tracking-widest">Edit Kolom</h2>
                <form id="formEditField" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-2">Nama Kolom</label>
                        <input type="text" id="edit_field_name" name="field_name" required class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-emerald-500 transition text-sm">
                    </div>
                    <div class="flex gap-2">
                        <select id="edit_field_type" name="input_type" required class="flex-grow bg-slate-900 border border-slate-700 text-white px-3 py-2.5 rounded-lg text-sm focus:border-emerald-500 transition">
                            <option value="text">Teks Bebas</option>
                            <option value="number">Angka (Number)</option>
                            <option value="date">Tanggal (Date)</option>
                        </select>
                        <label class="flex items-center gap-2 bg-slate-900 border border-slate-700 px-3 py-2.5 rounded-lg cursor-pointer">
                            <input type="checkbox" id="edit_field_required" name="is_required" value="1" class="rounded bg-slate-950 border-slate-600 text-emerald-500 focus:ring-emerald-500 w-4 h-4">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Wajib</span>
                        </label>
                    </div>
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-3 rounded-lg font-bold uppercase tracking-widest text-sm transition-all shadow-[0_0_15px_rgba(16,185,129,0.3)]">Simpan Perubahan</button>
                </form>
            </div>
        </div>

    </main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    // Logika Switch Tabs
    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('active');
            el.classList.replace('text-blue-400', 'text-slate-400');
            el.classList.replace('text-amber-400', 'text-slate-400');
            el.classList.replace('text-emerald-400', 'text-slate-400');
        });

        document.getElementById('tab-' + tabId).classList.add('active');
        let activeBtn = document.getElementById('btn-' + tabId);
        activeBtn.classList.add('active');
        
        if(tabId === 'dashboard') activeBtn.classList.replace('text-slate-400', 'text-blue-400');
        if(tabId === 'lokasi') activeBtn.classList.replace('text-slate-400', 'text-amber-400');
        if(tabId === 'kategori') activeBtn.classList.replace('text-slate-400', 'text-emerald-400');
    }

    // Modal Edit Lokasi Script
    function openEditLocation(id, name, type) {
        document.getElementById('formEditLocation').action = type === 'building' ? `/settings/building/${id}` : `/settings/floor/${id}`;
        document.getElementById('edit_location_name').value = name;
        document.getElementById('editLocationTitle').innerText = type === 'building' ? 'Edit Gedung' : 'Edit Lantai';
        document.getElementById('modalEditLocation').classList.remove('hidden');
    }

    // Modal Edit Kategori Script
    function openEditCategory(id, name, hasIp, iconSize, color) {
        document.getElementById('formEditCategory').action = `/settings/category/${id}`;
        document.getElementById('edit_cat_name').value = name;
        document.getElementById('edit_cat_has_ip').checked = hasIp;
        document.getElementById('edit_cat_size').value = iconSize || 40;
        document.getElementById('edit_cat_color').value = color || '#3b82f6';
        document.getElementById('modalEditCategory').classList.remove('hidden');
    }

    // Render Grafik Chart.js
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('buildingChart').getContext('2d');
        const labels = @json($buildings->pluck('name'));
        const dataCounts = @json($buildings->map(function($b) { return $b->floors->count(); }));

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Lantai',
                    data: dataCounts,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)', 
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, color: '#94a3b8' }, grid: { color: 'rgba(255, 255, 255, 0.05)' } },
                    x: { ticks: { color: '#94a3b8', font: { weight: 'bold' } }, grid: { display: false } }
                }
            }
        });
    });

    // Modal Edit Kolom Script
    function openEditField(id, name, type, isRequired) {
        document.getElementById('formEditField').action = `/settings/field/${id}`;
        document.getElementById('edit_field_name').value = name;
        document.getElementById('edit_field_type').value = type;
        document.getElementById('edit_field_required').checked = isRequired;
        document.getElementById('modalEditField').classList.remove('hidden');
    }
</script>
@endpush