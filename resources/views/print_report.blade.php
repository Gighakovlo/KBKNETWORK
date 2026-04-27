<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Out - Master Audit KBK</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <style>
        @page { size: A4; margin: 15mm; }
        body { background-color: #f1f5f9; color: #1e293b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; }
        
        .a4-container { width: 210mm; min-height: 297mm; background: white; margin: 20px auto; padding: 15mm 20mm; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); box-sizing: border-box; }

        @media print {
            body { background-color: white !important; }
            .a4-container { box-shadow: none !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            .avoid-break { page-break-inside: avoid; }
        }

        .text-center { text-align: center; } .flex { display: flex; } .justify-between { justify-content: space-between; } .items-center { align-items: center; }
        .mt-2 { margin-top: 8px; } .mt-4 { margin-top: 16px; } .mb-4 { margin-bottom: 16px; } .mb-8 { margin-bottom: 32px; }
        
        h1 { font-size: 22px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        h2 { font-size: 14px; font-weight: bold; color: #475569; margin-top: 0; }
        h3 { font-size: 16px; font-weight: bold; background-color: #1e293b; color: white; padding: 8px 12px; text-transform: uppercase; }
        h4 { font-size: 14px; font-weight: 900; text-transform: uppercase; margin: 0; }
        h5 { font-size: 14px; font-weight: bold; color: #1d4ed8; border-bottom: 2px solid #bfdbfe; padding-bottom: 4px; margin-bottom: 10px; }
        
        .text-xs { font-size: 10px; } .text-sm { font-size: 12px; } .text-slate-500 { color: #64748b; } .font-bold { font-weight: bold; }
        
        .summary-box { border: 1px solid #cbd5e1; background-color: #f8fafc; display: flex; gap: 40px; padding: 15px; }
        .summary-item p:first-child { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #64748b; margin: 0 0 5px 0; }
        .summary-item p:last-child { font-size: 20px; font-weight: 900; margin: 0; }
        
        .building-header { background-color: #e2e8f0; border-left: 5px solid #1e293b; padding: 10px 15px; margin-bottom: 15px; }
        
        .map-container { border: 2px dashed #cbd5e1; padding: 5px; border-radius: 4px; margin-bottom: 15px; text-align: center; background: #f8fafc; overflow: hidden; }
        .map-container canvas { max-width: 100%; height: auto !important; }
        .map-caption { font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-top: 5px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th, td { border: 1px solid #94a3b8; padding: 8px 6px; text-align: left; font-size: 10px; }
        th { background-color: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        .table-striped tbody tr:nth-child(even) { background-color: #f8fafc; }

        .action-buttons { position: fixed; top: 20px; right: 20px; display: flex; gap: 15px; z-index: 100; }
        .btn-print, .btn-back { padding: 10px 20px; border-radius: 8px; font-weight: bold; text-decoration: none; font-size: 14px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); cursor: pointer; border: none; transition: 0.2s; }
        .btn-print { background: #2563eb; color: white; }
        .btn-back { background: #475569; color: white; }
        .btn-print:hover { background: #1d4ed8; }
        .btn-back:hover { background: #334155; }
    </style>
</head>
<body>

    @php
        $isDataOnly = request('type') == 'data';
    @endphp

    <div class="action-buttons no-print">
        <a href="/management" class="btn-back">Kembali</a>
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Laporan</button>
    </div>

    <div class="a4-container">
        
        <div style="border-bottom: 3px solid #1e293b; padding-bottom: 15px; margin-bottom: 25px; display: flex; flex-direction: column; align-items: center; text-align: center;">
            
            <img src="{{ asset('img/KBK LOGO.png') }}" alt="Logo KBK" style="max-height: 55px; margin-bottom: 15px;">
            
            <h1>Master Audit Infrastruktur Jaringan</h1>
            <p class="text-xs text-slate-500 font-bold" style="margin: 0; text-transform: uppercase; letter-spacing: 1px;">
                Dokumen Klasifikasi: {{ $isDataOnly ? 'Inventory & Data Log' : 'Visual & Topologi Blueprint' }}
            </p>
            <p class="text-xs text-slate-500 mt-2">Dicetak pada: {{ \Carbon\Carbon::now()->format('d M Y - H:i') }}</p>
        </div>

        @if($isDataOnly)
            <div class="mb-8">
                <h3>Rekapitulasi Total Inventory Perangkat</h3>
                <div class="summary-box mt-4 mb-4">
                    <div class="summary-item"><p>Total Gedung</p><p>{{ $buildings->count() }} Gedung</p></div>
                    <div class="summary-item"><p>Total Switch / Router</p><p>{{ $buildings->flatMap->floors->flatMap->switchNodes->count() }} Unit</p></div>
                    <div class="summary-item"><p>Total PC / Client</p><p>{{ $buildings->flatMap->floors->flatMap->pcNodes->count() }} Unit</p></div>
                </div>

                <table class="table-striped">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 25px;">No</th>
                            <th>Lokasi</th>
                            <th style="width: 70px;">Jenis</th>
                            <th>Hostname</th>
                            <th>Alamat IP</th>
                            <th>Spesifikasi / User</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Tahun</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach($buildings as $building)
                            @foreach($building->floors as $floor)
                                
                                @foreach($floor->switchNodes as $switch)
                                    <tr>
                                        <td class="text-center">{{ $no++ }}</td>
                                        <td><strong>{{ $building->name }}</strong><br><span class="text-xs text-slate-500">{{ $floor->name }}</span></td>
                                        <td class="font-bold" style="color: #2563eb;">🖧 Switch</td>
                                        <td class="font-bold">{{ $switch->name }}</td>
                                        <td class="font-mono">{{ $switch->ip_address ?? 'Belum ada' }}</td>
                                        <td>{{ $switch->brand_model ?? '-' }}</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center font-bold">{{ $switch->installation_year ?? '-' }}</td>
                                    </tr>
                                @endforeach

                                @foreach($floor->pcNodes as $pc)
                                    <tr>
                                        <td class="text-center">{{ $no++ }}</td>
                                        <td><strong>{{ $building->name }}</strong><br><span class="text-xs text-slate-500">{{ $floor->name }}</span></td>
                                        <td class="font-bold" style="color: #0d9488;">💻 PC</td>
                                        <td class="font-bold">{{ $pc->name }}</td>
                                        <td class="font-mono">{{ $pc->ip_address ?? 'Belum ada' }}</td>
                                        <td>{{ $pc->current_user ?? '-' }}</td>
                                        <td class="text-center font-bold">{{ strtoupper($pc->status ?? '-') }}</td>
                                        <td class="text-center font-bold">{{ $pc->installation_year ?? '-' }}</td>
                                    </tr>
                                @endforeach

                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

        @else
            <div class="mb-8">
                <h3>1. Peta Kawasan (Global Map) & Rekapitulasi</h3>
                <div class="summary-box mt-4">
                    <div class="summary-item"><p>Total Gedung</p><p>{{ $buildings->count() }} Gedung</p></div>
                    <div class="summary-item"><p>Total Lantai</p><p>{{ $buildings->sum(function($b) { return $b->floors->count(); }) }} Lantai</p></div>
                </div>

                <div class="map-container mt-4">
                    <canvas id="globalPrintCanvas" width="640" height="360"></canvas>
                    <div class="map-caption">Citra Topologi Kawasan PT. KBK</div>
                </div>
            </div>

            <div class="page-break"></div>
            <h3 class="mb-4">2. Rincian Topologi Gedung & Lantai</h3>

            @foreach($buildings as $index => $building)
                <div class="mb-8 avoid-break"> 
                    <div class="building-header flex justify-between items-center">
                        <h4>🏢 Gedung: {{ $building->name }}</h4>
                        <span class="text-sm font-bold text-slate-500">{{ $building->floors->count() }} Lantai</span>
                    </div>

                    @foreach($building->floors as $floor)
                        <div style="margin-left: 20px; margin-bottom: 30px;" class="avoid-break">
                            <h5>📍 {{ $floor->name }}</h5>
                            
                            @if($floor->image_path)
                                <div class="map-container">
                                    <canvas id="floorCanvas_{{ $floor->id }}" width="640" height="360"></canvas>
                                    <div class="map-caption">Blueprint Denah: {{ $floor->name }} (Angka merujuk pada Nomor Tabel)</div>
                                </div>
                            @endif

                            @php $totalDevices = $floor->switchNodes->count() + $floor->pcNodes->count(); @endphp

                            @if($totalDevices > 0)
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 25px;">No</th>
                                            <th style="width: 70px;">Jenis</th>
                                            <th>Hostname</th>
                                            <th>IP Address</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Tahun</th>
                                            <th class="text-center" style="width: 90px;">Kordinat (X,Y)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $no = 1; @endphp
                                        @foreach($floor->switchNodes as $switch)
                                            <tr>
                                                <td class="text-center font-black text-blue-600">{{ $no++ }}</td>
                                                <td class="font-bold" style="color: #2563eb;">🖧 Switch</td>
                                                <td class="font-bold">{{ $switch->name }}</td>
                                                <td class="font-mono">{{ $switch->ip_address ?? '-' }}</td>
                                                <td class="text-center">-</td>
                                                <td class="text-center font-bold">{{ $switch->installation_year ?? '-' }}</td>
                                                <td class="text-center font-mono">{{ $switch->pos_x }}, {{ $switch->pos_y }}</td>
                                            </tr>
                                        @endforeach
                                        @foreach($floor->pcNodes as $pc)
                                            <tr>
                                                <td class="text-center font-black text-teal-600">{{ $no++ }}</td>
                                                <td class="font-bold" style="color: #0d9488;">💻 PC</td>
                                                <td class="font-bold">{{ $pc->name }}</td>
                                                <td class="font-mono">{{ $pc->ip_address ?? '-' }}</td>
                                                <td class="text-center font-bold">{{ strtoupper($pc->status ?? '-') }}</td>
                                                <td class="text-center font-bold">{{ $pc->installation_year ?? '-' }}</td>
                                                <td class="text-center font-mono">{{ $pc->pos_x }}, {{ $pc->pos_y }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif

        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #cbd5e1; font-size: 10px; font-weight: bold; color: #94a3b8; text-transform: uppercase;">
            *** Akhir dari Laporan Audit ***
        </div>
    </div>

    @if(!$isDataOnly)
    <script>
        const buildings = @json($buildings);
        
        // 1. RENDER GLOBAL MAP
        const gCanvas = new fabric.StaticCanvas('globalPrintCanvas');
        fabric.Image.fromURL('/img/denah-master.png', function(img) {
            let gScaleX = 640 / img.width; let gScaleY = 360 / img.height; let gScale = Math.min(gScaleX, gScaleY); 
            let gPanX = (640 - (img.width * gScale)) / 2; let gPanY = (360 - (img.height * gScale)) / 2;

            img.set({ scaleX: gScale, scaleY: gScale, left: gPanX, top: gPanY });
            gCanvas.setBackgroundImage(img, gCanvas.renderAll.bind(gCanvas));

            buildings.forEach(b => {
                if(b.polygon_points) {
                    let points = JSON.parse(b.polygon_points);
                    let mappedPoints = points.map(p => ({ x: (p.x * gScale) + gPanX, y: (p.y * gScale) + gPanY }));
                    let poly = new fabric.Polygon(mappedPoints, { fill: 'rgba(59, 130, 246, 0.1)', stroke: 'rgba(59, 130, 246, 0.6)', strokeWidth: 1.5 });
                    gCanvas.add(poly);
                }
                b.floors.forEach((f, idx) => {
                    if(!f.image_path || !f.box_width) return;
                    let fB_L = (parseFloat(f.box_left) * gScale) + gPanX; let fB_T = (parseFloat(f.box_top) * gScale) + gPanY;
                    let fB_W = parseFloat(f.box_width) * gScale; let fB_H = parseFloat(f.box_height) * gScale;

                    if (idx === 0) gCanvas.add(new fabric.Rect({ left: fB_L, top: fB_T, width: fB_W, height: fB_H, fill: 'rgba(255,255,255,0.5)', stroke: '#3b82f6', strokeWidth: 1 }));

                    fabric.Image.fromURL(f.image_path, function(floorImg) {
                        let scaleX = fB_W / floorImg.width; let scaleY = fB_H / floorImg.height;
                        let devices = [...(f.switch_nodes || f.switchNodes || []).map(d => ({...d, type: 'switch'})), ...(f.pc_nodes || f.pcNodes || []).map(d => ({...d, type: 'pc'}))];
                        
                        devices.forEach(dev => {
                            fabric.Image.fromURL(dev.type === 'switch' ? '/img/switch.png' : '/img/pc.png', function(iconImg) {
                                iconImg.scaleToWidth(14); 
                                iconImg.set({ left: fB_L + (parseFloat(dev.pos_x) * scaleX), top: fB_T + (parseFloat(dev.pos_y) * scaleY), originX: 'center', originY: 'center' });
                                gCanvas.add(iconImg); gCanvas.renderAll();
                            });
                        });
                    });
                });
            });
        });

        // 2. RENDER DENAH PER LANTAI
        buildings.forEach(b => {
            b.floors.forEach(f => {
                if(f.image_path) {
                    let fCanvas = new fabric.StaticCanvas(`floorCanvas_${f.id}`);
                    fabric.Image.fromURL(f.image_path, function(img) {
                        let finalScale = Math.min(640 / img.width, 360 / img.height);
                        let panX = (640 - (img.width * finalScale)) / 2; let panY = (360 - (img.height * finalScale)) / 2;

                        img.set({ scaleX: finalScale, scaleY: finalScale, left: panX, top: panY });
                        fCanvas.setBackgroundImage(img, fCanvas.renderAll.bind(fCanvas));

                        let counter = 1;
                        let devices = [...(f.switch_nodes || f.switchNodes || []).map(d => ({...d, type: 'switch', tableNo: counter++})), ...(f.pc_nodes || f.pcNodes || []).map(d => ({...d, type: 'pc', tableNo: counter++}))];
                        
                        devices.forEach(dev => {
                            fabric.Image.fromURL(dev.type === 'switch' ? '/img/switch.png' : '/img/pc.png', function(iconImg) {
                                iconImg.scaleToWidth(25); iconImg.set({ originX: 'center', originY: 'center' });
                                
                                let badgeRect = new fabric.Rect({ width: 16, height: 16, fill: dev.type === 'switch' ? '#2563eb' : '#0d9488', rx: 8, ry: 8, originX: 'center', originY: 'center', top: 18 });
                                let badgeText = new fabric.Text(dev.tableNo.toString(), { fontSize: 10, fill: '#ffffff', fontWeight: 'bold', originX: 'center', originY: 'center', top: 18 });

                                fCanvas.add(new fabric.Group([iconImg, badgeRect, badgeText], { left: panX + (parseFloat(dev.pos_x) * finalScale), top: panY + (parseFloat(dev.pos_y) * finalScale), originX: 'center', originY: 'center' }));
                                fCanvas.renderAll();
                            });
                        });
                    });
                }
            });
        });
    </script>
    @endif
</body>
</html>