<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Enterprise Mapping - {{ $floor->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <style>
        body { background-color: #0f172a; overflow: hidden; }
        .glass-panel { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .glass-nav { background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        #deviceModal, #notifModal { z-index: 9999; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        .grab-cursor { cursor: grab; }
        .grabbing-cursor { cursor: grabbing !important; }
        .draggable-item { cursor: grab; }
        .draggable-item:active { cursor: grabbing; }
    </style>
</head>
<body class="text-slate-300 font-sans h-screen flex flex-col relative">

    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[20%] left-[-10%] w-96 h-96 bg-indigo-900 rounded-full mix-blend-screen filter blur-[120px] opacity-20"></div>
    </div>

    <header class="glass-nav px-6 py-4 flex items-center justify-between shadow-2xl relative z-20">
        <div class="flex items-center gap-5">
            <a href="/hub" class="bg-slate-800/80 text-slate-300 font-bold px-4 py-2 rounded-lg text-sm border border-slate-600 hover:bg-slate-700 hover:text-white transition flex items-center gap-2">
                &larr; Kembali
            </a>
            <div class="h-6 w-px bg-slate-700"></div>
            <div>
                <h1 class="text-lg font-black text-white tracking-wide">{{ $floor->building->name }} <span class="text-indigo-500">|</span> {{ $floor->name }}</h1>
                <p class="text-xs text-slate-400 uppercase tracking-widest font-semibold">Enterprise Topology Mode</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="/mapping/{{ $floor->id }}/print" target="_blank" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white px-5 py-2.5 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(79,70,229,0.4)]">
                📄 Cetak Laporan PDF
            </a>
        </div>
    </header>

    <div class="flex flex-grow overflow-hidden relative z-10">
        <aside class="w-96 glass-panel h-full border-r border-slate-700/50 flex flex-col shadow-2xl relative z-20">
            
            <div class="p-6 border-b border-slate-700/80 bg-slate-900/50 overflow-y-auto max-h-[60vh] custom-scrollbar">
                <h2 class="text-base font-black mb-4 text-emerald-400 uppercase tracking-widest flex items-center gap-2">
                    <span class="text-xl">⚡</span> Deploy Aset Baru
                </h2>
                <form id="formSpawnAsset" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">Kategori Perangkat *</label>
                        <select id="spawn_category" required class="w-full bg-slate-950/80 border border-slate-700 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-emerald-500 transition text-sm font-bold text-slate-300">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->prefix }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">Nama Perangkat *</label>
                        <input type="text" id="spawn_name" required class="w-full bg-slate-950/80 border border-slate-700 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-emerald-500 transition text-sm" placeholder="Cth: SW_Kantor_1">
                    </div>
                    
                    <div id="spawn_ip_wrapper" class="hidden mt-4">
                        <label class="block text-xs font-bold text-slate-400 mb-1">IP Address / Jaringan (Opsional)</label>
                        <div class="flex gap-2">
                            <input type="text" id="spawn_ip" 
                                placeholder="Cth: 192.168.1.10" 
                                pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$"
                                oninput="if(!this.disabled) this.value = this.value.replace(/[^0-9.]/g, '')"
                                class="w-full bg-slate-950/80 border border-slate-700 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-emerald-500 transition text-sm disabled:opacity-50 disabled:bg-slate-900">
                            <button type="button" id="btnSpawnToggleIp" onclick="toggleSpawnIp()" 
                                class="bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-300 px-3 py-3 rounded-xl transition font-bold text-[10px] uppercase tracking-widest shrink-0 w-24 text-center">
                                Belum Ada
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Merek/Model</label>
                            <input type="text" id="spawn_brand" class="w-full bg-slate-950/80 border border-slate-700 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-emerald-500 transition text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Pengguna</label>
                            <input type="text" id="spawn_user" class="w-full bg-slate-950/80 border border-slate-700 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-emerald-500 transition text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Tahun Pasang</label>
                            <input type="number" id="spawn_year" class="w-full bg-slate-950/80 border border-slate-700 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-emerald-500 transition text-sm" placeholder="Cth: 2024">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1">Status Aset</label>
                            <select id="spawn_status" class="w-full bg-slate-950/80 border border-slate-700 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-emerald-500 transition text-sm font-bold">
                                <option value="aktif">🟢 Aktif</option>
                                <option value="rusak">🔴 Rusak</option>
                                <option value="tidak digunakan">⚪ Tidak Digunakan</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1">Keterangan / Catatan</label>
                        <textarea id="spawn_description" rows="2" class="w-full bg-slate-950/80 border border-slate-700 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-emerald-500 transition text-sm"></textarea>
                    </div>

                    <div id="dynamicFieldsContainer" class="space-y-4 pt-4 border-t border-slate-700/50 hidden">
                        <p class="text-xs font-bold text-emerald-500 uppercase tracking-widest mb-2">Spesifikasi Khusus</p>
                    </div>
                    
                    <button type="submit" class="w-full mt-4 bg-emerald-600 border border-emerald-500 text-white py-3 rounded-xl font-bold hover:bg-emerald-500 transition text-sm uppercase tracking-widest shadow-[0_0_15px_rgba(16,185,129,0.3)] mt-4">
                        + Spawn ke Kanvas
                    </button>
                </form>
            </div>

            <div class="p-5 border-b border-slate-700 bg-slate-900/80">
                <h2 class="text-sm font-black text-indigo-400 uppercase tracking-widest flex items-center gap-2">
                    <span class="text-xl">📦</span> Laci Gudang Aset
                </h2>
                <p class="text-xs text-slate-500 mt-2">Aset yang belum diletakkan. Drag & Drop ke kanvas.</p>
            </div>
            <div class="flex-grow p-5 overflow-y-auto custom-scrollbar space-y-4" id="gudangList">
                @forelse($unplacedAssets as $asset)
                    <div class="draggable-item bg-slate-800/80 border border-slate-600 p-4 rounded-xl hover:border-indigo-400 hover:bg-slate-800 transition shadow-sm group" 
                         draggable="true" ondragstart="dragStart(event, '{{ $asset->id }}')">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-base font-bold text-white group-hover:text-indigo-300 transition">{{ $asset->name }}</p>
                                <p class="text-xs font-mono text-slate-400 mt-1">{{ $asset->asset_code }}</p>
                            </div>
                            @if($asset->category && $asset->category->icon_path)
                                <div class="p-1 rounded-lg border" style="background-color: {{ $asset->category->color ?? '#3b82f6' }}20; border-color: {{ $asset->category->color ?? '#3b82f6' }}50;">
                                    <img src="{{ asset($asset->category->icon_path) }}" alt="icon" class="w-8 h-8 object-contain filter drop-shadow-md">
                                </div>
                            @else
                                <span class="text-[10px] px-2 py-1 rounded uppercase font-bold tracking-widest" style="background-color: {{ $asset->category->color ?? '#3b82f6' }}30; color: {{ $asset->category->color ?? '#3b82f6' }}; border: 1px solid {{ $asset->category->color ?? '#3b82f6' }}60;">
                                    {{ $asset->category->prefix ?? 'AST' }}
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center p-8 border border-dashed border-slate-700 rounded-xl mt-4">
                        <p class="text-slate-500 font-bold text-sm">Gudang kosong.</p>
                    </div>
                @endforelse
            </div>
        </aside>

        <main class="flex-grow flex justify-center items-center bg-slate-950 relative overflow-hidden" id="mainContainer" ondragover="allowDrop(event)" ondrop="drop(event)">
            <div class="absolute bottom-6 right-6 z-20 bg-slate-900/80 backdrop-blur border border-slate-700 px-4 py-2 rounded-xl text-xs font-bold text-slate-400 shadow-2xl pointer-events-none flex items-center gap-2">
                <kbd class="bg-slate-800 border border-slate-600 px-2 py-1 rounded text-white font-mono shadow">Alt</kbd> + <kbd class="bg-slate-800 border border-slate-600 px-2 py-1 rounded text-white font-mono shadow">Drag</kbd> untuk Geser Kanvas
            </div>
            <canvas id="mappingCanvas"></canvas>
        </main>
    </div>

    <div id="deviceModal" class="fixed inset-0 z-[300] hidden bg-[#0b1120]/80 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
        <div class="glass-panel p-6 rounded-2xl border-t-4 border-indigo-500 w-full max-w-2xl relative flex flex-col max-h-[85vh] shadow-[0_0_50px_rgba(79,70,229,0.15)] transform scale-95 transition-transform duration-300">
            <button id="closeModal" class="absolute top-4 right-5 text-slate-500 hover:text-red-400 font-black text-xl transition">&times;</button>
            <div class="flex items-center gap-4 mb-4 pb-4 border-b border-slate-700 shrink-0">
                <div id="modalDevIcon" class="w-14 h-14 rounded-xl flex items-center justify-center text-3xl shadow-inner border border-slate-600 shrink-0">📦</div>
                <div>
                    <h2 id="modalDevName" class="text-2xl font-black text-white tracking-wide">Hostname</h2>
                    <p id="modalDevType" class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Kategori Perangkat</p>
                </div>
            </div>
            <div id="modalDynamicContent" class="overflow-y-auto custom-scrollbar flex-grow pr-2 space-y-4 text-sm"></div>
            <div class="flex gap-3 pt-4 border-t border-slate-700 shrink-0 mt-4">
                <a href="#" id="btnEditAsset" class="flex-1 bg-slate-800 hover:bg-slate-700 border border-slate-600 text-white px-4 py-3 rounded-xl transition font-bold text-xs uppercase tracking-widest text-center shadow-lg">Edit Data</a>
                <button id="btnDeleteAsset" class="flex-1 bg-red-900/40 border border-red-500 text-red-400 hover:bg-red-600 hover:text-white px-4 py-3 rounded-xl transition font-bold text-xs uppercase tracking-widest shadow-lg">Hapus Permanen</button>
            </div>
        </div>
    </div>
    <div id="notifModal" class="fixed top-24 right-8 bg-indigo-900/80 backdrop-blur-md border border-indigo-500 text-indigo-100 font-bold px-6 py-4 rounded-xl shadow-[0_0_20px_rgba(79,70,229,0.3)] hidden transition-all duration-300 transform translate-x-10 opacity-0 z-50 flex items-center gap-3">
        <span class="text-xl">✓</span> <span id="notifText" class="text-sm tracking-wide">Notifikasi</span>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let placedAssets = @json($placedAssets);
        const imageUrl = "{{ $floor->image_path }}"; 
        const canvas = new fabric.Canvas('mappingCanvas', { selection: false, hoverCursor: 'pointer' });
        let currentFloorId = {{ $floor->id }};

        function toggleSpawnIp() {
            const ipInput = document.getElementById('spawn_ip');
            const btn = document.getElementById('btnSpawnToggleIp');
            if (ipInput.disabled) {
                ipInput.disabled = false; ipInput.value = '';
                btn.innerText = 'Belum Ada'; btn.className = "bg-slate-800 hover:bg-slate-700 border border-slate-600 text-slate-300 px-3 py-3 rounded-xl transition font-bold text-[10px] uppercase tracking-widest shrink-0 w-24 text-center";
            } else {
                ipInput.disabled = true; ipInput.value = 'Belum Ada';
                btn.innerText = 'Isi Manual'; btn.className = "bg-red-900/80 hover:bg-red-800 border border-red-500/50 text-red-300 px-3 py-3 rounded-xl transition font-bold text-[10px] uppercase tracking-widest shrink-0 w-24 text-center";
            }
        }

        document.getElementById('spawn_category').addEventListener('change', async function() {
            let catId = this.value; 
            const categoriesData = @json($categories);
            const selectedCat = categoriesData.find(c => c.id == catId);
            const ipWrapper = document.getElementById('spawn_ip_wrapper');
            const ipInput = document.getElementById('spawn_ip');
            if(selectedCat && selectedCat.has_ip) ipWrapper.classList.remove('hidden');
            else { ipWrapper.classList.add('hidden'); ipInput.value = ''; if(ipInput.disabled) toggleSpawnIp(); }
            let container = document.getElementById('dynamicFieldsContainer');
            if(!catId) { container.innerHTML = ''; container.classList.add('hidden'); return; }
            try {
                let res = await fetch(`/inventory/category/${catId}/fields`);
                let fields = await res.json();
                if (fields.length > 0) {
                    container.classList.remove('hidden');
                    let html = '<p class="text-xs font-bold text-emerald-500 uppercase tracking-widest mb-3">Spesifikasi Khusus</p>';
                    fields.forEach(field => {
                        // LOGIKA BARU ANTI-JEBAKAN SQL SERVER
                        const isFieldRequired = (field.is_required == 1 || field.is_required === true || field.is_required === "1");
                        
                        let requiredMarker = isFieldRequired ? ' *' : '';
                        let isRequiredStr = isFieldRequired ? 'required' : '';
                        
                        let inputType = field.input_type === 'number' ? 'number' : (field.input_type === 'date' ? 'date' : 'text');
                        html += `
                            <div class="mb-3">
                                <label class="block text-xs font-bold text-slate-400 mb-1">${field.field_name}${requiredMarker}</label>
                                <input type="${inputType}" name="dyn_field_${field.id}" ${isRequiredStr} data-field-id="${field.id}" class="dynamic-input w-full bg-slate-950 border border-slate-700 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-emerald-500 transition text-sm">
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                } else { container.innerHTML = ''; container.classList.add('hidden'); }
            } catch(e) { console.error('Gagal memuat form spesifikasi'); }
        });

        document.getElementById('formSpawnAsset').addEventListener('submit', async function(e) {
            e.preventDefault();
            let vpt = canvas.viewportTransform;
            let spawnX = (canvas.width / 2 - vpt[4]) / vpt[0]; 
            let spawnY = (canvas.height / 2 - vpt[5]) / vpt[3];
            let dynamicFields = {};
            document.querySelectorAll('.dynamic-input').forEach(input => {
                dynamicFields[input.getAttribute('data-field-id')] = input.value;
            });
            try {
                const res = await fetch('/mapping/spawn', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        floor_id: currentFloorId, asset_category_id: document.getElementById('spawn_category').value,
                        name: document.getElementById('spawn_name').value, brand_model: document.getElementById('spawn_brand').value,
                        current_user: document.getElementById('spawn_user').value, description: document.getElementById('spawn_description').value,
                        installation_year: document.getElementById('spawn_year').value, status: document.getElementById('spawn_status').value,
                        ip_address: document.getElementById('spawn_ip').disabled ? '' : document.getElementById('spawn_ip').value,
                        pos_x: spawnX, pos_y: spawnY, dynamic_fields: dynamicFields
                    })
                });
                const r = await res.json();
                if(r.success) {
                    drawDevice(r.data);
                    document.getElementById('formSpawnAsset').reset();
                    document.getElementById('dynamicFieldsContainer').classList.add('hidden');
                    document.getElementById('dynamicFieldsContainer').innerHTML = '';
                    if(document.getElementById('spawn_ip').disabled) toggleSpawnIp(); 
                    document.getElementById('spawn_ip_wrapper').classList.add('hidden');
                    showNotification('Aset Deployed!', 'border-emerald-500 text-emerald-100 bg-emerald-900/80');
                } else showNotification("Gagal: Pastikan data terisi!", "border-red-500 text-red-100 bg-red-900/80");
            } catch (error) { showNotification("Koneksi terputus!", "border-red-500 text-red-100 bg-red-900/80"); }
        });

        function dragStart(event, assetId) { event.dataTransfer.setData("assetId", assetId); }
        function allowDrop(event) { event.preventDefault(); }
        async function drop(event) {
            event.preventDefault();
            const assetId = event.dataTransfer.getData("assetId");
            if(!assetId) return;
            const rect = document.getElementById('mainContainer').getBoundingClientRect();
            const pointer = canvas.restorePointerVpt({ x: event.clientX - rect.left, y: event.clientY - rect.top });
            try {
                const res = await fetch('/mapping/asset/update-position', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ id: assetId, pos_x: pointer.x, pos_y: pointer.y })
                });
                const r = await res.json();
                if(r.success) {
                    showNotification('Aset ditarik ke peta!', 'border-emerald-500 text-emerald-100 bg-emerald-900/80');
                    setTimeout(() => location.reload(), 800); 
                }
            } catch (err) { showNotification("Gagal memindahkan aset!", "border-red-500 text-red-100 bg-red-900/80"); }
        }

        function initMap() {
            const container = document.getElementById('mainContainer');
            canvas.setWidth(container.clientWidth); canvas.setHeight(container.clientHeight);
            if(imageUrl && imageUrl !== '') {
                fabric.Image.fromURL(imageUrl, function(img) {
                    img.set({ originX: 'left', originY: 'top', left: 0, top: 0, scaleX: 1, scaleY: 1, selectable: false, evented: false });
                    canvas.add(img); img.sendToBack();
                    let zoomX = canvas.width / img.width; let zoomY = canvas.height / img.height;
                    let initialZoom = Math.min(zoomX, zoomY) * 0.9; canvas.setZoom(initialZoom);
                    let panX = (canvas.width - (img.width * initialZoom)) / 2; let panY = (canvas.height - (img.height * initialZoom)) / 2;
                    canvas.absolutePan(new fabric.Point(-panX, -panY));
                    placedAssets.forEach(ast => drawDevice(ast));
                });
            } else placedAssets.forEach(ast => drawDevice(ast));
        }

        canvas.on('mouse:wheel', function(opt) {
            let delta = opt.e.deltaY; let zoom = canvas.getZoom(); zoom *= 0.999 ** delta;
            if (zoom > 10) zoom = 10; if (zoom < 0.1) zoom = 0.1;
            canvas.zoomToPoint({ x: opt.e.offsetX, y: opt.e.offsetY }, zoom);
            opt.e.preventDefault(); opt.e.stopPropagation();
        });
        canvas.on('mouse:down', function(opt) {
            if (opt.e.altKey === true) { this.isDragging = true; this.selection = false; this.lastPosX = opt.e.clientX; this.lastPosY = opt.e.clientY; document.getElementById('mainContainer').classList.add('grabbing-cursor'); }
        });
        canvas.on('mouse:move', function(opt) {
            if (this.isDragging) { let e = opt.e; let vpt = this.viewportTransform; vpt[4] += e.clientX - this.lastPosX; vpt[5] += e.clientY - this.lastPosY; this.requestRenderAll(); this.lastPosX = e.clientX; this.lastPosY = e.clientY; }
        });
        canvas.on('mouse:up', function() { this.setViewportTransform(this.viewportTransform); this.isDragging = false; this.selection = true; document.getElementById('mainContainer').classList.remove('grabbing-cursor'); });

        window.onload = initMap; window.onresize = initMap;

        // --- FIXED DRAW DEVICE BARU ---
        function drawDevice(data) {
            let color = data.category && data.category.color ? data.category.color : '#3b82f6'; 
            let iconSize = data.category && data.category.icon_size ? parseInt(data.category.icon_size) : 40;
            let iconPath = data.category && data.category.icon_path ? '/' + data.category.icon_path : null;
            let shortName = data.name.length > 10 ? data.name.substring(0, 8) + '...' : data.name;
            let text = new fabric.Text(shortName, { 
                fontSize: 12, fill: '#ffffff', fontFamily: 'sans-serif', originX: 'center', originY: 'center', 
                top: (iconSize / 2) + 12, fontWeight: 'bold', backgroundColor: 'rgba(15, 23, 42, 0.8)' 
            });
            if (iconPath) {
                fabric.Image.fromURL(iconPath, function(img) {
                    let scale = iconSize / Math.max(img.width, img.height);
                    img.set({ originX: 'center', originY: 'center', scaleX: scale, scaleY: scale });
                    
                    // --- SOLUSI ERROR ADA DISINI ---
                    // Menggunakan properti shadow secara langsung, bukan fungsi .setShadow()
                    img.shadow = new fabric.Shadow({ color: color, blur: 15, offsetX: 0, offsetY: 0 });

                    let group = new fabric.Group([img, text], { 
                        left: parseFloat(data.pos_x), top: parseFloat(data.pos_y), 
                        originX: 'center', originY: 'center', hasControls: false, hasBorders: true, 
                        borderColor: color, borderDashArray: [5, 5], cornerColor: 'transparent',
                        id: data.id, fullData: data, deviceType: 'asset' 
                    });
                    canvas.add(group);
                });
            } else {
                let rect = new fabric.Rect({ 
                    width: iconSize, height: iconSize, fill: color, rx: 8, ry: 8, originX: 'center', originY: 'center', 
                    shadow: new fabric.Shadow({ color: color, blur: 15, offsetX: 0, offsetY: 0 }) 
                });
                let group = new fabric.Group([rect, text], { 
                    left: parseFloat(data.pos_x), top: parseFloat(data.pos_y), originX: 'center', originY: 'center', 
                    hasControls: false, hasBorders: true, borderColor: color, borderDashArray: [5, 5], cornerColor: 'transparent',
                    id: data.id, fullData: data, deviceType: 'asset' 
                });
                canvas.add(group);
            }
        }

        canvas.on('object:modified', function(options) {
            let obj = options.target;
            if(obj.deviceType === 'asset') {
                fetch('/mapping/asset/update-position', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }, 
                    body: JSON.stringify({ id: obj.id, pos_x: obj.left, pos_y: obj.top }) 
                }).then(res => res.json()).catch(err => console.error(err));
            }
        });

        let selectedDevId = null; let selectedObj = null; let lastClickTime = 0;
        canvas.on('mouse:down', function(options) {
            if(options.e.altKey) return; 
            let obj = options.target; let currentTime = new Date().getTime();
            if (obj && obj.deviceType === 'asset') {
                if (currentTime - lastClickTime < 300) {
                    let d = obj.fullData; selectedDevId = d.id; selectedObj = obj;
                    let ipAddr = d.ip_address ? d.ip_address.ip_address : (d.ipAddress ? d.ipAddress.ip_address : null);
                    let color = d.category && d.category.color ? d.category.color : '#3b82f6';
                    let iconPath = d.category && d.category.icon_path ? '/' + d.category.icon_path : null;
                    document.getElementById('modalDevName').innerText = d.name || 'Tanpa Nama';
                    document.getElementById('modalDevType').innerText = (d.category ? d.category.name : 'Unknown') + ' | ' + (d.asset_code || '-');
                    document.getElementById('modalDevType').style.color = color;
                    let iconEl = document.getElementById('modalDevIcon');
                    if(iconPath) { iconEl.innerHTML = `<img src="${iconPath}" class="w-10 h-10 object-contain drop-shadow-md">`; iconEl.style.backgroundColor = color + '20'; iconEl.style.borderColor = color + '50'; }
                    else { iconEl.innerHTML = '📦'; iconEl.style.backgroundColor = '#312e81'; iconEl.style.borderColor = '#6366f1'; }
                    let html = `<div class="grid grid-cols-1 md:grid-cols-2 gap-4">`;
                    if(ipAddr) html += `<div class="md:col-span-2 bg-slate-900/60 p-3 rounded-lg border border-slate-800"><span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">IP Address</span><span class="font-mono text-emerald-400 font-bold text-lg">${ipAddr}</span></div>`;
                    html += `<div class="bg-slate-900/60 p-3 rounded-lg border border-slate-800"><span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Merek / Model</span><span class="font-semibold text-white">${d.brand_model || '-'}</span></div>`;
                    html += `<div class="bg-slate-900/60 p-3 rounded-lg border border-slate-800"><span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Status</span><span class="font-black text-teal-400 uppercase">${d.status || '-'}</span></div>`;
                    html += `<div class="bg-slate-900/60 p-3 rounded-lg border border-slate-800"><span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Pengguna</span><span class="font-semibold text-amber-400">${d.current_user || '-'}</span></div>`;
                    html += `<div class="bg-slate-900/60 p-3 rounded-lg border border-slate-800"><span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Tahun Pasang</span><span class="font-semibold text-white">${d.installation_year || '-'}</span></div>`;
                    html += `</div>`;
                    if (d.category && d.category.fields && d.values && d.values.length > 0) {
                        html += `<h3 class="text-[10px] font-black uppercase tracking-widest border-b border-slate-700 pb-2 mt-6 pt-2" style="color: ${color}">Spesifikasi Detail</h3><div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">`;
                        d.category.fields.forEach(f => {
                            let valObj = d.values.find(v => v.category_field_id === f.id);
                            html += `<div class="bg-slate-800/30 p-3 rounded-lg border border-slate-700/50"><span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">${f.field_name}</span><span class="font-semibold text-blue-100 break-words">${valObj ? valObj.value : '-'}</span></div>`;
                        });
                        html += `</div>`;
                    }
                    html += `<div class="bg-slate-800/50 p-3 rounded-lg border border-slate-700 mt-4"><span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Catatan</span><span class="text-xs text-slate-400 break-words">${d.description || '-'}</span></div>`;
                    document.getElementById('modalDynamicContent').innerHTML = html;
                    document.getElementById('btnEditAsset').href = `/inventory/${d.id}/edit`;
                    document.getElementById('deviceModal').children[0].style.borderTopColor = color; modalDevIcon.style.color = color;
                    document.getElementById('deviceModal').classList.remove('hidden');
                    setTimeout(() => { deviceModal.classList.remove('opacity-0'); deviceModal.querySelector('div').classList.remove('scale-95'); }, 10);
                }
                lastClickTime = currentTime;
            }
        });

        document.getElementById('closeModal').addEventListener('click', () => {
            deviceModal.classList.add('opacity-0'); deviceModal.querySelector('div').classList.add('scale-95');
            setTimeout(() => { deviceModal.classList.add('hidden'); }, 300);
        });

        document.getElementById('btnDeleteAsset').addEventListener('click', async function() {
            if(selectedDevId && confirm('Aset akan dihapus permanen. Lanjutkan?')) {
                try {
                    const res = await fetch(`/inventory/${selectedDevId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                    });
                    const r = await res.json();
                    if(r.success) {
                        showNotification('Aset dimusnahkan!', 'border-red-500 text-red-100 bg-red-900/80');
                        document.getElementById('deviceModal').classList.add('hidden');
                        canvas.remove(selectedObj); canvas.renderAll();
                    } else showNotification(r.message, 'border-red-500');
                } catch(err) { showNotification('Gagal terhubung ke markas', 'border-red-500'); }
            }
        });

        function showNotification(txt, classes) { 
            let m = document.getElementById('notifModal'); 
            m.className = `fixed top-24 right-8 backdrop-blur-md border font-bold px-6 py-4 rounded-xl shadow-[0_0_20px_rgba(0,0,0,0.5)] z-50 flex items-center gap-3 transition-all duration-300 transform translate-x-0 opacity-100 ${classes}`; 
            document.getElementById('notifText').innerText = txt; 
            setTimeout(() => { m.classList.replace('translate-x-0', 'translate-x-10'); m.classList.replace('opacity-100', 'opacity-0'); setTimeout(() => m.classList.add('hidden'), 300); }, 3000); 
        }
    </script>
</body>
</html>