<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Monitor - Network KBK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <style>
        body { background-color: #0b1120; }
        .glass-panel { background: rgba(30, 41, 59, 0.85); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(59, 130, 246, 0.5); border-radius: 10px; }
        .grabbing-cursor { cursor: grabbing !important; }
    </style>
</head>
<body class="text-slate-300 font-sans h-screen flex flex-col overflow-hidden relative">

    <nav class="p-5 bg-slate-900 border-b border-slate-800 flex justify-between items-center z-20 shadow-xl">
        <div class="flex items-center gap-4">
            <a href="/hub" class="text-slate-500 hover:text-white font-bold transition flex items-center gap-2">
                <span class="text-xl">&larr;</span> Kembali ke Hub
            </a>
            <div class="h-6 w-px bg-slate-700"></div>
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                <h1 class="text-lg font-black text-white uppercase tracking-widest">Live Monitor</h1>
            </div>
            <span class="text-xs text-blue-400 bg-blue-900/30 px-3 py-1 rounded border border-blue-800 font-bold uppercase tracking-widest">Global Dashboard</span>
        </div>
        <form action="/logout" method="POST">@csrf<button type="submit" class="text-slate-500 hover:text-red-400 font-bold transition">LOGOUT</button></form>
    </nav>

    <main class="flex-grow relative flex justify-center items-center bg-slate-950">
        <div id="canvasParent" class="w-full h-full relative overflow-hidden">
            
            <button id="btnArchView" onclick="toggleArchView()" class="absolute top-6 right-6 z-40 bg-slate-800 border border-slate-600 text-slate-400 font-bold px-6 py-3 rounded-xl shadow-2xl hover:scale-105 transition-all uppercase tracking-widest text-sm flex items-center gap-3">
                <span class="text-xl">👁️</span> Mode Denah Internal: OFF
            </button>
            <div class="absolute top-6 left-6 z-50 flex flex-col gap-2">
                <div class="glass-panel p-1 rounded-xl flex items-center border border-slate-600/50 shadow-2xl focus-within:border-blue-500 transition-all relative">
                    <span class="pl-3 text-slate-500">🔍</span>
                    <input type="text" id="radarSearch" placeholder="Cari Hostname / IP / Gedung..." 
                        class="bg-transparent border-none outline-none px-3 py-2 text-sm text-white w-56 placeholder:text-slate-600 font-bold tracking-wide"
                        onkeypress="if(event.key === 'Enter') executeRadarSearch()"
                        oninput="if(this.value.trim() === '') { document.getElementById('radarResults').classList.add('hidden'); document.getElementById('radarResults').classList.remove('flex'); }">
                    <button onclick="executeRadarSearch()" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg text-xs font-black uppercase transition shadow-lg">Scan</button>
                </div>
                
                <div id="radarResults" class="glass-panel w-full max-h-60 rounded-xl overflow-y-auto custom-scrollbar hidden flex-col border border-slate-600/50 shadow-[0_10px_30px_rgba(0,0,0,0.5)]">
                    </div>
            </div>

            <canvas id="liveCanvas"></canvas>
            
            <div class="absolute bottom-6 left-6 p-4 glass-panel rounded-xl text-xs space-y-2 pointer-events-none shadow-2xl">
                <div class="flex items-center gap-2 text-slate-400 font-bold tracking-wide">
                    <kbd class="bg-slate-800 border border-slate-600 px-2 py-1 rounded text-white font-mono shadow">Alt</kbd> + <kbd class="bg-slate-800 border border-slate-600 px-2 py-1 rounded text-white font-mono shadow">Drag</kbd> untuk Geser Kamera
                </div>
            </div>
        </div>
    </main>

    <div id="deviceTooltip" class="fixed z-[200] hidden glass-panel px-4 py-2 rounded-lg pointer-events-none shadow-2xl transition-opacity duration-200 border border-slate-600">
        </div>

    <div id="deviceModal" class="fixed inset-0 z-[300] hidden bg-[#0b1120]/80 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
        <div class="glass-panel p-8 rounded-2xl border border-slate-700 w-96 transform scale-95 transition-transform duration-300 shadow-[0_0_50px_rgba(59,130,246,0.15)] relative">
            <button onclick="closeDeviceInfo()" class="absolute top-4 right-4 text-slate-400 hover:text-red-400 transition text-2xl font-black">&times;</button>
            
            <div class="flex items-center gap-4 mb-6 pb-4 border-b border-slate-700">
                <div id="modalDevIcon" class="w-14 h-14 bg-blue-900/50 rounded-xl flex items-center justify-center text-3xl shadow-inner border border-blue-500/30">🖧</div>
                <div>
                    <h2 id="modalDevName" class="text-xl font-black text-white tracking-wide">Hostname</h2>
                    <p id="modalDevType" class="text-[10px] uppercase tracking-widest text-blue-400 font-bold">Switch / Router</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-slate-900/60 p-3 rounded-lg border border-slate-800">
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Alamat IP / Jaringan</p>
                    <p id="modalDevIP" class="text-sm text-emerald-400 font-mono font-bold">192.168.1.1</p>
                </div>
                <div class="bg-slate-900/60 p-3 rounded-lg border border-slate-800">
                    <p id="modalDevLabel3" class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Merk / Brand</p>
                    <p id="modalDevValue3" class="text-sm text-amber-400 font-bold uppercase">-</p>
                </div>
                <div id="modalDevStatusWrapper" class="bg-slate-900/60 p-3 rounded-lg border border-slate-800 hidden">
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Status Operasional PC</p>
                    <p id="modalDevStatus" class="text-sm text-teal-400 font-black uppercase tracking-wider">-</p>
                </div>
                <div class="bg-slate-900/60 p-3 rounded-lg border border-slate-800">
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Tahun Pemasangan</p>
                    <p id="modalDevYear" class="text-sm text-white font-bold uppercase">-</p>
                </div>
            </div>
        </div>
    </div>

    <div id="deepDiveModal" class="fixed inset-0 z-[100] bg-[#0b1120]/80 backdrop-blur-md hidden flex-row opacity-0 transition-opacity duration-300">
        <div class="w-[340px] h-full bg-slate-900/30 backdrop-blur-2xl border-r border-slate-700/50 flex flex-col shadow-[20px_0_50px_rgba(0,0,0,0.5)] z-20">
            <div class="p-8 pb-4">
                <button onclick="closeDeepDive()" class="text-slate-400 hover:text-white font-bold mb-6 flex items-center gap-2 transition text-sm">&larr; Tutup Tampilan</button>
                <h2 id="ddBuildingName" class="text-3xl font-black text-white tracking-tight leading-none mb-2">Nama Gedung</h2>
                <p class="text-[10px] text-blue-400 font-bold uppercase tracking-widest">Detail Perangkat Internal</p>
            </div>
            <div id="ddFloorList" class="flex-grow overflow-y-auto px-6 pb-6 pt-2 custom-scrollbar space-y-4"></div>
        </div>
        <div class="flex-grow h-full relative" id="ddCanvasParent">
            <div class="absolute top-6 right-6 z-20 bg-slate-900/80 backdrop-blur border border-slate-700 px-4 py-2 rounded-xl text-xs font-bold text-slate-400 shadow-2xl pointer-events-none flex items-center gap-2">
                <kbd class="bg-slate-800 border border-slate-600 px-2 py-1 rounded text-white font-mono">Alt</kbd> + <kbd class="bg-slate-800 border border-slate-600 px-2 py-1 rounded text-white font-mono">Drag</kbd> Geser Kamera
            </div>
            <canvas id="deepDiveCanvas"></canvas>
        </div>
    </div>

    <script>
        const buildings = @json($buildings);
        const canvas = new fabric.Canvas('liveCanvas', { selection: false });
        const ddCanvas = new fabric.Canvas('deepDiveCanvas', { selection: false, hoverCursor: 'default' });
        
        let isArchViewOn = false;
        let archObjects = []; 

        function initMap() {
            const parent = document.getElementById('canvasParent');
            canvas.setWidth(parent.clientWidth);
            canvas.setHeight(parent.clientHeight);

            fabric.Image.fromURL('/img/denah-master.png', function(img) {
                img.set({ originX: 'left', originY: 'top', left: 0, top: 0, scaleX: 1, scaleY: 1, selectable: false, evented: false });
                canvas.add(img); img.sendToBack(); 

                let zoomX = canvas.width / img.width; let zoomY = canvas.height / img.height;
                let initialZoom = Math.min(zoomX, zoomY) * 0.9; canvas.setZoom(initialZoom);
                let panX = (canvas.width - (img.width * initialZoom)) / 2; let panY = (canvas.height - (img.height * initialZoom)) / 2;
                canvas.absolutePan(new fabric.Point(-panX, -panY));

                renderGlobalProjections();
            });
        }

        function renderGlobalProjections() {
            buildings.forEach(b => {
                if(b.polygon_points) {
                    let poly = new fabric.Polygon(JSON.parse(b.polygon_points), {
                        fill: 'rgba(59, 130, 246, 0.05)', stroke: 'rgba(59, 130, 246, 0.3)', strokeWidth: 1,
                        selectable: false, hasControls: false, hoverCursor: 'default', isBuildingPoly: true
                    });
                    canvas.add(poly);
                }

                if(b.floors && b.floors.length > 0) {
                    let renderedBoxes = []; // Kunci Algoritma Smart Overlap!

                    b.floors.forEach((f, index) => {
                        if(!f.image_path || !f.box_width) return;
                        
                        let fB_W = parseFloat(f.box_width); let fB_H = parseFloat(f.box_height);
                        let fB_L = parseFloat(f.box_left); let fB_T = parseFloat(f.box_top);
                        let fB_R = fB_L + fB_W; let fB_B = fB_T + fB_H;

                        // DETEKSI TABRAKAN LANTAI
                        let isOverlapping = renderedBoxes.some(box => {
                            // Rumus AABB Intersect (Apakah menabrak batas kotak sebelumnya?)
                            return !(fB_L >= box.right || fB_R <= box.left || fB_T >= box.bottom || fB_B <= box.top);
                        });

                        fabric.Image.fromURL(f.image_path, function(img) {
                            let scaleX = fB_W / img.width; let scaleY = fB_H / img.height;
                            
                            // Jika TIDAK bertabrakan (berbeda ruangan), tampilkan denahnya!
                            if(!isOverlapping) {
                                renderedBoxes.push({left: fB_L, right: fB_R, top: fB_T, bottom: fB_B});

                                img.set({ originX: 'left', originY: 'top', left: fB_L, top: fB_T, scaleX: scaleX, scaleY: scaleY, selectable: false, evented: true, hoverCursor: 'pointer', opacity: 0, isArchObj: true });
                                
                                img.on('mouseover', () => { if(isArchViewOn) { img.set('opacity', 1); canvas.renderAll(); } });
                                img.on('mouseout', () => { if(isArchViewOn) { img.set('opacity', 0.85); canvas.renderAll(); } });
                                
                                // Jika diklik, buka Deep Dive dan LANGSUNG arahkan ke Lantai ini!
                                img.on('mousedown', () => { if(isArchViewOn) openDeepDive(b, f.id); });
                                
                                canvas.add(img); archObjects.push(img);

                                // Render Badge Lantai
                                let badgeText = new fabric.Text(`${b.floors.length} Lantai`, { fontSize: 14, fill: '#ffffff', fontWeight: 'bold', originX: 'center', originY: 'center', left: 0, top: 0 });
                                let badgeRect = new fabric.Rect({ width: badgeText.width + 16, height: badgeText.height + 10, fill: '#e11d48', rx: 6, ry: 6, originX: 'center', originY: 'center', left: 0, top: 0, shadow: new fabric.Shadow({ color: '#e11d48', blur: 10 }) });
                                let badgeGroup = new fabric.Group([badgeRect, badgeText], { originX: 'right', originY: 'bottom', left: fB_R + 20, top: fB_T - 10, opacity: 0, selectable: false, evented: false, isArchObj: true });
                                canvas.add(badgeGroup); archObjects.push(badgeGroup);
                            }

                            // Render Titik Perangkat 
                            let switches = (f.switch_nodes || f.switchNodes || []).map(d => ({...d, type: 'switch'}));
                            let pcs = (f.pc_nodes || f.pcNodes || []).map(d => ({...d, type: 'pc'}));
                            let devices = [...switches, ...pcs];
                            
                            devices.forEach(dev => {
                                let iconPath = dev.type === 'switch' ? '/img/switch.png' : '/img/pc.png';
                                let glowColor = dev.type === 'switch' ? '#3b82f6' : '#14b8a6';
                                let localX = parseFloat(dev.pos_x); let localY = parseFloat(dev.pos_y);

                                fabric.Image.fromURL(iconPath, function(iconImg) {
                                    iconImg.scaleToWidth(24);
                                    iconImg.set({
                                        left: fB_L + (localX * scaleX),
                                        top: fB_T + (localY * scaleY),
                                        originX: 'center', originY: 'center',
                                        selectable: false, evented: true, hoverCursor: 'pointer',
                                        shadow: new fabric.Shadow({ color: glowColor, blur: 15 }),
                                        deviceData: { ...dev, buildingName: b.name, floorName: f.name }// <--- INI KUNCI RADARNYA!
                                    });

                                    // EVENT INFO PERANGKAT GLOBAL
                                    iconImg.on('mouseover', (opt) => showDeviceTooltip(opt, dev));
                                    iconImg.on('mouseout', () => hideDeviceTooltip());
                                    iconImg.on('mousedown', () => showDeviceInfoModal(dev));

                                    canvas.add(iconImg);
                                    canvas.requestRenderAll();
                                });
                            });
                        });
                    });
                }
            });
        }

        function toggleArchView() {
            isArchViewOn = !isArchViewOn;
            const btn = document.getElementById('btnArchView');

            if(isArchViewOn) {
                btn.innerHTML = "<span class='text-xl animate-pulse'>🔴</span> Mode Denah Internal: ON";
                btn.classList.replace('bg-slate-800', 'bg-blue-600/20'); btn.classList.replace('border-slate-600', 'border-blue-500'); btn.classList.replace('text-slate-400', 'text-blue-400');
                
                canvas.getObjects('polygon').forEach(p => { if(p.isBuildingPoly) p.set('opacity', 0); });
                archObjects.forEach(o => { o.set('opacity', o.type === 'image' ? 0.85 : 1); });
            } else {
                btn.innerHTML = "<span class='text-xl'>👁️</span> Mode Denah Internal: OFF";
                btn.classList.replace('bg-blue-600/20', 'bg-slate-800'); btn.classList.replace('border-blue-500', 'border-slate-600'); btn.classList.replace('text-blue-400', 'text-slate-400');
                
                canvas.getObjects('polygon').forEach(p => { if(p.isBuildingPoly) p.set('opacity', 1); });
                archObjects.forEach(o => o.set('opacity', 0));
            }
            canvas.renderAll();
        }

        // =========================================================
        // BAGIAN 2: DEEP DIVE & RENDER DETAIL LANTAI
        // =========================================================

        function openDeepDive(building, targetFloorId = null) {
            const modal = document.getElementById('deepDiveModal');
            modal.classList.remove('hidden'); modal.classList.add('flex');
            
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                const parent = document.getElementById('ddCanvasParent');
                ddCanvas.setWidth(parent.clientWidth); ddCanvas.setHeight(parent.clientHeight);

                document.getElementById('ddBuildingName').innerText = building.name;
                renderFloorSidebar(building.floors, targetFloorId);

                // Auto Select Floor yang dipilih (Atau fallback ke lantai 1)
                let selectedFloor = building.floors.find(f => f.id === targetFloorId) || building.floors[0];
                if(selectedFloor) loadFloorDetail(selectedFloor);
            }, 50); 
        }

        function renderFloorSidebar(floors, activeFloorId = null) {
            const floorList = document.getElementById('ddFloorList');
            floorList.innerHTML = '';
            
            if(!activeFloorId && floors.length > 0) activeFloorId = floors[0].id;

            floors.forEach((f) => {
                let isActive = f.id === activeFloorId;
                let bgImage = f.image_path ? `url('${f.image_path}')` : 'none';
                
                let btn = document.createElement('button');
                btn.className = `w-full relative rounded-2xl overflow-hidden border transition-all group min-h-[100px] flex items-end text-left ${isActive ? 'border-blue-500 shadow-[0_0_20px_rgba(59,130,246,0.3)]' : 'border-slate-700/50 hover:border-slate-400'}`;
                
                btn.innerHTML = `
                    <div class="absolute inset-0 bg-slate-800 bg-cover bg-center opacity-40 transition-all duration-500 ${isActive ? 'opacity-70 scale-110' : 'group-hover:opacity-70 group-hover:scale-105'}" style="background-image: ${bgImage};"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-transparent"></div>
                    <div class="relative p-4 w-full transform ${isActive ? 'translate-y-0' : 'translate-y-2 group-hover:translate-y-0'} transition-transform">
                        <h3 class="text-lg font-black text-white shadow-black drop-shadow-md flex items-center gap-2">
                            ${isActive ? '<span class="text-blue-500 text-sm">●</span>' : ''} ${f.name}
                        </h3>
                    </div>
                `;
                
                btn.onclick = () => { renderFloorSidebar(floors, f.id); loadFloorDetail(f); };
                floorList.appendChild(btn);
            });
        }

        function loadFloorDetail(floor) {
            ddCanvas.clear(); ddCanvas.setViewportTransform([1, 0, 0, 1, 0, 0]); 
            if(!floor.image_path) return;

            fabric.Image.fromURL(floor.image_path, function(img) {
                img.set({ originX: 'left', originY: 'top', left: 0, top: 0, scaleX: 1, scaleY: 1, selectable: false, evented: false });
                ddCanvas.setBackgroundImage(img, ddCanvas.renderAll.bind(ddCanvas));

                let zoomX = ddCanvas.width / img.width; let zoomY = ddCanvas.height / img.height;
                let targetZoom = Math.min(zoomX, zoomY) * 0.85; 
                ddCanvas.setZoom(targetZoom);

                let vpt = ddCanvas.viewportTransform;
                vpt[4] = (ddCanvas.width - (img.width * targetZoom)) / 2;
                vpt[5] = (ddCanvas.height - (img.height * targetZoom)) / 2;
                ddCanvas.setViewportTransform(vpt);

                let switches = (floor.switch_nodes || floor.switchNodes || []).map(d => ({...d, type: 'switch'}));
                let pcs = (floor.pc_nodes || floor.pcNodes || []).map(d => ({...d, type: 'pc'}));
                let devices = [...switches, ...pcs];
                
                devices.forEach(dev => {
                    let iconPath = dev.type === 'switch' ? '/img/switch.png' : '/img/pc.png';
                    let glowColor = dev.type === 'switch' ? '#3b82f6' : '#14b8a6';

                    fabric.Image.fromURL(iconPath, function(iconImg) {
                        iconImg.scaleToWidth(50);
                        iconImg.set({ originX: 'center', originY: 'center', shadow: new fabric.Shadow({ color: glowColor, blur: 20 }) });

                        let labelText = dev.name.length > 12 ? dev.name.substring(0,10)+'...' : dev.name;
                        let text = new fabric.Text(labelText, { 
                            fontSize: 12, fill: '#ffffff', fontFamily: 'sans-serif', 
                            originX: 'center', originY: 'top', top: 30, fontWeight: 'bold', backgroundColor: 'rgba(15, 23, 42, 0.7)' 
                        });

                        // Grup untuk Hover & Click di Deep Dive
                        let group = new fabric.Group([iconImg, text], { 
                            left: parseFloat(dev.pos_x), top: parseFloat(dev.pos_y), 
                            originX: 'center', originY: 'center', selectable: false, evented: true, hoverCursor: 'pointer' 
                        });

                        group.on('mouseover', (opt) => showDeviceTooltip(opt, dev));
                        group.on('mouseout', () => hideDeviceTooltip());
                        group.on('mousedown', () => showDeviceInfoModal(dev));
                        
                        ddCanvas.add(group);
                        ddCanvas.requestRenderAll();
                    });
                });
            });
        }

        function closeDeepDive() {
            const modal = document.getElementById('deepDiveModal');
            modal.classList.add('opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 300);
            ddCanvas.clear(); hideDeviceTooltip();
        }

        // =========================================================
        // BAGIAN 3: INFO PERANGKAT (TOOLTIP & MODAL GLASSMORPHISM)
        // =========================================================

        function showDeviceTooltip(opt, dev) {
            const tooltip = document.getElementById('deviceTooltip');
            tooltip.innerHTML = `<span class="font-black text-white text-sm">${dev.name}</span><br><span class="text-[10px] text-blue-400 font-mono tracking-widest">${dev.ip_address || 'Tidak ada data IP'}</span>`;
            // Posisi menyesuaikan kursor mouse
            tooltip.style.left = (opt.e.clientX + 15) + 'px';
            tooltip.style.top = (opt.e.clientY + 15) + 'px';
            tooltip.classList.remove('hidden');
        }

        function hideDeviceTooltip() {
            document.getElementById('deviceTooltip').classList.add('hidden');
        }

        function showDeviceInfoModal(dev) {
            hideDeviceTooltip(); // Matikan tooltip saat modal terbuka
            const modal = document.getElementById('deviceModal');
            const mContent = modal.querySelector('div.glass-panel');

            // Set Data Umum
            document.getElementById('modalDevName').innerText = dev.name;
            document.getElementById('modalDevIP').innerText = dev.ip_address || 'TIDAK TERIDENTIFIKASI';
            document.getElementById('modalDevYear').innerText = dev.installation_year || 'Tidak Diketahui';

            // Spesifik berdasarkan Jenis Perangkat
            if(dev.type === 'switch') {
                document.getElementById('modalDevIcon').innerText = '🖧';
                document.getElementById('modalDevIcon').className = 'w-14 h-14 bg-blue-900/50 rounded-xl flex items-center justify-center text-3xl shadow-inner border border-blue-500/30';
                document.getElementById('modalDevType').innerText = 'Switch / Router / Server';
                document.getElementById('modalDevType').className = 'text-[10px] uppercase tracking-widest text-blue-400 font-bold';
                
                document.getElementById('modalDevLabel3').innerText = 'Merk / Brand / Vendor';
                document.getElementById('modalDevValue3').innerText = dev.brand_model || '-'; 
                document.getElementById('modalDevStatusWrapper').classList.add('hidden'); // Sembunyikan status
            } else {
                document.getElementById('modalDevIcon').innerText = '💻';
                document.getElementById('modalDevIcon').className = 'w-14 h-14 bg-teal-900/50 rounded-xl flex items-center justify-center text-3xl shadow-inner border border-teal-500/30';
                document.getElementById('modalDevType').innerText = 'PC / Client Desktop';
                document.getElementById('modalDevType').className = 'text-[10px] uppercase tracking-widest text-teal-400 font-bold';
                
                document.getElementById('modalDevLabel3').innerText = 'Pengguna / Divisi Pemilik';
                document.getElementById('modalDevValue3').innerText = dev.current_user || '-'; 
                
                // Tampilkan dan set status PC
                document.getElementById('modalDevStatusWrapper').classList.remove('hidden');
                document.getElementById('modalDevStatus').innerText = dev.status || '-';
            }

            // Animasi Buka Modal
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                mContent.classList.remove('scale-95');
            }, 10);
        }

        function closeDeviceInfo() {
            const modal = document.getElementById('deviceModal');
            const mContent = modal.querySelector('div.glass-panel');
            modal.classList.add('opacity-0');
            mContent.classList.add('scale-95');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }

        // =========================================================
        // BAGIAN 4: SISTEM PAN & ZOOM 
        // =========================================================
        
        function attachNavEvents(targetCanvas, parentId) {
            targetCanvas.on('mouse:wheel', function(opt) {
                let zoom = targetCanvas.getZoom() * (0.999 ** opt.e.deltaY);
                if (zoom > 10) zoom = 10; if (zoom < 0.1) zoom = 0.1;
                targetCanvas.zoomToPoint({ x: opt.e.offsetX, y: opt.e.offsetY }, zoom);
                opt.e.preventDefault(); opt.e.stopPropagation();
            });
            targetCanvas.on('mouse:down', function(opt) {
                if (opt.e.altKey === true) { this.isDragging = true; this.lastPosX = opt.e.clientX; this.lastPosY = opt.e.clientY; document.getElementById(parentId).classList.add('grabbing-cursor'); }
            });
            targetCanvas.on('mouse:move', function(opt) {
                if (this.isDragging) {
                    let e = opt.e; let vpt = this.viewportTransform;
                    vpt[4] += e.clientX - this.lastPosX; vpt[5] += e.clientY - this.lastPosY;
                    this.requestRenderAll(); this.lastPosX = e.clientX; this.lastPosY = e.clientY;
                } else if (!this.isDragging && !document.getElementById('deviceTooltip').classList.contains('hidden')) {
                    // Update posisi Tooltip terus-menerus saat mouse bergerak di atas perangkat
                    const tooltip = document.getElementById('deviceTooltip');
                    tooltip.style.left = (opt.e.clientX + 15) + 'px';
                    tooltip.style.top = (opt.e.clientY + 15) + 'px';
                }
            });
            targetCanvas.on('mouse:up', function() { this.setViewportTransform(this.viewportTransform); this.isDragging = false; document.getElementById(parentId).classList.remove('grabbing-cursor'); });
        }

        attachNavEvents(canvas, 'canvasParent');
        attachNavEvents(ddCanvas, 'ddCanvasParent');

        window.onload = initMap;
        window.onresize = () => { initMap(); if(!document.getElementById('deepDiveModal').classList.contains('hidden')) { const p = document.getElementById('ddCanvasParent'); ddCanvas.setWidth(p.clientWidth); ddCanvas.setHeight(p.clientHeight); }};

        // Fungsi mengeksekusi pencarian
        function executeRadarSearch() {
            const query = document.getElementById('radarSearch').value.toLowerCase();
            const resultBox = document.getElementById('radarResults');
            resultBox.innerHTML = ''; // Kosongkan hasil sebelumnya

            if (!query) {
                resultBox.classList.add('hidden');
                return;
            }

            const objects = canvas.getObjects();
            let matches = [];

            // Filter objek gambar (ikon)
            objects.forEach(obj => {
                if (obj.deviceData) {
                    const dName = (obj.deviceData.name || '').toLowerCase();
                    const dIp = (obj.deviceData.ip_address || '').toLowerCase();
                    const bName = (obj.deviceData.buildingName || '').toLowerCase();
                    
                    // Coba cocokkan dengan nama perangkat, IP, atau nama gedungnya
                    if (dName.includes(query) || dIp.includes(query) || bName.includes(query)) {
                        matches.push(obj);
                    }
                }
            });

            if (matches.length === 0) {
                resultBox.innerHTML = '<div class="p-4 text-xs font-bold text-slate-400 text-center uppercase tracking-widest">Tidak ada target ditemukan</div>';
                resultBox.classList.replace('hidden', 'flex');
            } else if (matches.length === 1) {
                // Jika hanya 1 yang cocok, langsung TERBANG!
                resultBox.classList.add('hidden');
                flyToTarget(matches[0]);
            } else {
                // Jika LEBIH DARI 1, tampilkan di Dropdown List
                matches.forEach(target => {
                    const btn = document.createElement('button');
                    btn.className = "text-left w-full p-3 border-b border-slate-700/50 hover:bg-blue-600/20 transition-all group last:border-0 focus:outline-none";
                    btn.innerHTML = `
                        <div class="font-black text-white text-sm group-hover:text-blue-400 transition-colors">${target.deviceData.name}</div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1 flex justify-between">
                            <span>${target.deviceData.type === 'switch' ? '🖧 Switch' : '💻 PC'}</span>
                            <span>📍 ${target.deviceData.buildingName} - ${target.deviceData.floorName}</span>
                        </div>
                    `;
                    // Jika diklik, sembunyikan list dan TERBANG!
                    btn.onclick = () => {
                        resultBox.classList.add('hidden');
                        flyToTarget(target);
                    };
                    resultBox.appendChild(btn);
                });
                resultBox.classList.replace('hidden', 'flex');
            }
        }

        // Fungsi Kamera Terbang (Dipisah agar bisa dipanggil oleh tombol list)
        function flyToTarget(target) {
            const zoomLevel = 2.5; 
            const currentVpt = canvas.viewportTransform.slice(0);
            
            const targetVpt = currentVpt.slice(0);
            targetVpt[0] = zoomLevel; targetVpt[3] = zoomLevel; 
            targetVpt[4] = (canvas.width / 2) - (target.left * zoomLevel); 
            targetVpt[5] = (canvas.height / 2) - (target.top * zoomLevel); 

            fabric.util.animate({
                startValue: 0, endValue: 1, duration: 800, easing: fabric.util.ease.easeInOutQuad,
                onChange: function(value) {
                    const newVpt = currentVpt.map((startVal, idx) => startVal + (targetVpt[idx] - startVal) * value);
                    canvas.setViewportTransform(newVpt);
                },
                onComplete: function() {
                    canvas.setViewportTransform(targetVpt);
                    
                    target.set('opacity', 0);
                    setTimeout(() => { target.set('opacity', 1); canvas.renderAll(); }, 200);
                    setTimeout(() => { target.set('opacity', 0); canvas.renderAll(); }, 400);
                    setTimeout(() => { target.set('opacity', 1); canvas.renderAll(); }, 600);
                    
                    showDeviceInfoModal(target.deviceData);
                }
            });
        }
    </script>
</body>
</html> 