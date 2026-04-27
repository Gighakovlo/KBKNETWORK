<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mapping Control - {{ $floor->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <style>
        body { background-color: #0f172a; overflow: hidden; }
        .glass-panel { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .glass-nav { background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        #deviceModal, #notifModal, #cableModal { z-index: 9999; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        .grab-cursor { cursor: grab; }
        .grabbing-cursor { cursor: grabbing !important; }
    </style>
</head>
<body class="text-slate-300 font-sans h-screen flex flex-col relative">

    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[20%] left-[-10%] w-96 h-96 bg-blue-900 rounded-full mix-blend-screen filter blur-[120px] opacity-20"></div>
    </div>

    <header class="glass-nav px-6 py-4 flex items-center justify-between shadow-2xl relative z-20">
        <div class="flex items-center gap-5">
            <a href="/hub" class="bg-slate-800/80 text-slate-300 font-bold px-4 py-2 rounded-lg text-sm border border-slate-600 hover:bg-slate-700 hover:text-white transition flex items-center gap-2">
                &larr; Kembali
            </a>
            <div class="h-6 w-px bg-slate-700"></div>
            <div>
                <h1 class="text-lg font-black text-white tracking-wide">{{ $floor->building->name }} <span class="text-blue-500">|</span> {{ $floor->name }}</h1>
                <p class="text-xs text-slate-400 uppercase tracking-widest font-semibold">Topology Editor Mode</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span id="cableStatus" class="text-red-400 font-bold hidden items-center mr-3 text-sm tracking-wide animate-pulse">
                ⚡ Menunggu Perangkat Pertama...
            </span>
            <button id="btnCableMode" class="bg-slate-800 border border-slate-600 text-slate-300 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-700 transition flex gap-2 items-center text-sm shadow-lg">
                <span id="cableIcon">🔌</span> <span id="cableText">Mode Tarik Kabel</span>
            </button>
            <a href="/mapping/{{ $floor->id }}/print" target="_blank" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-5 py-2.5 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(79,70,229,0.4)]">
                📄 Cetak Laporan PDF
            </a>
        </div>
    </header>

    <div class="flex flex-grow overflow-hidden relative z-10">
        <aside class="w-80 glass-panel h-full border-r border-slate-700/50 overflow-y-auto hidden xl:block shadow-2xl relative z-20 custom-scrollbar">
            <div class="p-6 space-y-8">
                
                <div class="relative">
                    <div class="absolute -left-6 top-0 w-1 h-full bg-blue-500 rounded-r-md"></div>
                    <h2 class="text-sm font-black mb-4 text-blue-400 uppercase tracking-widest flex items-center gap-2"><span class="text-lg">🗄️</span> Node Switch</h2>
                    <form id="formAddSwitch" class="space-y-3">
                        <input type="text" id="sw_name" required class="w-full bg-slate-900/80 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:outline-none focus:border-blue-500 transition text-sm placeholder-slate-600" placeholder="Nama Switch">
                        
                        <div class="relative">
                            <input type="text" id="sw_ip" class="w-full bg-slate-900/80 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:outline-none focus:border-blue-500 transition text-sm placeholder-slate-600" placeholder="IP Address (contoh: 192.168.1.1)" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                            <div class="mt-2 flex items-center justify-end">
                                <label class="cursor-pointer flex items-center gap-2 text-xs font-bold text-slate-400 select-none hover:text-blue-400 transition">
                                    <input type="checkbox" id="sw_ip_none" class="peer sr-only" onchange="toggleIpState('sw_ip', this.checked)">
                                    <div class="w-8 h-4 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-blue-500 relative"></div>
                                    Set "Belum Ada"
                                </label>
                            </div>
                        </div>

                        <input type="text" id="sw_brand" class="w-full bg-slate-900/80 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:outline-none focus:border-blue-500 transition text-sm placeholder-slate-600" placeholder="Merek/Model">
                        <input type="number" id="sw_year" class="w-full bg-slate-900/80 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:outline-none focus:border-blue-500 transition text-sm placeholder-slate-600" placeholder="Tahun Pemasangan (Cth: 2024)">
                        
                        <button type="submit" class="w-full bg-blue-600/20 border border-blue-500 text-blue-400 py-2.5 rounded-lg font-bold hover:bg-blue-600 hover:text-white transition text-sm shadow-[0_0_10px_rgba(59,130,246,0.2)] mt-2">+ Tambah Switch</button>
                    </form>
                </div>
                
                <div class="h-px w-full bg-slate-700/50"></div>

                <div class="relative">
                    <div class="absolute -left-6 top-0 w-1 h-full bg-teal-500 rounded-r-md"></div>
                    <h2 class="text-sm font-black mb-4 text-teal-400 uppercase tracking-widest flex items-center gap-2"><span class="text-lg">💻</span> Node Client (PC)</h2>
                    <form id="formAddPc" class="space-y-3">
                        <input type="text" id="pc_name" required class="w-full bg-slate-900/80 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:outline-none focus:border-teal-500 transition text-sm placeholder-slate-600" placeholder="Nama PC">
                        
                        <div class="relative">
                            <input type="text" id="pc_ip" class="w-full bg-slate-900/80 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:outline-none focus:border-teal-500 transition text-sm placeholder-slate-600" placeholder="IP Address (contoh: 192.168.1.1)" oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                            <div class="mt-2 flex items-center justify-end">
                                <label class="cursor-pointer flex items-center gap-2 text-xs font-bold text-slate-400 select-none hover:text-teal-400 transition">
                                    <input type="checkbox" id="pc_ip_none" class="peer sr-only" onchange="toggleIpState('pc_ip', this.checked)">
                                    <div class="w-8 h-4 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-teal-500 relative"></div>
                                    Set "Belum Ada"
                                </label>
                            </div>
                        </div>

                        <input type="text" id="pc_user" class="w-full bg-slate-900/80 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:outline-none focus:border-teal-500 transition text-sm placeholder-slate-600" placeholder="Pengguna Saat Ini">
                        
                        <select id="pc_status" required class="w-full bg-slate-900/80 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:outline-none focus:border-teal-500 transition text-sm">
                            <option value="aktif">🟢 Status: Aktif</option>
                            <option value="tidak digunakan">⚪ Status: Tidak Digunakan</option>
                            <option value="rusak">🔴 Status: Rusak</option>
                        </select>
                        
                        <input type="number" id="pc_year" class="w-full bg-slate-900/80 border border-slate-700 text-white px-4 py-2.5 rounded-lg focus:outline-none focus:border-teal-500 transition text-sm placeholder-slate-600" placeholder="Tahun Pemasangan (Cth: 2024)">

                        <button type="submit" class="w-full bg-teal-600/20 border border-teal-500 text-teal-400 py-2.5 rounded-lg font-bold hover:bg-teal-600 hover:text-white transition text-sm shadow-[0_0_10px_rgba(20,184,166,0.2)] mt-2">+ Tambah PC</button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="flex-grow flex justify-center items-center bg-slate-950 relative overflow-hidden" id="mainContainer">
            <div class="absolute bottom-6 right-6 z-20 bg-slate-900/80 backdrop-blur border border-slate-700 px-4 py-2 rounded-xl text-xs font-bold text-slate-400 shadow-2xl pointer-events-none flex items-center gap-2">
                <kbd class="bg-slate-800 border border-slate-600 px-2 py-1 rounded text-white font-mono shadow">Alt</kbd> + <kbd class="bg-slate-800 border border-slate-600 px-2 py-1 rounded text-white font-mono shadow">Drag</kbd> untuk Geser Kanvas
            </div>
            
            <canvas id="mappingCanvas"></canvas>
        </main>
    </div>

    <div id="deviceModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden flex justify-center items-center transition-opacity">
        <div class="glass-panel p-8 rounded-2xl shadow-[0_0_30px_rgba(0,0,0,0.5)] w-96 relative border-t-4 border-blue-500">
            <button id="closeModal" class="absolute top-4 right-5 text-slate-500 hover:text-red-400 font-black text-xl transition">&times;</button>
            <h2 id="modalName" class="text-2xl font-black mb-6 text-white tracking-wide border-b border-slate-700 pb-3">Nama Perangkat</h2>
            <div class="space-y-4 mb-8 text-slate-300 text-sm">
                <div><span class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">IP Address</span><span id="modalIp" class="font-mono text-lg text-blue-400 font-bold">-</span></div>
                
                <div id="wrapperBrand" class="hidden"><span class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Merek / Model</span><span id="modalBrand" class="font-semibold text-white">-</span></div>
                <div id="wrapperUser" class="hidden"><span class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Pengguna Saat Ini</span><span id="modalUser" class="font-semibold text-white">-</span></div>
                
                <div id="wrapperStatus" class="hidden"><span class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Status PC</span><span id="modalStatus" class="font-semibold text-white uppercase tracking-wider">-</span></div>
                <div id="wrapperYear"><span class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Tahun Pemasangan</span><span id="modalYear" class="font-semibold text-white">-</span></div>
            </div>
            <button id="btnDeleteDevice" class="bg-red-900/40 border border-red-500 text-red-400 px-4 py-3 rounded-xl hover:bg-red-600 hover:text-white transition font-bold w-full text-sm uppercase tracking-widest shadow-[0_0_15px_rgba(239,68,68,0.2)]">Hapus Perangkat</button>
        </div>
    </div>

    <div id="cableModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden flex justify-center items-center transition-opacity">
        <div class="glass-panel p-8 rounded-2xl shadow-[0_0_30px_rgba(0,0,0,0.5)] w-80 relative border-t-4 border-yellow-500">
            <button id="closeCableModal" class="absolute top-4 right-5 text-slate-500 hover:text-red-400 font-black text-xl transition">&times;</button>
            <h2 class="text-xl font-black mb-3 text-white tracking-wide border-b border-slate-700 pb-3">Deteksi Koneksi</h2>
            <p class="text-slate-400 text-sm mb-8 font-medium">Jalur komunikasi terpilih. Putuskan koneksi fisik kabel ini?</p>
            <button id="btnDeleteCable" class="bg-red-900/40 border border-red-500 text-red-400 px-4 py-3 rounded-xl hover:bg-red-600 hover:text-white transition font-bold w-full text-sm uppercase tracking-widest shadow-[0_0_15px_rgba(239,68,68,0.2)]">Putuskan Kabel</button>
        </div>
    </div>

    <div id="notifModal" class="fixed top-24 right-8 bg-teal-900/80 backdrop-blur-md border border-teal-500 text-teal-100 font-bold px-6 py-4 rounded-xl shadow-[0_0_20px_rgba(20,184,166,0.3)] hidden transition-all duration-300 transform translate-x-10 opacity-0 z-50 flex items-center gap-3">
        <span class="text-xl">✓</span> <span id="notifText" class="text-sm tracking-wide">Notifikasi</span>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const existingSwitches = @json($switches);
        const existingPcs = @json($pcs);
        const existingConnections = @json($connections);
        const currentFloorId = {{ $floor->id }};
        const imageUrl = "{{ $floor->image_path }}"; 

        const canvas = new fabric.Canvas('mappingCanvas', { selection: false, hoverCursor: 'pointer' });
        let isCableMode = false; let cableSourceId = null; let cableSourceType = null;

        // FUNGSI CHECKBOX IP YANG BARU
        function toggleIpState(inputId, isChecked) {
            const inputField = document.getElementById(inputId);
            if(isChecked) {
                inputField.value = 'Belum Ada';
                inputField.readOnly = true; // Pakai readOnly biar warnanya gak terlalu pudar dibanding disabled
                inputField.classList.add('opacity-50', 'bg-slate-800');
            } else {
                inputField.value = '';
                inputField.readOnly = false;
                inputField.classList.remove('opacity-50', 'bg-slate-800');
            }
        }

        // POST SWITCH DENGAN ERROR HANDLING
        document.getElementById('formAddSwitch').addEventListener('submit', async function(e) {
            e.preventDefault();
            let vpt = canvas.viewportTransform;
            let spawnX = (canvas.width / 2 - vpt[4]) / vpt[0]; let spawnY = (canvas.height / 2 - vpt[5]) / vpt[3];

            try {
                const res = await fetch('/switch', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }, // Tambah Accept JSON!
                    body: JSON.stringify({ 
                        floor_id: currentFloorId, 
                        name: document.getElementById('sw_name').value, 
                        ip_address: document.getElementById('sw_ip').value, 
                        brand_model: document.getElementById('sw_brand').value, 
                        installation_year: document.getElementById('sw_year').value,
                        pos_x: spawnX, pos_y: spawnY 
                    }) 
                });

                const r = await res.json();
                
                if(res.ok && r.success) { 
                    drawDevice(r.data, 'switch'); 
                    document.getElementById('formAddSwitch').reset(); 
                    document.getElementById('sw_ip_none').checked = false; // Reset toggle
                    toggleIpState('sw_ip', false); 
                    showNotification('Switch Deployed!', 'border-blue-500 text-blue-100 bg-blue-900/80'); 
                } else if (res.status === 422) {
                    // Tangkap error validasi dari Laravel
                    let errMsg = r.errors?.ip_address ? r.errors.ip_address[0] : (r.message || "Format data tidak valid!");
                    showNotification(errMsg, "border-red-500 text-red-100 bg-red-900/80");
                } else {
                    showNotification("Terjadi kesalahan server!", "border-red-500 text-red-100 bg-red-900/80");
                }
            } catch (error) {
                showNotification("Koneksi terputus!", "border-red-500 text-red-100 bg-red-900/80");
            }
        });

        // POST PC DENGAN ERROR HANDLING
        document.getElementById('formAddPc').addEventListener('submit', async function(e) {
            e.preventDefault();
            let vpt = canvas.viewportTransform;
            let spawnX = (canvas.width / 2 - vpt[4]) / vpt[0]; let spawnY = (canvas.height / 2 - vpt[5]) / vpt[3];

            try {
                const res = await fetch('/pc', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }, // Tambah Accept JSON!
                    body: JSON.stringify({ 
                        floor_id: currentFloorId, 
                        name: document.getElementById('pc_name').value, 
                        ip_address: document.getElementById('pc_ip').value, 
                        current_user: document.getElementById('pc_user').value, 
                        status: document.getElementById('pc_status').value,
                        installation_year: document.getElementById('pc_year').value,
                        pos_x: spawnX, pos_y: spawnY 
                    }) 
                });

                const r = await res.json();
                
                if(res.ok && r.success) { 
                    drawDevice(r.data, 'pc'); 
                    document.getElementById('formAddPc').reset(); 
                    document.getElementById('pc_ip_none').checked = false; // Reset toggle
                    toggleIpState('pc_ip', false); 
                    showNotification('PC Client Connected!', 'border-teal-500 text-teal-100 bg-teal-900/80'); 
                } else if (res.status === 422) {
                    // Tangkap error validasi dari Laravel
                    let errMsg = r.errors?.ip_address ? r.errors.ip_address[0] : (r.message || "Format data tidak valid!");
                    showNotification(errMsg, "border-red-500 text-red-100 bg-red-900/80");
                } else {
                    showNotification("Terjadi kesalahan server!", "border-red-500 text-red-100 bg-red-900/80");
                }
            } catch (error) {
                showNotification("Koneksi terputus!", "border-red-500 text-red-100 bg-red-900/80");
            }
        });

        function initMap() {
            const container = document.getElementById('mainContainer');
            canvas.setWidth(container.clientWidth); canvas.setHeight(container.clientHeight);
            
            if(imageUrl && imageUrl !== '') {
                fabric.Image.fromURL(imageUrl, function(img) {
                    img.set({ originX: 'left', originY: 'top', left: 0, top: 0, scaleX: 1, scaleY: 1, selectable: false, evented: false });
                    canvas.add(img); img.sendToBack();
                    
                    let zoomX = canvas.width / img.width; let zoomY = canvas.height / img.height;
                    let initialZoom = Math.min(zoomX, zoomY) * 0.9; canvas.setZoom(initialZoom);
                    
                    let panX = (canvas.width - (img.width * initialZoom)) / 2;
                    let panY = (canvas.height - (img.height * initialZoom)) / 2;
                    canvas.absolutePan(new fabric.Point(-panX, -panY));
                    
                    renderAllDevices();
                });
            } else {
                renderAllDevices();
            }
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

        function renderAllDevices() {
            existingSwitches.forEach(sw => drawDevice(sw, 'switch'));
            existingPcs.forEach(pc => drawDevice(pc, 'pc'));
            drawAllCables();
        }

        function drawDevice(data, type) {
            let color = type === 'switch' ? '#3b82f6' : '#14b8a6'; 
            let rect = new fabric.Rect({ width: 80, height: 40, fill: color, rx: type === 'pc'? 20 : 5, ry: type === 'pc'? 20 : 5, originX: 'center', originY: 'center', shadow: new fabric.Shadow({ color: color, blur: 15, offsetX: 0, offsetY: 0 }) });
            let shortName = data.name.length > 10 ? data.name.substring(0, 8) + '...' : data.name;
            let text = new fabric.Text(shortName, { fontSize: 12, fill: '#ffffff', fontFamily: 'sans-serif', originX: 'center', originY: 'center', fontWeight: 'bold' });
            
            let posX = data.pos_x ? parseFloat(data.pos_x) : canvas.width/2;
            let posY = data.pos_y ? parseFloat(data.pos_y) : canvas.height/2;

            let group = new fabric.Group([rect, text], { left: posX, top: posY, originX: 'center', originY: 'center', hasControls: false, hasBorders: true, borderColor: '#facc15', borderDashArray: [5, 5], cornerColor: 'transparent',
                id: data.id, full_name: data.name, ip_address: data.ip_address, brand_model: data.brand_model, current_user: data.current_user, deviceType: type, 
                installation_year: data.installation_year, status: data.status // INJEKSI DATA BARU
            });
            canvas.add(group);
        }

        function drawAllCables() {
            existingConnections.forEach(conn => {
                const fromDev = canvas.getObjects('group').find(obj => obj.id == conn.from_id && obj.deviceType === conn.from_type);
                const toDev = canvas.getObjects('group').find(obj => obj.id == conn.to_id && obj.deviceType === conn.to_type);
                if (fromDev && toDev) drawCableLine(fromDev, toDev, conn.color, conn.id);
            });
        }

        function drawCableLine(fromDev, toDev, color, connId) {
            let coords = [fromDev.left, fromDev.top, toDev.left, toDev.top];
            let line = new fabric.Line(coords, { stroke: color, strokeWidth: 4, selectable: false, evented: true, perPixelTargetFind: true, hoverCursor: 'crosshair', fromId: fromDev.id, fromType: fromDev.deviceType, toId: toDev.id, toType: toDev.deviceType, connectionId: connId, isCable: true, shadow: new fabric.Shadow({ color: color, blur: 8, offsetX: 0, offsetY: 0 }) });
            canvas.add(line); canvas.sendToBack(line);
        }


        canvas.on('object:moving', function(options) {
            let p = options.target;
            if (p.deviceType) {
                const cables = canvas.getObjects('line').filter(l => l.isCable && ((l.fromId == p.id && l.fromType == p.deviceType) || (l.toId == p.id && l.toType == p.deviceType)));
                cables.forEach(line => {
                    if (line.fromId == p.id && line.fromType == p.deviceType) { line.set({ x1: p.left, y1: p.top }); } 
                    if (line.toId == p.id && line.toType == p.deviceType) { line.set({ x2: p.left, y2: p.top }); }
                }); canvas.renderAll();
            }
        });

        canvas.on('object:modified', function(options) {
            let obj = options.target;
            let endpoint = obj.deviceType === 'switch' ? '/switch/update-position' : '/pc/update-position';
            fetch(endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ id: obj.id, pos_x: obj.left, pos_y: obj.top }) });
        });

        let selectedDevId = null; let selectedDevType = null; let selectedCableId = null; let selectedObj = null; let lastClickTime = 0;

        canvas.on('mouse:down', function(options) {
            if(options.e.altKey) return; 
            
            let obj = options.target; let currentTime = new Date().getTime();
            if (obj && obj.deviceType) {
                if (isCableMode) {
                    if (!cableSourceId) {
                        cableSourceId = obj.id; cableSourceType = obj.deviceType;
                        document.getElementById('cableStatus').innerText = "⚡ Pilih Target Tujuan..."; obj.set('borderColor', '#facc15').set('hasBorders', true); canvas.renderAll();
                    } else if (cableSourceId === obj.id && cableSourceType === obj.deviceType) {
                        obj.set('hasBorders', false); cableSourceId = null; cableSourceType = null;
                        document.getElementById('cableStatus').innerText = "⚡ Menunggu Perangkat Pertama..."; canvas.renderAll();
                    } else { connectCable(cableSourceId, cableSourceType, obj.id, obj.deviceType); }
                    return; 
                }

                if (currentTime - lastClickTime < 300) {
                    document.getElementById('modalName').innerText = obj.full_name || 'Tanpa Nama';
                    document.getElementById('modalIp').innerText = obj.ip_address || '-';
                    document.getElementById('modalYear').innerText = obj.installation_year || '-';

                    if(obj.deviceType === 'switch') { 
                        document.getElementById('wrapperBrand').classList.remove('hidden'); document.getElementById('wrapperUser').classList.add('hidden'); 
                        document.getElementById('modalBrand').innerText = obj.brand_model || '-'; 
                        document.getElementById('wrapperStatus').classList.add('hidden');
                    }
                    if(obj.deviceType === 'pc') { 
                        document.getElementById('wrapperUser').classList.remove('hidden'); document.getElementById('wrapperBrand').classList.add('hidden'); 
                        document.getElementById('modalUser').innerText = obj.current_user || '-'; 
                        document.getElementById('wrapperStatus').classList.remove('hidden');
                        document.getElementById('modalStatus').innerText = obj.status || '-';
                    }
                    selectedDevId = obj.id; selectedDevType = obj.deviceType; selectedObj = obj; document.getElementById('deviceModal').classList.remove('hidden');
                }
                lastClickTime = currentTime;
            } else if (obj && obj.isCable && !isCableMode) {
                if (currentTime - lastClickTime < 300) { selectedCableId = obj.connectionId; selectedObj = obj; document.getElementById('cableModal').classList.remove('hidden'); }
                lastClickTime = currentTime;
            } else { resetCableModeHighlight(); }
        });

        function connectCable(fromId, fromType, toId, toType) {
            fetch('/connection', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ from_id: fromId, from_type: fromType, to_id: toId, to_type: toType }) })
            .then(res => res.json()).then(r => {
                if(r.success) {
                    const fromDev = canvas.getObjects('group').find(o => o.id == fromId && o.deviceType === fromType);
                    const toDev = canvas.getObjects('group').find(o => o.id == toId && o.deviceType === toType);
                    drawCableLine(fromDev, toDev, r.data.color, r.data.id); showNotification('Link Established!', 'border-green-500 text-green-100 bg-green-900/80');
                } resetCableModeHighlight();
            });
        }

        function resetCableModeHighlight() {
            if (isCableMode && cableSourceId) { const sourceObj = canvas.getObjects('group').find(o => o.id == cableSourceId && o.deviceType === cableSourceType); if (sourceObj) sourceObj.set('hasBorders', false); cableSourceId = null; cableSourceType = null; document.getElementById('cableStatus').innerText = "⚡ Menunggu Perangkat Pertama..."; canvas.renderAll(); }
        }

        document.getElementById('btnCableMode').addEventListener('click', function() {
            isCableMode = !isCableMode;
            if (isCableMode) { this.classList.replace('bg-slate-800', 'bg-yellow-600/20'); this.classList.replace('border-slate-600', 'border-yellow-500'); this.classList.replace('text-slate-300', 'text-yellow-400'); document.getElementById('cableText').innerText = "Cancel Routing"; document.getElementById('cableIcon').innerText = "❌"; document.getElementById('cableStatus').classList.remove('hidden'); document.getElementById('cableStatus').classList.add('flex');
            } else { this.classList.replace('bg-yellow-600/20', 'bg-slate-800'); this.classList.replace('border-yellow-500', 'border-slate-600'); this.classList.replace('text-yellow-400', 'text-slate-300'); document.getElementById('cableText').innerText = "Mode Tarik Kabel"; document.getElementById('cableIcon').innerText = "🔌"; document.getElementById('cableStatus').classList.add('hidden'); document.getElementById('cableStatus').classList.remove('flex'); resetCableModeHighlight(); }
        });

        document.getElementById('closeModal').addEventListener('click', () => document.getElementById('deviceModal').classList.add('hidden'));
        document.getElementById('closeCableModal').addEventListener('click', () => document.getElementById('cableModal').classList.add('hidden'));

        document.getElementById('btnDeleteDevice').addEventListener('click', function() {
            let endpoint = selectedDevType === 'switch' ? '/switch/' : '/pc/';
            if(selectedDevId) {
                fetch(endpoint + selectedDevId, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } }).then(res => res.json()).then(r => {
                    if(r.success) {
                        const cables = canvas.getObjects('line').filter(l => l.isCable && ((l.fromId == selectedDevId && l.fromType == selectedDevType) || (l.toId == selectedDevId && l.toType == selectedDevType)));
                        cables.forEach(c => canvas.remove(c)); canvas.remove(selectedObj); document.getElementById('deviceModal').classList.add('hidden'); selectedDevId = null; canvas.renderAll(); showNotification('Perangkat Dihapus!', 'border-red-500 text-red-100 bg-red-900/80');
                    }
                });
            }
        });

        document.getElementById('btnDeleteCable').addEventListener('click', function() {
            if(selectedCableId) {
                fetch('/connection/' + selectedCableId, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } }).then(res => res.json()).then(r => {
                    if(r.success) { canvas.remove(selectedObj); document.getElementById('cableModal').classList.add('hidden'); selectedCableId = null; canvas.renderAll(); showNotification('Koneksi Diputus!', 'border-yellow-500 text-yellow-100 bg-yellow-900/80'); }
                });
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