<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Micro Studio - Split Screen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <style>
        body { background-color: #0b1120; }
        .glass-panel { background: rgba(15, 23, 42, 0.9); border-left: 1px solid rgba(255,255,255,0.1); }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        .crosshair-cursor { cursor: crosshair !important; }
        .grabbing-cursor { cursor: grabbing !important; }
    </style>
</head>
<body class="text-slate-300 font-sans h-screen flex overflow-hidden">

    <div class="w-2/3 h-full relative bg-slate-950" id="leftPanel">
        <div class="absolute top-6 left-6 z-20 flex gap-4">
            <a href="{{ isset($floor) ? '/management' : '/hub' }}" class="bg-slate-800/80 hover:bg-slate-700 backdrop-blur px-4 py-2 rounded-lg font-bold text-white shadow-xl transition">&larr; Kembali</a>
            <div class="bg-blue-600/80 backdrop-blur px-4 py-2 rounded-lg font-bold text-white shadow-xl">
                📍 Target Fokus: {{ $building->name }}
            </div>
            @if(isset($floor))
            <div class="bg-amber-600/80 backdrop-blur px-4 py-2 rounded-lg font-bold text-white shadow-xl animate-pulse">
                ✏️ EDIT MODE: {{ $floor->name }}
            </div>
            @endif
        </div>
        
        <div class="absolute bottom-6 left-6 z-20 bg-slate-900/80 backdrop-blur border border-slate-700 px-4 py-2 rounded-xl text-xs font-bold text-slate-400 shadow-2xl pointer-events-none flex items-center gap-2">
            <kbd class="bg-slate-800 border border-slate-600 px-2 py-1 rounded text-white font-mono shadow">Alt</kbd> + <kbd class="bg-slate-800 border border-slate-600 px-2 py-1 rounded text-white font-mono shadow">Drag</kbd> untuk Geser Kamera
        </div>

        <canvas id="splitCanvas"></canvas>
    </div>

    <div class="w-1/3 h-full glass-panel flex flex-col shadow-2xl z-10">
        <div class="p-8 border-b border-slate-800">
            <h1 class="text-3xl font-black text-white tracking-tight">{{ $building->name }}</h1>
            <p class="text-xs text-blue-400 font-bold uppercase tracking-widest mt-2">Micro Studio Workspace</p>
        </div>

        <div class="p-8 flex-grow overflow-y-auto space-y-8 custom-scrollbar">
            
            <form id="floorForm" onsubmit="saveFloor(event)" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="building_id" value="{{ $building->id }}">
                @if(isset($floor))
                    <input type="hidden" name="floor_id" value="{{ $floor->id }}">
                @endif
                
                <div class="bg-slate-900 border border-slate-700 p-6 rounded-2xl mb-6">
                    <h3 class="text-sm font-bold text-amber-500 uppercase tracking-widest mb-4">Tahap 1: Batas Lantai</h3>
                    
                    <div class="flex gap-2 mb-6 bg-slate-950 p-1 rounded-xl border border-slate-800">
                        <button type="button" id="tabRect" onclick="setMode('rect')" class="flex-1 py-2 text-xs font-bold rounded-lg bg-amber-600 text-white transition shadow-md">Kotak Presisi</button>
                        <button type="button" id="tabPoly" onclick="setMode('poly')" class="flex-1 py-2 text-xs font-bold rounded-lg text-slate-500 hover:text-white transition">Bentuk Bebas</button>
                    </div>

                    <div id="controlRect" class="space-y-4">
                        <button type="button" onclick="spawnFloorBox()" class="w-full bg-amber-600/20 border border-amber-500 text-amber-400 hover:bg-amber-600 hover:text-white font-bold py-3 rounded-xl transition shadow-lg">+ Buat Kotak Area Baru (Redraw)</button>
                        <div class="bg-slate-950 border border-slate-800 p-4 rounded-xl flex justify-between items-center">
                            <span class="text-xs text-slate-500 font-bold uppercase">Aspect Ratio</span>
                            <div class="flex items-center gap-2">
                                <input type="number" id="ratioW" value="1" oninput="applyManualRatio()" class="w-16 bg-slate-900 border border-slate-700 text-white text-center font-bold rounded p-1 outline-none focus:border-amber-500 transition">
                                <span class="text-slate-700 font-black">:</span>
                                <input type="number" id="ratioH" value="1" oninput="applyManualRatio()" class="w-16 bg-slate-900 border border-slate-700 text-white text-center font-bold rounded p-1 outline-none focus:border-amber-500 transition">
                            </div>
                        </div>
                    </div>

                    <div id="controlPoly" class="space-y-4 hidden">
                        <p class="text-xs text-slate-400 leading-relaxed bg-slate-950 p-4 rounded-xl border border-slate-800">
                            <span class="text-amber-500 font-bold">Cara Pakai:</span> Klik titik sudut di kanvas. Tekan <b>Klik Kanan</b> untuk menyelesaikan bentuk.
                        </p>
                        <button type="button" id="btnStartPoly" onclick="startPolyDraw()" class="w-full bg-amber-600/20 border border-amber-500 text-amber-400 hover:bg-amber-600 hover:text-white font-bold py-3 rounded-xl transition shadow-lg">Mulai Gambar Bentuk Bebas (Redraw)</button>
                    </div>

                    <button type="button" id="btnDownloadTemplate" onclick="downloadTemplate()" class="w-full mt-6 bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl transition shadow-[0_0_15px_rgba(37,99,235,0.4)] flex justify-center items-center gap-2 hidden">
                        <span class="text-lg">📥</span> Download Template Denah
                    </button>
                    <p id="descTemplate" class="text-[10px] text-slate-500 mt-2 text-center font-bold hidden">Berikan PNG ini ke Drafter sebagai master ukurannya.</p>

                    <input type="hidden" name="box_width" id="box_width">
                    <input type="hidden" name="box_height" id="box_height">
                    <input type="hidden" name="box_left" id="box_left">
                    <input type="hidden" name="box_top" id="box_top">
                    <input type="hidden" name="polygon_points" id="polygon_points">
                </div>

                <div id="step2" class="bg-slate-900 border border-slate-700 p-6 rounded-2xl opacity-50 pointer-events-none transition-all duration-500">
                    <h3 class="text-sm font-bold text-emerald-500 uppercase tracking-widest mb-4">Tahap 2: Upload Denah Final</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-2">Nama Lantai</label>
                            <input type="text" name="name" required placeholder="Contoh: Lantai Dasar" class="w-full bg-slate-950 border border-slate-700 p-3 rounded-lg text-white outline-none focus:border-emerald-500 transition">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-2">File Gambar Denah</label>
                            <input type="file" name="image" id="imageInput" accept="image/png, image/jpeg" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500" {{ isset($floor) ? '' : 'required' }}>
                            @if(isset($floor) && $floor->image_path)
                                <p class="text-[10px] text-emerald-400 mt-2">✔️ Denah sudah ter-upload. Kosongkan jika tidak ingin diganti.</p>
                            @endif
                        </div>
                        <button type="submit" id="btnSave" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-4 rounded-xl transition shadow-lg shadow-emerald-500/20 mt-4">Simpan Data Lantai</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <div id="toast" class="fixed bottom-10 right-10 px-6 py-3 rounded-lg shadow-2xl transform translate-y-20 opacity-0 transition-all duration-300 z-[100] font-bold text-sm text-white"></div>

    <script>
        const targetBuilding = @json($building);
        const existingFloor = @json($floor ?? null); 
        
        const canvas = new fabric.Canvas('splitCanvas', { selection: false });
        
        let currentMode = 'rect';
        let activeFloorShape = null; 
        let boundingBoxDashed = null; 
        
        let polyDrawing = false;
        let polyPoints = [];
        let polyLines = [];
        let polyCircles = [];

        function initSplitScreen() {
            const parent = document.getElementById('leftPanel');
            canvas.setWidth(parent.clientWidth);
            canvas.setHeight(parent.clientHeight);

            fabric.Image.fromURL('/img/denah-master.png', function(img) {
                img.set({ originX: 'left', originY: 'top', left: 0, top: 0, scaleX: 1, scaleY: 1, selectable: false, evented: false });
                canvas.add(img); img.sendToBack(); 

                if (targetBuilding.polygon_points) renderBuildingFocus();
                if (existingFloor) loadExistingFloor();
            });
        }

        function renderBuildingFocus() {
            let polyPoints = JSON.parse(targetBuilding.polygon_points);
            let poly = new fabric.Polygon(polyPoints, { fill: 'rgba(59, 130, 246, 0.1)', stroke: '#3b82f6', strokeWidth: 2, selectable: false, evented: false });
            canvas.add(poly);

            canvas.setViewportTransform([1, 0, 0, 1, 0, 0]);
            let br = poly.getBoundingRect();
            let targetZoom = Math.min(canvas.width / br.width, canvas.height / br.height) * 0.7;
            canvas.setZoom(targetZoom);
            
            let vpt = canvas.viewportTransform;
            vpt[4] = (canvas.width / 2) - ((br.left + br.width/2) * targetZoom);
            vpt[5] = (canvas.height / 2) - ((br.top + br.height/2) * targetZoom);
            canvas.setViewportTransform(vpt);
        }

        function switchTabUI(mode) {
            currentMode = mode;
            if(mode === 'rect') {
                document.getElementById('tabRect').className = "flex-1 py-2 text-xs font-bold rounded-lg bg-amber-600 text-white transition shadow-md";
                document.getElementById('tabPoly').className = "flex-1 py-2 text-xs font-bold rounded-lg text-slate-500 hover:text-white transition";
                document.getElementById('controlRect').classList.remove('hidden');
                document.getElementById('controlPoly').classList.add('hidden');
            } else {
                document.getElementById('tabPoly').className = "flex-1 py-2 text-xs font-bold rounded-lg bg-amber-600 text-white transition shadow-md";
                document.getElementById('tabRect').className = "flex-1 py-2 text-xs font-bold rounded-lg text-slate-500 hover:text-white transition";
                document.getElementById('controlPoly').classList.remove('hidden');
                document.getElementById('controlRect').classList.add('hidden');
            }
        }

        function setMode(mode) {
            switchTabUI(mode);
            clearActiveShapes();
            hideStep2();
        }

        function loadExistingFloor() {
            document.querySelector('input[name="name"]').value = existingFloor.name;
            document.getElementById('btnSave').innerText = "Update Visual (Overwrite)";
            
            if (existingFloor.polygon_points && existingFloor.polygon_points !== "") {
                let points = JSON.parse(existingFloor.polygon_points);
                
                let minX = Math.min(...points.map(p => p.x));
                let minY = Math.min(...points.map(p => p.y));
                let normalizedPoints = points.map(p => ({ x: p.x - minX, y: p.y - minY }));

                activeFloorShape = new fabric.Polygon(normalizedPoints, {
                    left: minX, top: minY, originX: 'left', originY: 'top', // FIX: Paksa origin di pojok kiri atas
                    fill: 'rgba(245, 158, 11, 0.3)', stroke: '#f59e0b', strokeWidth: 3, 
                    selectable: true, hasControls: true, cornerColor: '#ffffff', cornerSize: 8,
                    objectCaching: false 
                });
                switchTabUI('poly'); 
            } else {
                activeFloorShape = new fabric.Rect({
                    left: parseFloat(existingFloor.box_left), top: parseFloat(existingFloor.box_top),
                    width: parseFloat(existingFloor.box_width), height: parseFloat(existingFloor.box_height),
                    originX: 'left', originY: 'top',
                    fill: 'rgba(245, 158, 11, 0.3)', stroke: '#f59e0b', strokeWidth: 2,
                    cornerColor: '#ffffff', cornerSize: 8, transparentCorners: false, hasRotatingPoint: false
                });
                switchTabUI('rect'); 
            }

            canvas.add(activeFloorShape);
            canvas.setActiveObject(activeFloorShape);
            updateCoordsFromShape(activeFloorShape);

            activeFloorShape.on('moving', () => updateCoordsFromShape(activeFloorShape));
            activeFloorShape.on('scaling', () => updateCoordsFromShape(activeFloorShape));
        }

        function clearActiveShapes() {
            polyDrawing = false;
            if(activeFloorShape) canvas.remove(activeFloorShape);
            if(boundingBoxDashed) canvas.remove(boundingBoxDashed);
            polyLines.forEach(l => canvas.remove(l));
            polyCircles.forEach(c => canvas.remove(c));
            
            activeFloorShape = null; boundingBoxDashed = null;
            polyPoints = []; polyLines = []; polyCircles = [];
            
            document.getElementById('btnStartPoly').innerText = "Mulai Gambar Bentuk Bebas (Redraw)";
            document.getElementById('btnStartPoly').classList.replace('bg-amber-600', 'bg-amber-600/20');
            document.getElementById('btnStartPoly').classList.replace('text-white', 'text-amber-400');
            document.getElementById('leftPanel').classList.remove('crosshair-cursor');
            
            canvas.renderAll();
        }

        function hideStep2() {
            document.getElementById('step2').classList.add('opacity-50', 'pointer-events-none');
            document.getElementById('btnDownloadTemplate').classList.add('hidden');
            document.getElementById('descTemplate').classList.add('hidden');
        }

        function spawnFloorBox() {
            clearActiveShapes();
            activeFloorShape = new fabric.Rect({
                left: parseFloat(targetBuilding.pos_x), top: parseFloat(targetBuilding.pos_y),
                originX: 'center', originY: 'center', width: 200, height: 120,
                fill: 'rgba(245, 158, 11, 0.3)', stroke: '#f59e0b', strokeWidth: 2,
                cornerColor: '#ffffff', cornerSize: 8, transparentCorners: false, hasRotatingPoint: false
            });
            canvas.add(activeFloorShape); canvas.setActiveObject(activeFloorShape);
            updateCoordsFromShape(activeFloorShape);
            activeFloorShape.on('moving', () => updateCoordsFromShape(activeFloorShape));
            activeFloorShape.on('scaling', () => updateCoordsFromShape(activeFloorShape));
        }

        function applyManualRatio() {
            if(!activeFloorShape || currentMode !== 'rect') return;
            let targetW = parseFloat(document.getElementById('ratioW').value);
            let targetH = parseFloat(document.getElementById('ratioH').value);
            if(targetW <= 0 || targetH <= 0 || isNaN(targetW) || isNaN(targetH)) return;

            let currentWidth = activeFloorShape.width * activeFloorShape.scaleX; 
            let calcHeight = (targetH / targetW) * currentWidth;

            activeFloorShape.set({ width: currentWidth, height: calcHeight, scaleX: 1, scaleY: 1 });
            
            let vpt = canvas.viewportTransform; canvas.setViewportTransform([1,0,0,1,0,0]);
            let br = activeFloorShape.getBoundingRect(); canvas.setViewportTransform(vpt);
            
            document.getElementById('box_width').value = br.width; document.getElementById('box_height').value = br.height;
            document.getElementById('box_left').value = br.left; document.getElementById('box_top').value = br.top;
            
            drawDashedBounds(br); canvas.requestRenderAll();
        }

        function startPolyDraw() {
            clearActiveShapes();
            hideStep2(); // <--- KUNCI UTAMANYA: Redupkan Tahap 2 & sembunyikan tombol Download!
            
            polyDrawing = true;
            document.getElementById('leftPanel').classList.add('crosshair-cursor');
            document.getElementById('btnStartPoly').innerText = "Sedang Menggambar... (Klik Kanan = Selesai)";
            document.getElementById('btnStartPoly').classList.replace('bg-amber-600/20', 'bg-amber-600');
            document.getElementById('btnStartPoly').classList.replace('text-amber-400', 'text-white');
        }

        canvas.on('mouse:down', function(o) {
            if (!polyDrawing || o.e.altKey) return;
            let pointer = canvas.getPointer(o.e);

            polyPoints.push({ x: pointer.x, y: pointer.y });
            let circle = new fabric.Circle({ radius: 4, fill: '#f59e0b', left: pointer.x, top: pointer.y, originX: 'center', originY: 'center', selectable: false, evented: false });
            polyCircles.push(circle); canvas.add(circle);

            if (polyPoints.length > 1) {
                let prev = polyPoints[polyPoints.length - 2];
                let line = new fabric.Line([prev.x, prev.y, pointer.x, pointer.y], { stroke: '#f59e0b', strokeWidth: 2, selectable: false, evented: false });
                polyLines.push(line); canvas.add(line);
            }
        });

        window.addEventListener('contextmenu', function(e) {
            if (polyDrawing && polyPoints.length >= 3) {
                e.preventDefault(); finishPolyDraw();
            } else if (polyDrawing) {
                e.preventDefault(); showToast("Minimal butuh 3 titik!", true);
            }
        });

        function finishPolyDraw() {
            polyDrawing = false;
            document.getElementById('leftPanel').classList.remove('crosshair-cursor');
            document.getElementById('btnStartPoly').innerText = "Gambar Ulang Bentuk Bebas (Redraw)";
            document.getElementById('btnStartPoly').classList.replace('bg-amber-600', 'bg-amber-600/20');
            document.getElementById('btnStartPoly').classList.replace('text-white', 'text-amber-400');

            polyLines.forEach(l => canvas.remove(l));
            polyCircles.forEach(c => canvas.remove(c));
            
            let minX = Math.min(...polyPoints.map(p => p.x));
            let minY = Math.min(...polyPoints.map(p => p.y));
            let normalizedPoints = polyPoints.map(p => ({ x: p.x - minX, y: p.y - minY }));

            activeFloorShape = new fabric.Polygon(normalizedPoints, { 
                left: minX, top: minY, originX: 'left', originY: 'top', // FIX: Paksa origin
                fill: 'rgba(245, 158, 11, 0.3)', stroke: '#f59e0b', strokeWidth: 3, 
                selectable: true, hasControls: true, cornerColor: '#ffffff', cornerSize: 8,
                objectCaching: false
            });
            
            canvas.add(activeFloorShape); canvas.setActiveObject(activeFloorShape);
            updateCoordsFromShape(activeFloorShape);

            activeFloorShape.on('moving', () => updateCoordsFromShape(activeFloorShape));
            activeFloorShape.on('scaling', () => updateCoordsFromShape(activeFloorShape));
        }

        // ==========================================
        // ENGINE: SMART EXTRACTOR & ANTI-SHIFT BUG
        // ==========================================
        function updateCoordsFromShape(obj) {
            if(!obj) return;
            
            let vpt = canvas.viewportTransform.slice();
            canvas.setViewportTransform([1,0,0,1,0,0]);
            let br = obj.getBoundingRect();

            // FIX: MATEMATIKA MURNI PENJINAK PATHOFFSET (MENCEGAH BENTUK BERGESER)
            if(obj.type === 'polygon') {
                let matrix = obj.calcTransformMatrix();
                let absPoints = obj.get('points').map(function(p) {
                    // Kurangi dengan pathOffset internal Fabric.js
                    let lx = p.x - obj.pathOffset.x;
                    let ly = p.y - obj.pathOffset.y;
                    return {
                        x: (lx * matrix[0]) + (ly * matrix[2]) + matrix[4],
                        y: (lx * matrix[1]) + (ly * matrix[3]) + matrix[5]
                    };
                });
                document.getElementById('polygon_points').value = JSON.stringify(absPoints);
            } else {
                document.getElementById('polygon_points').value = ""; 
            }

            canvas.setViewportTransform(vpt);

            document.getElementById('box_width').value = br.width;
            document.getElementById('box_height').value = br.height;
            document.getElementById('box_left').value = br.left;
            document.getElementById('box_top').value = br.top;

            if(currentMode === 'rect') {
                let gcd = (a, b) => b ? gcd(b, a % b) : a;
                let div = gcd(Math.round(br.width), Math.round(br.height));
                document.getElementById('ratioW').value = Math.round(br.width) / div;
                document.getElementById('ratioH').value = Math.round(br.height) / div;
            }

            drawDashedBounds(br);
            
            document.getElementById('step2').classList.remove('opacity-50', 'pointer-events-none');
            document.getElementById('btnDownloadTemplate').classList.remove('hidden');
            document.getElementById('descTemplate').classList.remove('hidden');
        }

        function drawDashedBounds(br) {
            if(boundingBoxDashed) canvas.remove(boundingBoxDashed);
            boundingBoxDashed = new fabric.Rect({
                left: br.left, top: br.top, width: br.width, height: br.height,
                fill: 'transparent', stroke: '#3b82f6', strokeWidth: 2, strokeDashArray: [5, 5],
                selectable: false, evented: false
            });
            canvas.add(boundingBoxDashed);
            
            canvas.sendToBack(boundingBoxDashed);
            let mapImg = canvas.getObjects('image')[0];
            if(mapImg) canvas.sendToBack(mapImg);
        }

        function downloadTemplate() {
            if (!activeFloorShape) return;

            let vpt = canvas.viewportTransform;
            canvas.setViewportTransform([1,0,0,1,0,0]);
            let br = activeFloorShape.getBoundingRect();

            if(boundingBoxDashed) boundingBoxDashed.set('opacity', 0);
            
            let originalFill = activeFloorShape.fill;
            let originalStroke = activeFloorShape.strokeWidth;
            activeFloorShape.set({ fill: 'transparent', strokeWidth: 5 });
            canvas.renderAll();

            let dataURL = canvas.toDataURL({
                format: 'png', left: br.left, top: br.top, width: br.width, height: br.height,
                multiplier: 2
            });

            activeFloorShape.set({ fill: originalFill, strokeWidth: originalStroke });
            if(boundingBoxDashed) boundingBoxDashed.set('opacity', 1);
            canvas.setViewportTransform(vpt);

            let a = document.createElement('a');
            a.href = dataURL;
            a.download = `Template_Lantai_${targetBuilding.name}.png`;
            a.click();
            
            showToast("Template berhasil diunduh! Berikan file ini ke Drafter.");
        }

        async function saveFloor(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSave');
            const formData = new FormData(e.target);
            
            btn.innerText = "Menyimpan Ke Database..."; btn.disabled = true;

            let endpoint = existingFloor ? '/floor/update-visual' : '/floor';

            try {
                const res = await fetch(endpoint, {
                    method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await res.json();
                if(res.ok) {
                    showToast(result.message); setTimeout(() => location.href = '/management', 1500); 
                } else { showToast("Error: Periksa inputan Tuan", true); }
            } catch (err) { showToast("Terjadi kesalahan jaringan", true);
            } finally { btn.innerText = "Simpan Data Lantai"; btn.disabled = false; }
        }

        canvas.on('mouse:wheel', function(opt) {
            let zoom = canvas.getZoom() * (0.999 ** opt.e.deltaY);
            if (zoom > 10) zoom = 10; if (zoom < 0.01) zoom = 0.01;
            canvas.zoomToPoint({ x: opt.e.offsetX, y: opt.e.offsetY }, zoom);
            opt.e.preventDefault(); opt.e.stopPropagation();
        });
        canvas.on('mouse:down', function(opt) {
            if (opt.e.altKey === true) { this.isDragging = true; this.lastPosX = opt.e.clientX; this.lastPosY = opt.e.clientY; document.getElementById('leftPanel').classList.add('grabbing-cursor'); }
        });
        canvas.on('mouse:move', function(opt) {
            if (this.isDragging) { let e = opt.e; let vpt = this.viewportTransform; vpt[4] += e.clientX - this.lastPosX; vpt[5] += e.clientY - this.lastPosY; this.requestRenderAll(); this.lastPosX = e.clientX; this.lastPosY = e.clientY; }
        });
        canvas.on('mouse:up', function() { this.setViewportTransform(this.viewportTransform); this.isDragging = false; document.getElementById('leftPanel').classList.remove('grabbing-cursor'); });

        function showToast(msg, isError = false) {
            const t = document.getElementById('toast');
            t.innerText = msg;
            t.className = `fixed bottom-10 right-10 px-6 py-3 rounded-lg shadow-2xl transform transition-all duration-300 z-[100] font-bold text-sm ${isError ? 'bg-red-600' : 'bg-emerald-600'} text-white translate-y-0 opacity-100`;
            setTimeout(() => { t.classList.remove('translate-y-0', 'opacity-100'); t.classList.add('translate-y-20', 'opacity-0'); }, 3000);
        }

        window.onload = initSplitScreen;
    </script>
</body>
</html>