<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viewer - Denah {{ $floor->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <style>
        body { background-color: #0f172a; overflow: hidden; }
        .glass-panel { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .glass-nav { background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        #deviceModal { z-index: 9999; }
    </style>
</head>
<body class="text-slate-300 font-sans h-screen flex flex-col relative">
    
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[20%] right-[-10%] w-96 h-96 bg-teal-900 rounded-full mix-blend-screen filter blur-[120px] opacity-20"></div>
    </div>

    <header class="glass-nav px-6 py-4 flex items-center justify-between shadow-2xl relative z-20">
        <div class="flex items-center gap-5">
            <a href="/viewer" class="bg-slate-800/80 text-slate-300 font-bold px-4 py-2 rounded-lg text-sm border border-slate-600 hover:bg-slate-700 hover:text-white transition flex items-center gap-2">
                &larr; Direktori Topologi
            </a>
            <div class="h-6 w-px bg-slate-700"></div>
            <div>
                <h1 class="text-lg font-black text-white tracking-wide">{{ $floor->building->name }} <span class="text-teal-500">|</span> {{ $floor->name }}</h1>
                <p class="text-xs text-slate-400 uppercase tracking-widest font-semibold">Live Monitoring View</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="relative flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span></span>
            <span class="text-sm font-bold text-green-400 tracking-wide">READ ONLY</span>
        </div>
    </header>

    <main class="flex-grow flex justify-center items-center bg-[#0b1121] relative p-6 lg:p-10 shadow-inner">
        <div id="canvasContainer" class="w-full max-w-7xl h-full rounded-2xl border border-slate-700 bg-black relative flex justify-center items-center overflow-hidden shadow-[0_0_40px_rgba(0,0,0,0.8)]">
            <canvas id="mappingCanvas"></canvas>
        </div>
    </main>

    <div id="deviceModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden flex justify-center items-center transition-opacity">
        <div class="glass-panel p-8 rounded-2xl shadow-[0_0_30px_rgba(0,0,0,0.5)] w-96 relative border-t-4 border-teal-500">
            <button id="closeModal" class="absolute top-4 right-5 text-slate-500 hover:text-red-400 font-black text-xl transition">&times;</button>
            <h2 id="modalName" class="text-2xl font-black mb-6 text-white tracking-wide border-b border-slate-700 pb-3">Nama Perangkat</h2>
            <div class="space-y-4 mb-8 text-slate-300 text-sm">
                <div><span class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">IP Address</span><span id="modalIp" class="font-mono text-lg text-teal-400 font-bold">-</span></div>
                <div id="wrapperBrand" class="hidden"><span class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Merek / Model</span><span id="modalBrand" class="font-semibold text-white">-</span></div>
                <div id="wrapperUser" class="hidden"><span class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Pengguna Saat Ini</span><span id="modalUser" class="font-semibold text-white">-</span></div>
            </div>
            <button id="closeModalBtn" class="bg-slate-800 border border-slate-600 text-slate-300 px-4 py-3 rounded-xl hover:bg-slate-700 hover:text-white transition font-bold w-full text-sm uppercase tracking-widest shadow-lg">Tutup Panel</button>
        </div>
    </div>

    <script>
        const existingSwitches = @json($switches);
        const existingPcs = @json($pcs);
        const existingConnections = @json($connections);
        const imageUrl = "{{ asset('storage/' . $floor->image_path) }}";

        const canvas = new fabric.Canvas('mappingCanvas', { selection: false, hoverCursor: 'pointer', interactive: false });

        document.addEventListener("DOMContentLoaded", function() {
            fabric.Image.fromURL(imageUrl, function(img) {
                const container = document.getElementById('canvasContainer');
                let canvasWidth = container.offsetWidth > 10 ? container.offsetWidth - 2 : 1000; 
                let canvasHeight = (9 / 16) * canvasWidth;
                
                canvas.setWidth(canvasWidth); canvas.setHeight(canvasHeight);
                
                if (img && img.width) {
                    canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), { scaleX: canvasWidth / img.width, scaleY: canvasHeight / img.height });
                }
                
                existingSwitches.forEach(sw => drawDevice(sw, 'switch'));
                existingPcs.forEach(pc => drawDevice(pc, 'pc'));
                drawAllCables();
            });
        });

        function drawDevice(data, type) {
            let color = type === 'switch' ? '#3b82f6' : '#14b8a6'; 
            let rect = new fabric.Rect({ width: 80, height: 40, fill: color, rx: type === 'pc'? 20 : 5, ry: type === 'pc'? 20 : 5, originX: 'center', originY: 'center', shadow: new fabric.Shadow({ color: color, blur: 15, offsetX: 0, offsetY: 0 }) });
            let shortName = data.name.length > 10 ? data.name.substring(0, 8) + '...' : data.name;
            let text = new fabric.Text(shortName, { fontSize: 12, fill: '#ffffff', fontFamily: 'sans-serif', originX: 'center', originY: 'center', fontWeight: 'bold' });
            let group = new fabric.Group([rect, text], { left: data.pos_x, top: data.pos_y, selectable: false, evented: true, hasControls: false, hasBorders: false, id: data.id, full_name: data.name, ip_address: data.ip_address, brand_model: data.brand_model, current_user: data.current_user, deviceType: type });
            canvas.add(group);
        }

        function drawAllCables() {
            existingConnections.forEach(conn => {
                const fromDev = canvas.getObjects('group').find(obj => obj.id == conn.from_id && obj.deviceType === conn.from_type);
                const toDev = canvas.getObjects('group').find(obj => obj.id == conn.to_id && obj.deviceType === conn.to_type);
                if (fromDev && toDev) drawCableLine(fromDev, toDev, conn.color);
            });
            canvas.renderAll();
        }

        function drawCableLine(fromDev, toDev, color) {
            let coords = [fromDev.left, fromDev.top, toDev.left, toDev.top];
            let line = new fabric.Line(coords, { stroke: color, strokeWidth: 4, selectable: false, evented: false, shadow: new fabric.Shadow({ color: color, blur: 8, offsetX: 0, offsetY: 0 }) });
            canvas.add(line); canvas.sendToBack(line);
        }

        canvas.on('mouse:down', function(options) {
            if (options.target && options.target.deviceType) {
                let obj = options.target;
                document.getElementById('modalName').innerText = obj.full_name || 'Tanpa Nama';
                document.getElementById('modalIp').innerText = obj.ip_address || '-';
                if(obj.deviceType === 'switch') {
                    document.getElementById('wrapperBrand').classList.remove('hidden'); document.getElementById('wrapperUser').classList.add('hidden'); document.getElementById('modalBrand').innerText = obj.brand_model || '-';
                } else if(obj.deviceType === 'pc') {
                    document.getElementById('wrapperUser').classList.remove('hidden'); document.getElementById('wrapperBrand').classList.add('hidden'); document.getElementById('modalUser').innerText = obj.current_user || '-';
                }
                document.getElementById('deviceModal').classList.remove('hidden');
            }
        });

        const closeModal = () => document.getElementById('deviceModal').classList.add('hidden');
        document.getElementById('closeModal').onclick = closeModal;
        document.getElementById('closeModalBtn').onclick = closeModal;
    </script>
</body>
</html>