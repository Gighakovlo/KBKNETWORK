<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Macro Editor - Network KBK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <style>
        body { background-color: #0f172a; overflow: hidden; }
        .glass-panel { background: rgba(30, 41, 59, 0.9); backdrop-filter: blur(16px); border-right: 1px solid rgba(255, 255, 255, 0.1); }
        .toolbar-glass { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }
        .grabbing-cursor { cursor: grabbing !important; }
        .crosshair-cursor { cursor: crosshair !important; }
    </style>
</head>
<body class="text-slate-300 font-sans h-screen flex relative">

    <aside class="w-80 h-full glass-panel flex flex-col z-20 shadow-[20px_0_50px_rgba(0,0,0,0.5)]">
        <div class="p-6 border-b border-slate-700/50">
            <a href="/hub" class="text-slate-500 hover:text-white font-bold mb-4 flex items-center gap-2 transition text-sm">&larr; Kembali ke Hub</a>
            <h1 class="text-2xl font-black text-white tracking-wide flex items-center gap-2"><span class="text-amber-500">📐</span> Macro Editor</h1>
            <p class="text-[10px] text-amber-400 font-bold uppercase tracking-widest mt-1">Master Area / Bounding Box</p>
        </div>

        <div class="p-6">
            <button onclick="startNewBuilding()" class="w-full bg-amber-600/20 border border-amber-500 text-amber-400 py-3 rounded-xl font-bold hover:bg-amber-600 hover:text-white transition shadow-[0_0_15px_rgba(245,158,11,0.2)]">
                + Gambar Gedung Baru
            </button>
        </div>

        <div class="flex-grow overflow-y-auto px-6 pb-6 custom-scrollbar space-y-3" id="buildingList">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Daftar Gedung Terdaftar</h3>
            @forelse($buildings as $b)
                <div class="bg-slate-900 border border-slate-700 p-4 rounded-xl flex flex-col gap-3 group hover:border-blue-500 transition">
                    <div>
                        <div class="text-white font-bold">{{ $b->name }}</div>
                        <div class="text-xs text-slate-500 mt-1">{!! $b->polygon_points ? '<span class="text-emerald-400">Area Terpetakan</span>' : '<span class="text-red-400">Belum Digambar</span>' !!}</div>
                    </div>
                    <button onclick="startRedraw({{ $b->id }}, '{{ addslashes($b->name) }}')" class="w-full bg-slate-800 text-slate-300 border border-slate-600 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-600 hover:text-white hover:border-blue-500 transition">
                        ✏️ Gambar Ulang Area
                    </button>
                </div>
            @empty
                <div class="text-center text-slate-500 italic text-sm mt-10">Belum ada data gedung.</div>
            @endforelse
        </div>
    </aside>

    <main class="flex-grow relative bg-slate-950" id="canvasContainer">
        
        <div id="drawingToolbar" class="absolute top-6 left-1/2 transform -translate-x-1/2 z-50 toolbar-glass px-2 py-2 rounded-2xl flex items-center gap-2 shadow-2xl hidden transition-all">
            <button id="btnToolRect" onclick="setTool('rect')" class="px-4 py-2 rounded-xl text-sm font-bold bg-amber-600 text-white flex items-center gap-2 transition"><span class="text-lg">⬛</span> Kotak Presisi</button>
            <button id="btnToolPoly" onclick="setTool('poly')" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-400 hover:text-white hover:bg-slate-800 flex items-center gap-2 transition"><span class="text-lg">🔺</span> Bentuk Bebas</button>
            <div class="w-px h-8 bg-slate-700 mx-1"></div>
            <button onclick="cancelDrawing()" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-400 hover:text-red-400 hover:bg-slate-800 transition">Batal</button>
            <button id="btnSaveShape" onclick="confirmSaveShape()" class="px-5 py-2 rounded-xl text-sm font-bold bg-emerald-600 text-white hover:bg-emerald-500 shadow-[0_0_15px_rgba(5,150,105,0.4)] hidden transition animate-pulse">✅ Simpan Area</button>
        </div>

        <div id="statusBar" class="absolute top-6 right-6 z-50 bg-blue-900/80 backdrop-blur text-blue-100 border border-blue-500 px-5 py-2 rounded-xl text-sm font-bold shadow-[0_0_20px_rgba(59,130,246,0.5)] hidden">
            Mode Gambar Aktif: <span id="statusText">Gedung Baru</span>
        </div>

        <canvas id="macroCanvas"></canvas>

        <div class="absolute bottom-6 right-6 z-20 bg-slate-900/80 backdrop-blur border border-slate-700 px-4 py-2 rounded-xl text-xs font-bold text-slate-400 shadow-2xl pointer-events-none flex items-center gap-2">
            <kbd class="bg-slate-800 border border-slate-600 px-2 py-1 rounded text-white font-mono shadow">Alt</kbd> + <kbd class="bg-slate-800 border border-slate-600 px-2 py-1 rounded text-white font-mono shadow">Drag</kbd> untuk Geser Kamera
        </div>
    </main>

    <div id="buildingTooltip" class="fixed pointer-events-none hidden bg-slate-900/90 backdrop-blur border border-blue-500 text-blue-100 px-4 py-2 rounded-lg shadow-[0_0_15px_rgba(59,130,246,0.3)] text-sm font-bold z-[100] transform -translate-x-1/2 -translate-y-full mt-[-10px] whitespace-nowrap">
        Nama Gedung
    </div>

    <div id="newBuildingModal" class="fixed inset-0 z-[999] hidden bg-[#0f172a]/80 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
        <div class="glass-panel p-8 rounded-2xl border border-slate-700 w-96 transform scale-95 transition-transform duration-300 shadow-[0_0_50px_rgba(245,158,11,0.15)] relative">
            <h2 class="text-xl font-black text-white tracking-wide mb-2"><span class="text-amber-500">🏢</span> Gedung Baru</h2>
            <p class="text-xs text-slate-400 mb-6">Masukkan nama identifikasi untuk area gedung baru.</p>

            <div class="mb-6">
                <input type="text" id="inputBuildingName" class="w-full bg-slate-900/80 border border-slate-600 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition placeholder-slate-600 font-bold" placeholder="Misal: Gudang Utama" onkeypress="if(event.key === 'Enter') submitNewBuilding()">
            </div>

            <div class="flex justify-end gap-3">
                <button onclick="closeBuildingModal()" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-400 hover:text-white hover:bg-slate-800 transition">Batal</button>
                <button onclick="submitNewBuilding()" class="px-5 py-2 rounded-xl text-sm font-bold bg-amber-600 hover:bg-amber-500 text-white shadow-lg transition">Mulai Gambar &rarr;</button>
            </div>
        </div>
    </div>

    <div id="confirmSaveModal" class="fixed inset-0 z-[999] hidden bg-[#0f172a]/80 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
        <div class="glass-panel p-8 rounded-2xl border border-slate-700 w-96 transform scale-95 transition-transform duration-300 shadow-[0_0_50px_rgba(16,185,129,0.15)] relative">
            <h2 class="text-xl font-black text-white tracking-wide mb-2"><span class="text-emerald-500">❓</span> Konfirmasi Simpan</h2>
            <p class="text-sm text-slate-400 mb-6">Apakah Anda yakin ingin menyimpan bentuk area ini ke Database?</p>

            <div class="flex justify-end gap-3">
                <button onclick="closeConfirmModal()" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-400 hover:text-white hover:bg-slate-800 transition">Batal</button>
                <button onclick="executeSaveShape()" class="px-5 py-2 rounded-xl text-sm font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg transition">✅ Simpan</button>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed bottom-10 right-1/2 transform translate-x-1/2 translate-y-20 px-6 py-3 rounded-xl shadow-2xl opacity-0 transition-all duration-300 z-[999] font-bold text-sm text-white"></div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const buildings = @json($buildings);
        
        const canvas = new fabric.Canvas('macroCanvas', { selection: false });
        let masterZoom = 1;

        let appMode = 'view';
        let currentTool = 'rect';
        let actionType = null;
        let targetId = null;
        let buildingName = "";

        let isDrawing = false;
        let rectStartPoint = {x: 0, y: 0};
        let activeShape = null; 
        let polyPoints = [];
        let polyLines = [];
        let polyCircles = [];
        let tempPointsToSave = [];

        function initMap() {
            const container = document.getElementById('canvasContainer');
            canvas.setWidth(container.clientWidth); canvas.setHeight(container.clientHeight);

            fabric.Image.fromURL('/img/denah-master.png', function(img) {
                img.set({ originX: 'left', originY: 'top', left: 0, top: 0, selectable: false, evented: false });
                canvas.add(img); img.sendToBack();

                let zoomX = canvas.width / img.width; let zoomY = canvas.height / img.height;
                masterZoom = Math.min(zoomX, zoomY) * 0.9; canvas.setZoom(masterZoom);
                
                let panX = (canvas.width - (img.width * masterZoom)) / 2; let panY = (canvas.height - (img.height * masterZoom)) / 2;
                canvas.absolutePan(new fabric.Point(-panX, -panY));

                renderExistingBuildings();
            });
        }

        function renderExistingBuildings() {
            const tooltip = document.getElementById('buildingTooltip');

            buildings.forEach(b => {
                if(b.polygon_points) {
                    let poly = new fabric.Polygon(JSON.parse(b.polygon_points), {
                        fill: 'rgba(59, 130, 246, 0.2)', stroke: '#3b82f6', strokeWidth: 2,
                        selectable: false, hasControls: false, hoverCursor: 'default',
                        id: b.id, isBuilding: true, buildingName: b.name // Simpan nama di objek
                    });
                    
                    // --- LOGIKA SMART TOOLTIP ---
                    poly.on('mouseover', function(e) { 
                        if(appMode === 'view') { 
                            this.set({fill: 'rgba(59, 130, 246, 0.5)'}); 
                            canvas.renderAll(); 
                            tooltip.innerText = this.buildingName;
                            tooltip.classList.remove('hidden');
                        } 
                    });
                    
                    poly.on('mousemove', function(e) {
                        if(appMode === 'view' && !tooltip.classList.contains('hidden')) {
                            tooltip.style.left = e.e.clientX + 'px';
                            tooltip.style.top = e.e.clientY + 'px';
                        }
                    });

                    poly.on('mouseout', function() { 
                        if(appMode === 'view') { 
                            this.set({fill: 'rgba(59, 130, 246, 0.2)'}); 
                            canvas.renderAll(); 
                            tooltip.classList.add('hidden');
                        } 
                    });
                    
                    canvas.add(poly);
                }
            });
        }

        // ==========================================
        // ALUR KONTROL UI
        // ==========================================
        function setTool(tool) {
            currentTool = tool;
            document.getElementById('btnToolRect').className = tool === 'rect' ? "px-4 py-2 rounded-xl text-sm font-bold bg-amber-600 text-white flex items-center gap-2 transition" : "px-4 py-2 rounded-xl text-sm font-bold text-slate-400 hover:text-white hover:bg-slate-800 flex items-center gap-2 transition";
            document.getElementById('btnToolPoly').className = tool === 'poly' ? "px-4 py-2 rounded-xl text-sm font-bold bg-amber-600 text-white flex items-center gap-2 transition" : "px-4 py-2 rounded-xl text-sm font-bold text-slate-400 hover:text-white hover:bg-slate-800 flex items-center gap-2 transition";
            resetDrawingVars();
        }

        function startNewBuilding() {
            const modal = document.getElementById('newBuildingModal');
            const input = document.getElementById('inputBuildingName');
            input.value = ''; // Kosongkan inputan lama
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Sedikit delay agar animasi glassmorphism-nya mulus
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('div.glass-panel').classList.remove('scale-95');
                input.focus();
            }, 10);
        }

        function closeBuildingModal() {
            const modal = document.getElementById('newBuildingModal');
            modal.classList.add('opacity-0');
            modal.querySelector('div.glass-panel').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        function submitNewBuilding() {
            let name = document.getElementById('inputBuildingName').value;
            if(!name || name.trim() === "") {
                showToast("Nama gedung tidak boleh kosong!", true);
                return;
            }
            
            buildingName = name.trim(); 
            actionType = 'new'; 
            targetId = null;
            
            closeBuildingModal(); // Tutup pop-up
            activateDrawingMode("Gedung Baru: " + buildingName); // Aktifkan mode menggambar kanvas
        }

        function startRedraw(id, name) {
            let oldPoly = canvas.getObjects().find(o => o.id === id && o.isBuilding);
            if(oldPoly) canvas.remove(oldPoly);

            buildingName = name; actionType = 'redraw'; targetId = id;
            activateDrawingMode("Redraw: " + name);
        }

        function activateDrawingMode(statusTxt) {
            appMode = 'draw';
            document.getElementById('drawingToolbar').classList.remove('hidden');
            document.getElementById('statusBar').classList.remove('hidden');
            document.getElementById('statusText').innerText = statusTxt;
            document.getElementById('canvasContainer').classList.add('crosshair-cursor');
            document.getElementById('buildingTooltip').classList.add('hidden'); // Paksa sembunyikan tooltip
            setTool('rect');
        }

        function cancelDrawing() {
            appMode = 'view';
            document.getElementById('drawingToolbar').classList.add('hidden');
            document.getElementById('statusBar').classList.add('hidden');
            document.getElementById('canvasContainer').classList.remove('crosshair-cursor');
            resetDrawingVars();
            canvas.clear(); initMap(); 
        }

        function resetDrawingVars() {
            isDrawing = false;
            if(activeShape) canvas.remove(activeShape);
            polyLines.forEach(l => canvas.remove(l));
            polyCircles.forEach(c => canvas.remove(c));
            
            activeShape = null; polyPoints = []; polyLines = []; polyCircles = []; tempPointsToSave = [];
            
            let btnSave = document.getElementById('btnSaveShape');
            if(btnSave) btnSave.classList.add('hidden');
            
            canvas.renderAll();
        }

        // ==========================================
        // LOGIKA MENGGAMBAR (FABRIC.JS)
        // ==========================================
        
        canvas.on('mouse:down', function(o) {
            if (appMode !== 'draw' || o.e.altKey) return;
            let pointer = canvas.getPointer(o.e);

            if (currentTool === 'rect' && !isDrawing) {
                resetDrawingVars(); 
                
                isDrawing = true;
                rectStartPoint = { x: pointer.x, y: pointer.y };
                activeShape = new fabric.Rect({ 
                    left: pointer.x, top: pointer.y, width: 0, height: 0, 
                    fill: 'rgba(245, 158, 11, 0.4)', stroke: '#f59e0b', strokeWidth: 2, 
                    selectable: false, evented: false 
                });
                canvas.add(activeShape);
            
            } else if (currentTool === 'poly') {
                polyPoints.push({ x: pointer.x, y: pointer.y });
                
                let circle = new fabric.Circle({ radius: 4, fill: '#f59e0b', left: pointer.x, top: pointer.y, originX: 'center', originY: 'center', selectable: false, evented: false });
                polyCircles.push(circle); canvas.add(circle);

                if (polyPoints.length > 1) {
                    let prev = polyPoints[polyPoints.length - 2];
                    let line = new fabric.Line([prev.x, prev.y, pointer.x, pointer.y], { stroke: '#f59e0b', strokeWidth: 2, selectable: false, evented: false });
                    polyLines.push(line); canvas.add(line);
                }

                if(polyPoints.length >= 3) {
                    tempPointsToSave = polyPoints;
                    document.getElementById('btnSaveShape').classList.remove('hidden');
                }
            }
        });

        canvas.on('mouse:move', function(o) {
            if (appMode !== 'draw' || !isDrawing) return;
            let pointer = canvas.getPointer(o.e);

            if (currentTool === 'rect' && activeShape) {
                if (pointer.x < rectStartPoint.x) { activeShape.set({ left: pointer.x, width: rectStartPoint.x - pointer.x }); } 
                else { activeShape.set({ width: pointer.x - rectStartPoint.x }); }
                
                if (pointer.y < rectStartPoint.y) { activeShape.set({ top: pointer.y, height: rectStartPoint.y - pointer.y }); } 
                else { activeShape.set({ height: pointer.y - rectStartPoint.y }); }
                
                canvas.renderAll();
            }
        });

        canvas.on('mouse:up', function(o) {
            if (appMode !== 'draw' || o.e.altKey) return;

            if (currentTool === 'rect' && isDrawing) {
                isDrawing = false;
                if(activeShape.width < 10 || activeShape.height < 10) {
                    resetDrawingVars(); showToast("Kotak terlalu kecil! Tarik yang agak lebar.", true); return;
                }

                let l = activeShape.left, t = activeShape.top, w = activeShape.width, h = activeShape.height;
                tempPointsToSave = [ {x: l, y: t}, {x: l+w, y: t}, {x: l+w, y: t+h}, {x: l, y: t+h} ];
                
                document.getElementById('btnSaveShape').classList.remove('hidden');
            }
        });

        function confirmSaveShape() {
            if (tempPointsToSave.length < 3) { showToast("Bentuk tidak valid!", true); return; }
            
            // Buka Modal Konfirmasi
            const modal = document.getElementById('confirmSaveModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('div.glass-panel').classList.remove('scale-95');
            }, 10);
        }

        function closeConfirmModal() {
            const modal = document.getElementById('confirmSaveModal');
            modal.classList.add('opacity-0');
            modal.querySelector('div.glass-panel').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        function executeSaveShape() {
            closeConfirmModal(); // Tutup pop-up
            processSaveToServer(tempPointsToSave); // Jalankan penyimpanan
        }

        function processSaveToServer(pointsArray) {
            resetDrawingVars();
            let successPoly = new fabric.Polygon(pointsArray, { fill: 'rgba(59, 130, 246, 0.4)', stroke: '#3b82f6', strokeWidth: 2, selectable: false });
            canvas.add(successPoly); canvas.renderAll();

            appMode = 'saving';
            document.getElementById('statusText').innerText = "Menyimpan ke Database...";

            if (actionType === 'new') {
                fetch('/building', {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ name: buildingName, polygon_points: JSON.stringify(pointsArray) })
                }).then(res => res.json()).then(data => { finishSave(data); });
                
            } else if (actionType === 'redraw') {
                fetch('/building/position', {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ id: targetId, polygon_points: JSON.stringify(pointsArray) })
                }).then(res => res.json()).then(data => { finishSave(data); });
            }
        }

        function finishSave(data) {
            if(data.success) { showToast("Gedung berhasil dipetakan!", false); setTimeout(() => location.reload(), 1000); } 
            else { showToast("Gagal menyimpan data!", true); cancelDrawing(); }
        }

        // --- SISTEM PAN & ZOOM ---
        canvas.on('mouse:wheel', function(opt) {
            let zoom = canvas.getZoom() * (0.999 ** opt.e.deltaY);
            if (zoom > 10) zoom = 10; if (zoom < 0.1) zoom = 0.1;
            canvas.zoomToPoint({ x: opt.e.offsetX, y: opt.e.offsetY }, zoom);
            opt.e.preventDefault(); opt.e.stopPropagation();
        });
        canvas.on('mouse:down', function(opt) {
            if (opt.e.altKey === true) { this.isDragging = true; this.lastPosX = opt.e.clientX; this.lastPosY = opt.e.clientY; document.getElementById('canvasContainer').classList.add('grabbing-cursor'); }
        });
        canvas.on('mouse:move', function(opt) {
            if (this.isDragging) {
                let e = opt.e; let vpt = this.viewportTransform;
                vpt[4] += e.clientX - this.lastPosX; vpt[5] += e.clientY - this.lastPosY;
                this.requestRenderAll(); this.lastPosX = e.clientX; this.lastPosY = e.clientY;
            }
        });
        canvas.on('mouse:up', function() { this.setViewportTransform(this.viewportTransform); this.isDragging = false; document.getElementById('canvasContainer').classList.remove('grabbing-cursor'); });

        function showToast(msg, isError = false) {
            const t = document.getElementById('toast');
            t.innerText = msg;
            t.className = `fixed bottom-10 right-1/2 transform translate-x-1/2 px-6 py-3 rounded-xl shadow-[0_0_30px_rgba(0,0,0,0.5)] transition-all duration-300 z-[999] font-bold text-sm ${isError ? 'bg-red-600' : 'bg-emerald-600'} text-white translate-y-0 opacity-100`;
            setTimeout(() => { t.classList.remove('translate-y-0', 'opacity-100'); t.classList.add('translate-y-20', 'opacity-0'); }, 3000);
        }

        window.onload = initMap;
        window.onresize = () => { const c = document.getElementById('canvasContainer'); canvas.setWidth(c.clientWidth); canvas.setHeight(c.clientHeight); initMap(); };
    </script>
</body>
</html>