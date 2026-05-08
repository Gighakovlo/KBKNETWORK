<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Aset {{ $asset->asset_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0b1120; }
        .glass-panel { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="text-slate-300 font-sans h-screen flex flex-col">

    <nav class="p-6 bg-slate-900 border-b border-slate-800 flex justify-between items-center z-20 shadow-xl">
        <div class="flex items-center gap-6">
            <a href="{{ route('inventory.category', $asset->asset_category_id) }}" class="text-slate-500 hover:text-white font-bold transition flex items-center gap-2">
                <span class="text-xl">&larr;</span> Kembali
            </a>
            <div class="h-8 w-px bg-slate-700"></div>
            <h1 class="text-xl font-black text-white tracking-widest drop-shadow-md">UPDATE <span class="text-blue-400">{{ $asset->asset_code }}</span></h1>
        </div>
    </nav>

    <main class="flex-grow p-6 md:p-10 overflow-y-auto block">
        <div class="w-full max-w-4xl mx-auto glass-panel p-8 rounded-2xl shadow-2xl relative mb-12 h-fit">
            
            <form id="formEditAsset" class="space-y-8">
                <div class="bg-slate-900/50 p-6 rounded-xl border border-slate-700">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Kategori Perangkat</label>
                    <input type="text" disabled value="{{ $asset->category->name }} ({{ $asset->category->prefix }})" class="w-full bg-slate-800 border border-slate-600 text-slate-400 px-4 py-3 rounded-lg font-bold text-lg cursor-not-allowed">
                </div>

                <div id="generalFields" class="space-y-6">
                    <h3 class="text-sm font-black text-blue-400 uppercase tracking-widest border-b border-slate-700 pb-2">Informasi Dasar</h3>
                                        
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-2">Nama Perangkat *</label>
                            <input type="text" id="asset_name" value="{{ $asset->name }}" required class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-2">Merek / Model</label>
                            <input type="text" id="brand_model" value="{{ $asset->brand_model }}" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-blue-500 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-2">Pengguna Saat Ini</label>
                            <input type="text" id="current_user" value="{{ $asset->current_user }}" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-blue-500 transition">
                        </div>
                        <div class="flex gap-4">
                            <div class="w-1/2">
                                <label class="block text-xs font-bold text-slate-400 mb-2">Tahun</label>
                                <input type="number" id="installation_year" value="{{ $asset->installation_year }}" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-blue-500 transition">
                            </div>
                            <div class="w-1/2">
                                <label class="block text-xs font-bold text-slate-400 mb-2">Status *</label>
                                <select id="status" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-blue-500 transition">
                                    <option value="aktif" {{ $asset->status == 'aktif' ? 'selected' : '' }}>🟢 Aktif</option>
                                    <option value="tidak digunakan" {{ $asset->status == 'tidak digunakan' ? 'selected' : '' }}>⚪ Tidak Digunakan</option>
                                    <option value="rusak" {{ $asset->status == 'rusak' ? 'selected' : '' }}>🔴 Rusak</option>
                                    <option value="hilang" {{ $asset->status == 'hilang' ? 'selected' : '' }}>⚫ Hilang</option>
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
                                    <option value="{{ $b->id }}" {{ $asset->building_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
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
                        
                        @foreach($asset->category->fields as $field)
                            @php
                                $valObj = $asset->values->firstWhere('category_field_id', $field->id);
                                $val = $valObj ? $valObj->value : '';
                            @endphp
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-2">{{ $field->field_name }} {{ $field->is_required ? '*' : '' }}</label>
                                <input type="{{ $field->input_type }}" name="dyn_{{ $field->id }}" value="{{ $val }}" {{ $field->is_required ? 'required' : '' }} class="dynamic-input w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-emerald-500 transition">
                            </div>
                        @endforeach

                    </div>

                    <div class="pt-6 border-t border-slate-700">
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black uppercase tracking-widest py-4 rounded-xl shadow-[0_0_20px_rgba(16,185,129,0.3)] transition-all">
                            Perbarui Data
                        </button>
                    </div>
                </div>
            </form>
            
            <div id="toast" class="absolute top-4 right-4 px-6 py-3 rounded-lg shadow-2xl transition-all duration-300 opacity-0 z-50 font-bold text-sm text-white transform -translate-y-4 pointer-events-none"></div>
        </div>
    </main>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const form = document.getElementById('formEditAsset');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            let dynamicData = {};
            document.querySelectorAll('.dynamic-input').forEach(input => {
                const fieldId = input.name.replace('dyn_', '');
                dynamicData[fieldId] = input.value;
            });

            const payload = {
                name: document.getElementById('asset_name').value,
                brand_model: document.getElementById('brand_model').value,
                current_user: document.getElementById('current_user').value,
                installation_year: document.getElementById('installation_year').value,
                status: document.getElementById('status').value,
                building_id: document.getElementById('building_id').value,
                floor_id: document.getElementById('floor_id').value,
                dynamic_fields: dynamicData,
            };

            try {
                const response = await fetch(`/inventory/{{ $asset->id }}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(payload)
                });

                const r = await response.json();
                
                if(response.ok && r.success) {
                    showToast(r.message, 'bg-emerald-600');
                    setTimeout(() => {
                        window.location.href = "{{ route('inventory.category', $asset->asset_category_id) }}";
                    }, 1000);
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