<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Input Aset - KBK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0b1120; }
        .glass-panel { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="text-slate-300 font-sans h-screen flex flex-col">

    <nav class="p-6 bg-slate-900 border-b border-slate-800 flex justify-between items-center z-20 shadow-xl">
        <div class="flex items-center gap-6">
            <a href="{{ route('inventory.index') }}" class="text-slate-500 hover:text-white font-bold transition flex items-center gap-2">
                <span class="text-xl">&larr;</span> Kembali
            </a>
            <div class="h-8 w-px bg-slate-700"></div>
            <h1 class="text-xl font-black text-white uppercase tracking-widest drop-shadow-md">Deploy Aset Baru</h1>
        </div>
    </nav>

    <main class="flex-grow p-6 md:p-10 overflow-y-auto block">
        <div class="w-full max-w-4xl mx-auto glass-panel p-8 rounded-2xl shadow-2xl relative mb-12 h-fit">

            <form id="formAddAsset" class="space-y-8">
                <div class="bg-slate-900/50 p-6 rounded-xl border border-slate-700">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Pilih Jenis Perangkat *</label>
                    <select id="asset_category_id" required class="w-full bg-slate-800 border border-slate-600 text-white px-4 py-3 rounded-lg focus:outline-none focus:border-blue-500 transition font-bold text-lg">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->prefix }})</option>
                        @endforeach
                    </select>
                </div>

                <div id="generalFields" class="hidden space-y-6">
                    <h3 class="text-sm font-black text-blue-400 uppercase tracking-widest border-b border-slate-700 pb-2">Informasi Dasar</h3>
                                     
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-2">Nama Perangkat (Hostname/Identifier) *</label>
                            <input type="text" id="asset_name" required class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-2">Merek / Model</label>
                            <input type="text" id="brand_model" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-2">Pengguna Saat Ini</label>
                            <input type="text" id="current_user" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-blue-500 transition">
                        </div>
                        <div class="flex gap-4">
                            <div class="w-1/2">
                                <label class="block text-xs font-bold text-slate-400 mb-2">Tahun</label>
                                <input type="number" id="installation_year" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-blue-500 transition">
                            </div>
                            <div class="w-1/2">
                                <label class="block text-xs font-bold text-slate-400 mb-2">Status *</label>
                                <select id="status" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-blue-500 transition">
                                    <option value="aktif">🟢 Aktif</option>
                                    <option value="tidak digunakan">⚪ Tidak Digunakan</option>
                                    <option value="rusak">🔴 Rusak</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-sm font-black text-amber-400 uppercase tracking-widest border-b border-slate-700 pb-2 pt-4">Lokasi Penempatan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-2">Gedung</label>
                            <select id="building_id" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-amber-500 transition">
                                <option value="">-- Di Gudang / Belum Ditempatkan --</option>
                                @foreach($buildings as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-2">Lantai</label>
                            <select id="floor_id" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-amber-500 transition">
                                <option value="">-- Pilih Lantai --</option>
                                </select>
                        </div>
                    </div>

                    <h3 class="text-sm font-black text-emerald-400 uppercase tracking-widest border-b border-slate-700 pb-2 pt-4">Spesifikasi Khusus</h3>
                    <div id="dynamicFieldsContainer" class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-800/30 p-4 rounded-xl border border-slate-700/50">
                        <p class="text-slate-500 text-sm col-span-2 text-center py-4">Pilih kategori di atas untuk memuat form spesifikasi.</p>
                    </div>

                    <div class="pt-6 border-t border-slate-700">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black uppercase tracking-widest py-4 rounded-xl shadow-[0_0_20px_rgba(37,99,235,0.3)] transition-all">
                            Simpan ke Database
                        </button>
                    </div>
                </div>
            </form>
            
            <div id="toast" class="absolute top-4 right-4 px-6 py-3 rounded-lg shadow-2xl transition-all duration-300 opacity-0 z-50 font-bold text-sm text-white transform -translate-y-4 pointer-events-none"></div>
        </div>
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const categorySelect = document.getElementById('asset_category_id');
        const generalFields = document.getElementById('generalFields');
        const dynamicContainer = document.getElementById('dynamicFieldsContainer');
        const form = document.getElementById('formAddAsset');

        // Event Listener: Saat Kategori Berubah -> Ambil Spesifikasinya
        categorySelect.addEventListener('change', async function() {
            const catId = this.value;
            dynamicContainer.innerHTML = ''; // Kosongkan form dinamis
            
            if(!catId) {
                generalFields.classList.add('hidden');
                return;
            }

            generalFields.classList.remove('hidden');
            dynamicContainer.innerHTML = '<p class="text-slate-500 text-sm col-span-2 text-center py-4">Memuat spesifikasi...</p>';

            try {
                const response = await fetch(`/inventory/category/${catId}/fields`);
                const fields = await response.json();

                dynamicContainer.innerHTML = ''; // Bersihkan loading
                
                if(fields.length === 0) {
                    dynamicContainer.innerHTML = '<p class="text-slate-500 text-sm col-span-2 text-center py-4">Tidak ada atribut khusus untuk kategori ini.</p>';
                    return;
                }

                // Render Input HTML Dinamis
                fields.forEach(f => {
                    const reqStr = f.is_required ? 'required' : '';
                    const star = f.is_required ? '*' : '';
                    
                    const html = `
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-2">${f.field_name} ${star}</label>
                            <input type="${f.input_type}" name="dyn_${f.id}" ${reqStr} class="dynamic-input w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-emerald-500 transition">
                        </div>
                    `;
                    dynamicContainer.innerHTML += html;
                });
            } catch (error) {
                dynamicContainer.innerHTML = '<p class="text-red-500 text-sm col-span-2 text-center py-4">Gagal memuat spesifikasi.</p>';
            }
        });

        // Event Listener: Submit Form Utama
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Kumpulkan data dinamis
            let dynamicData = {};
            document.querySelectorAll('.dynamic-input').forEach(input => {
                const fieldId = input.name.replace('dyn_', '');
                dynamicData[fieldId] = input.value;
            });

            const payload = {
                asset_category_id: categorySelect.value,
                name: document.getElementById('asset_name').value,
                brand_model: document.getElementById('brand_model').value,
                current_user: document.getElementById('current_user').value,
                installation_year: document.getElementById('installation_year').value,
                status: document.getElementById('status').value,
                building_id: document.getElementById('building_id').value,
                floor_id: document.getElementById('floor_id').value,
                dynamic_fields: dynamicData, // Masukkan array dinamisnya
            };

            try {
                const response = await fetch('/inventory', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(payload)
                });

                const r = await response.json();
                
                if(response.ok && r.success) {
                    showToast(r.message, 'bg-emerald-600');
                    // Kosongkan field text, biarkan kategori tetap terpilih
                    document.getElementById('asset_name').value = '';
                    document.getElementById('brand_model').value = '';
                    document.getElementById('current_user').value = '';
                    document.querySelectorAll('.dynamic-input').forEach(i => i.value = '');
                    document.getElementById('asset_name').focus();
                } else {
                    showToast(r.message || 'Terjadi kesalahan!', 'bg-red-600');
                }
            } catch (err) {
                showToast('Gagal terhubung ke server.', 'bg-red-600');
            }
        });

        function showToast(msg, colorClass) {
            const t = document.getElementById('toast');
            t.innerText = msg;
            t.className = `absolute top-4 right-4 px-6 py-3 rounded-lg shadow-2xl transition-all duration-300 z-[999] font-bold text-sm text-white transform translate-y-0 opacity-100 ${colorClass}`;
            setTimeout(() => { t.classList.remove('translate-y-0', 'opacity-100'); t.classList.add('-translate-y-4', 'opacity-0'); }, 3000);
        }
    </script>
</body>
</html>