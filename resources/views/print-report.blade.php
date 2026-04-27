<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Topologi - {{ $floor->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    <style>
        /* Tema Gelap untuk Tampilan Web */
        body { background-color: #0f172a; overflow-x: hidden; }
        .glass-nav { background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255, 255, 255, 0.1); z-index: 50; }
        
        /* Pengaturan Kertas Print */
        @media print {
            body { background-color: white !important; background-image: none !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            .print-paper { box-shadow: none !important; border: none !important; margin: 0 !important; padding: 0 !important; max-width: 100% !important; background-color: white !important; }
        }
        #topologyNetwork { width: 100%; height: 500px; background-color: #f8fafc; border-radius: 0.5rem; border: 2px solid #e2e8f0; }
        .vis-network { outline: none; }
    </style>
</head>
<body class="font-sans relative text-slate-800">

    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none no-print">
        <div class="absolute top-[-10%] right-[-10%] w-96 h-96 bg-blue-900 rounded-full mix-blend-screen filter blur-[120px] opacity-20"></div>
    </div>

    <div class="glass-nav sticky top-0 w-full px-8 py-4 flex justify-between items-center shadow-xl no-print">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-teal-400 text-white flex items-center justify-center font-black text-lg rounded-lg shadow-[0_0_15px_rgba(37,99,235,0.4)]">KBK</div>
            <h1 class="text-white font-bold tracking-wide">Report Generator System</h1>
        </div>
        <div class="flex items-center gap-4">
            <a href="javascript:history.back()" class="bg-slate-800 border border-slate-600 text-slate-300 px-5 py-2.5 rounded-xl font-bold hover:bg-slate-700 transition text-sm">
                &larr; Batal & Kembali
            </a>
            <button onclick="window.print()" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-6 py-2.5 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(79,70,229,0.4)]">
                🖨️ Cetak & Simpan PDF
            </button>
        </div>
    </div>

    <div class="max-w-4xl mx-auto bg-white p-12 shadow-[0_0_40px_rgba(0,0,0,0.5)] border border-slate-700 mt-10 mb-20 relative z-10 print-paper">
        
        <div class="flex items-center justify-between border-b-4 border-gray-800 pb-4 mb-8">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-blue-800 text-white flex items-center justify-center font-bold text-2xl rounded-lg shadow-inner">KBK</div>
                <div>
                    <h1 class="text-2xl font-black text-gray-800 uppercase tracking-wide">PT. Krakatau Baja Konstruksi</h1>
                    <p class="text-sm text-gray-600 font-semibold">Departemen IT Infrastructure & Network</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-gray-800">LAPORAN TOPOLOGI</p>
                <p class="text-xs text-gray-500">Dicetak: {{ date('d M Y') }}</p>
            </div>
        </div>

        <div class="text-center mb-8">
            <h2 class="text-xl font-bold uppercase text-gray-900">Dokumentasi Jaringan: {{ $floor->building->name }} - {{ $floor->name }}</h2>
        </div>

        <div class="mb-10">
            <h3 class="text-md font-bold text-gray-800 mb-3 border-b-2 border-gray-200 pb-1">1. Diagram Topologi Jaringan</h3>
            <div id="topologyNetwork"></div>
        </div>

        <div class="page-break"></div>

        <div class="mb-8">
            <h3 class="text-md font-bold text-gray-800 mb-3 border-b-2 border-gray-200 pb-1">2. Daftar Perangkat Switch</h3>
            <table class="w-full text-sm text-left border-collapse border border-gray-300">
                <thead class="bg-gray-100 text-gray-800">
                    <tr>
                        <th class="border border-gray-300 p-2 w-10 text-center">No</th>
                        <th class="border border-gray-300 p-2">Nama Switch</th>
                        <th class="border border-gray-300 p-2">IP Address</th>
                        <th class="border border-gray-300 p-2">Merek/Model</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse($switches as $index => $sw)
                    <tr>
                        <td class="border border-gray-300 p-2 text-center">{{ $index + 1 }}</td>
                        <td class="border border-gray-300 p-2 font-bold">{{ $sw->name }}</td>
                        <td class="border border-gray-300 p-2">{{ $sw->ip_address ?? '-' }}</td>
                        <td class="border border-gray-300 p-2">{{ $sw->brand_model ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="border border-gray-300 p-2 text-center text-gray-500">Tidak ada data switch.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mb-8">
            <h3 class="text-md font-bold text-gray-800 mb-3 border-b-2 border-gray-200 pb-1">3. Daftar Perangkat Komputer (PC)</h3>
            <table class="w-full text-sm text-left border-collapse border border-gray-300">
                <thead class="bg-gray-100 text-gray-800">
                    <tr>
                        <th class="border border-gray-300 p-2 w-10 text-center">No</th>
                        <th class="border border-gray-300 p-2">Nama PC</th>
                        <th class="border border-gray-300 p-2">IP Address</th>
                        <th class="border border-gray-300 p-2">Pengguna (User)</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse($pcs as $index => $pc)
                    <tr>
                        <td class="border border-gray-300 p-2 text-center">{{ $index + 1 }}</td>
                        <td class="border border-gray-300 p-2 font-bold">{{ $pc->name }}</td>
                        <td class="border border-gray-300 p-2">{{ $pc->ip_address ?? '-' }}</td>
                        <td class="border border-gray-300 p-2">{{ $pc->current_user ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="border border-gray-300 p-2 text-center text-gray-500">Tidak ada data PC.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mb-8">
            <h3 class="text-md font-bold text-gray-800 mb-3 border-b-2 border-gray-200 pb-1">4. Rincian Jalur Kabel</h3>
            <table class="w-full text-sm text-left border-collapse border border-gray-300">
                <thead class="bg-gray-100 text-gray-800">
                    <tr>
                        <th class="border border-gray-300 p-2 w-10 text-center">No</th>
                        <th class="border border-gray-300 p-2">Titik Awal</th>
                        <th class="border border-gray-300 p-2">Titik Tujuan</th>
                        <th class="border border-gray-300 p-2">Jenis Jalur</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @forelse($connectionDetails as $index => $conn)
                    <tr>
                        <td class="border border-gray-300 p-2 text-center">{{ $index + 1 }}</td>
                        <td class="border border-gray-300 p-2 font-bold">{{ $conn->from }}</td>
                        <td class="border border-gray-300 p-2 font-bold">{{ $conn->to }}</td>
                        <td class="border border-gray-300 p-2 font-bold" style="color: {{ $conn->color }}">{{ $conn->type }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="border border-gray-300 p-2 text-center text-gray-500">Tidak ada jalur kabel.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-16 text-right">
            <p class="text-sm mb-16 text-gray-800">Mengetahui,</p>
            <p class="font-bold underline text-sm text-gray-900">Tim IT Infrastructure</p>
        </div>

    </div>

    <script>
        const switches = @json($switches);
        const pcs = @json($pcs);
        const connections = @json($connections);

        const switchSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#3b82f6"><path d="M2 7v10h20V7H2zm18 8H4V9h16v6zm-2-4h-2v2h2v-2zm-4 0h-2v2h2v-2zm-4 0H8v2h2v-2z"/></svg>';
        const pcSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#14b8a6"><path d="M21 2H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h7v2H8v2h8v-2h-2v-2h7c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H3V4h18v12z"/></svg>';
        
        const switchIcon = "data:image/svg+xml;charset=utf-8," + encodeURIComponent(switchSvg);
        const pcIcon = "data:image/svg+xml;charset=utf-8," + encodeURIComponent(pcSvg);

        let nodesArray = [];
        
        switches.forEach(sw => {
            nodesArray.push({
                id: 'switch_' + sw.id, label: sw.name + '\n' + (sw.ip_address || ''),
                image: switchIcon, shape: 'image', level: 0,
                font: { face: 'sans-serif', size: 14, bold: true, color: '#1e293b' }
            });
        });

        pcs.forEach(pc => {
            nodesArray.push({
                id: 'pc_' + pc.id, label: pc.name + '\n' + (pc.current_user || ''),
                image: pcIcon, shape: 'image', level: 1,
                font: { face: 'sans-serif', size: 12, color: '#475569' }
            });
        });

        let nodes = new vis.DataSet(nodesArray);
        let edgesArray = [];
        connections.forEach(conn => {
            edgesArray.push({
                from: conn.from_type + '_' + conn.from_id, to: conn.to_type + '_' + conn.to_id,
                color: { color: conn.color, highlight: conn.color }, width: 3,
                smooth: { type: 'cubicBezier', forceDirection: 'vertical', roundness: 0.4 }
            });
        });
        
        let edges = new vis.DataSet(edgesArray);
        let container = document.getElementById('topologyNetwork');
        let data = { nodes: nodes, edges: edges };
        
        let options = {
            layout: { hierarchical: { direction: 'UD', levelSeparation: 150, nodeSpacing: 250, treeSpacing: 200 } },
            physics: false,
            interaction: { dragNodes: false, zoomView: false, dragView: false }
        };

        let network = new vis.Network(container, data, options);

        network.on("afterDrawing", function() {
            setTimeout(() => { window.print(); }, 800);
        });
    </script>
</body>
</html>