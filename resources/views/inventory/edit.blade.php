@extends('layouts.itam')

@section('title', 'Edit ' . $asset->asset_code . ' - ITAM KBK')

@section('content')
    <nav class="p-6 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 flex justify-between items-center z-20 shadow-lg">
        <div class="flex items-center gap-4">
            <a href="{{ route('inventory.category', $asset->asset_category_id) }}" class="text-slate-500 hover:text-white font-bold transition flex items-center gap-2 mr-2">
                <span class="text-xl">&larr;</span> Kembali
            </a>
            <div class="w-3 h-3 bg-amber-500 rounded-full animate-pulse"></div>
            <h2 class="text-lg font-black text-white uppercase tracking-widest">Update <span class="text-blue-400">{{ $asset->asset_code }}</span></h2>
        </div>
    </nav>

    <main class="flex-grow p-6 overflow-hidden flex flex-col">
        <form id="formEditAsset" class="flex-grow flex flex-col h-full overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full overflow-hidden">
                
                <div class="glass-panel p-6 rounded-2xl flex flex-col gap-6 overflow-y-auto custom-scrollbar border-t-4 border-blue-500 shadow-xl">
                    <h3 class="text-sm font-black text-blue-400 uppercase tracking-widest border-b border-slate-700 pb-2">1. Klasifikasi & Lokasi</h3>
                    
                    <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Kategori Perangkat</label>
                        <input type="text" disabled value="{{ $asset->category->name }} ({{ $asset->category->prefix }})" class="w-full bg-slate-800 border border-slate-600 text-slate-400 px-4 py-3 rounded-lg font-bold text-sm cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-2">Gedung Penempatan</label>
                        <select id="building_id" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-amber-500 transition text-sm">
                            <option value="">-- Di Gudang / Belum Ditempatkan --</option>
                            @foreach($buildings as $b)
                                <option value="{{ $b->id }}" {{ $asset->building_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-2">Lantai Penempatan</label>
                        <select id="floor_id" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-amber-500 transition text-sm">
                            <option value="">-- Pilih Lantai --</option>
                            </select>
                    </div>
                </div>

                <div class="glass-panel p-6 rounded-2xl flex flex-col gap-5 overflow-y-auto custom-scrollbar border-t-4 border-amber-500 shadow-xl">
                    <h3 class="text-sm font-black text-amber-400 uppercase tracking-widest border-b border-slate-700 pb-2">2. Informasi Dasar</h3>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-2">Nama Perangkat *</label>
                        <input type="text" id="asset_name" value="{{ $asset->name }}" required class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-amber-500 transition text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-2">Merek / Model</label>
                        <input type="text" id="brand_model" value="{{ $asset->brand_model }}" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-amber-500 transition text-sm">
                    </div>
                    @php
                        $existingIp = $asset->ipAddress->ip_address ?? '';
                        $isNoIp = empty($existingIp);
                    @endphp
                    <div id="ip_wrapper" class="{{ $asset->category->has_ip ? '' : 'hidden' }}">
                        <label class="block text-xs font-bold text-slate-400 mb-2">IP Address / Jaringan (Opsional)</label>
                        <div class="flex gap-2">
                            <input type="text" id="ip_address" 
                                value="{{ $isNoIp ? 'Belum Ada' : $existingIp }}" 
                                {{ $isNoIp ? 'disabled' : '' }}
                                placeholder="Cth: 192.168.1.10" 
                                pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$" 
                                title="Format IP Address tidak valid! (Contoh: 192.168.1.10)"
                                oninput="if(!this.disabled) this.value = this.value.replace(/[^0-9.]/g, '')"
                                class="flex-grow bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-amber-500 transition font-mono text-sm disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-slate-800">
                            
                            <button type="button" id="btnToggleIp" onclick="toggleIpStatus()" 
                                class="{{ $isNoIp ? 'bg-red-900/80 text-red-300 border-red-500/50' : 'bg-slate-800 text-slate-300 border-slate-600 hover:bg-slate-700' }} border px-4 py-2.5 rounded-lg transition font-bold text-xs uppercase tracking-widest shrink-0 shadow-sm w-44 text-center">
                                {{ $isNoIp ? 'Isi IP Manual' : 'Cabut IP' }}
                            </button>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">Gunakan tombol "Cabut IP" untuk menghapus IP dari sistem.</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-2">Tahun Pasang</label>
                            <input type="number" id="installation_year" value="{{ $asset->installation_year }}" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-amber-500 transition text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-2">Status *</label>
                            <select id="status" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-amber-500 transition text-sm">
                                <option value="aktif" {{ $asset->status == 'aktif' ? 'selected' : '' }}>🟢 Aktif</option>
                                <option value="tidak digunakan" {{ $asset->status == 'tidak digunakan' ? 'selected' : '' }}>⚪ Tidak Digunakan</option>
                                <option value="rusak" {{ $asset->status == 'rusak' ? 'selected' : '' }}>🔴 Rusak</option>
                                <option value="hilang" {{ $asset->status == 'hilang' ? 'selected' : '' }}>⚫ Hilang</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-2">Pengguna Saat Ini</label>
                        <input type="text" id="current_user" value="{{ $asset->current_user }}" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-amber-500 transition text-sm">
                    </div>

                    <div class="flex-grow">
                        <label class="block text-xs font-bold text-slate-400 mb-2">Keterangan / Deskripsi</label>
                        <textarea id="description" name="description" rows="3" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-amber-500 transition text-sm">{{ $asset->description ?? '' }}</textarea>
                    </div>
                </div>

                <div class="glass-panel p-6 rounded-2xl flex flex-col shadow-xl border-t-4 border-emerald-500 relative">
                    <h3 class="text-sm font-black text-emerald-400 uppercase tracking-widest border-b border-slate-700 pb-2 mb-4">3. Spesifikasi (EAV)</h3>
                    
                    <div id="dynamicFieldsContainer" class="flex-grow overflow-y-auto custom-scrollbar pr-2 space-y-4">
                        @foreach($asset->category->fields as $field)
                            @php
                                $valObj = $asset->values->firstWhere('category_field_id', $field->id);
                                $val = $valObj ? $valObj->value : '';
                            @endphp
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-2">{{ $field->field_name }} {{ $field->is_required ? '*' : '' }}</label>
                                <input type="{{ $field->input_type }}" name="dyn_{{ $field->id }}" value="{{ $val }}" {{ $field->is_required ? 'required' : '' }} class="dynamic-input w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:border-emerald-500 transition text-sm">
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-6 mt-4 border-t border-slate-700 shrink-0">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black uppercase tracking-widest py-4 rounded-xl shadow-[0_0_20px_rgba(37,99,235,0.3)] transition-all">
                            🔄 Perbarui Data
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </main>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const form = document.getElementById('formEditAsset');

    function showToast(msg, colorClass) {
        const t = document.getElementById('toast');
        t.innerText = msg;
        t.className = `fixed top-6 right-6 px-6 py-4 rounded-xl shadow-2xl transition-all duration-300 z-[9999] font-bold text-sm text-white transform translate-y-0 opacity-100 ${colorClass}`;
        setTimeout(() => { t.classList.remove('translate-y-0', 'opacity-100'); t.classList.add('-translate-y-4', 'opacity-0'); }, 4000);
    }

    function toggleIpStatus() {
        const ipInput = document.getElementById('ip_address');
        const btn = document.getElementById('btnToggleIp');
        
        if (ipInput.disabled) {
            ipInput.disabled = false;
            ipInput.value = '';
            btn.innerText = 'Cabut IP';
            btn.className = "bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-300 px-4 py-2.5 rounded-lg transition font-bold text-xs uppercase tracking-widest shrink-0 shadow-sm w-44 text-center";
        } else {
            ipInput.disabled = true;
            ipInput.value = 'Belum Ada';
            btn.innerText = 'Isi IP Manual';
            btn.className = "bg-red-900/80 hover:bg-red-800 border border-red-500/50 text-red-300 px-4 py-2.5 rounded-lg transition font-bold text-xs uppercase tracking-widest shrink-0 shadow-sm w-44 text-center";
        }
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        let dynamicData = {};
        document.querySelectorAll('.dynamic-input').forEach(input => {
            const fieldId = input.name.replace('dyn_', '');
            dynamicData[fieldId] = input.value;
        });

        const payload = {
            name: document.getElementById('asset_name').value,
            ip_address: document.getElementById('ip_address').disabled ? '' : document.getElementById('ip_address').value,
            brand_model: document.getElementById('brand_model').value,
            current_user: document.getElementById('current_user').value,
            description: document.getElementById('description').value,
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

    // Inisialisasi Lantai Saat Halaman Dimuat
    const buildingsData = @json($buildings);
    const buildingSelect = document.getElementById('building_id');
    const floorSelect = document.getElementById('floor_id');
    const currentFloorId = "{{ $asset->floor_id }}";

    function populateFloors(buildingId, selectedFloorId = null) {
        floorSelect.innerHTML = '<option value="">-- Pilih Lantai --</option>';
        const selectedBuilding = buildingsData.find(b => b.id == buildingId);
        if (selectedBuilding && selectedBuilding.floors) {
            selectedBuilding.floors.forEach(floor => {
                let option = document.createElement('option');
                option.value = floor.id;
                option.text = floor.name;
                if(floor.id == selectedFloorId) option.selected = true;
                floorSelect.add(option);
            });
        }
    }

    if(buildingSelect.value) {
        populateFloors(buildingSelect.value, currentFloorId);
    }

    buildingSelect.addEventListener('change', function() {
        populateFloors(this.value);
    });
</script>
@endpush