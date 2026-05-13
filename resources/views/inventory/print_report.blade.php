<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Out - Master Audit KBK</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <style id="page-style">
        @page { size: A4 portrait; margin: 15mm; }
    </style>
    <style>
        body { background-color: #f1f5f9; color: #1e293b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; }
        .a4-container { width: 100%; max-width: 297mm; background: white; margin: 20px auto; padding: 15mm 20mm; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); box-sizing: border-box; }

        @media print {
            body { background-color: white !important; }
            .a4-container { box-shadow: none !important; margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: none !important; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; break-before: page; }
            .avoid-break { page-break-inside: avoid; break-inside: avoid; }
            table { page-break-inside: auto; break-inside: auto; border-collapse: collapse; width: 100%; }
            tr { page-break-inside: avoid; break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; } tfoot { display: table-footer-group; }
            .map-container canvas { max-width: 100% !important; page-break-inside: avoid; }
        }

        .text-center { text-align: center; } .flex { display: flex; } .justify-between { justify-content: space-between; } .items-center { align-items: center; }
        .mt-2 { margin-top: 8px; } .mt-4 { margin-top: 16px; } .mb-4 { margin-bottom: 16px; } .mb-8 { margin-bottom: 32px; }
        
        h1 { font-size: 22px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        h3 { font-size: 16px; font-weight: bold; background-color: #1e293b; color: white; padding: 8px 12px; text-transform: uppercase; }
        h4 { font-size: 14px; font-weight: 900; text-transform: uppercase; margin: 0; }
        h5 { font-size: 12px; font-weight: bold; color: #1e293b; border-bottom: 2px solid #cbd5e1; padding-bottom: 4px; margin-bottom: 10px; text-transform: uppercase; }
        
        .text-xs { font-size: 10px; } .text-sm { font-size: 12px; } .text-slate-500 { color: #64748b; } .font-bold { font-weight: bold; }
        
        .summary-box { border: 1px solid #cbd5e1; background-color: #f8fafc; display: flex; gap: 40px; padding: 15px; }
        .summary-item p:first-child { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #64748b; margin: 0 0 5px 0; }
        .summary-item p:last-child { font-size: 20px; font-weight: 900; margin: 0; }
        
        .building-header { background-color: #e2e8f0; border-left: 5px solid #1e293b; padding: 10px 15px; margin-bottom: 15px; }
        
        .map-container { border: 2px dashed #cbd5e1; padding: 5px; border-radius: 4px; margin-bottom: 15px; text-align: center; background: #f8fafc; overflow: hidden; }
        .map-caption { font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-top: 5px; }

        th, td { border: 1px solid #94a3b8; padding: 8px 6px; text-align: left; font-size: 10px; }
        th { background-color: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        .table-striped tbody tr:nth-child(even) { background-color: #f8fafc; }

        .action-buttons { position: fixed; top: 20px; right: 20px; display: flex; gap: 10px; z-index: 100; }
        .btn-print, .btn-back, .btn-toggle { padding: 10px 15px; border-radius: 8px; font-weight: bold; text-decoration: none; font-size: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); cursor: pointer; border: none; transition: 0.2s; }
        .btn-print { background: #2563eb; color: white; }
        .btn-toggle { background: #0f172a; color: white; border: 1px solid #334155; }
        .btn-back { background: #475569; color: white; }
        .btn-print:hover { background: #1d4ed8; } .btn-toggle:hover { background: #1e293b; } .btn-back:hover { background: #334155; }
    </style>
</head>
<body>

    @php
        $isDataOnly = request('type') == 'data';
        $totalFloors = $buildings->sum(function($b) { return $b->floors->count(); });
        $totalAssets = $buildings->sum(function($b) { return $b->floors->sum(function($f) { return $f->assets->count(); }); });

        foreach($buildings as $b) {
            foreach($b->floors as $f) {
                $counter = 1;
                foreach($f->assets as $a) {
                    if($a->pos_x !== null) { $a->mapNumber = $counter++; }
                }
            }
        }
    @endphp

    <div class="action-buttons no-print">
        <button onclick="window.close()" class="btn-back">Tutup Halaman</button>
        <button onclick="setOrientation('portrait')" class="btn-toggle">📄 Portrait</button>
        <button onclick="setOrientation('landscape')" class="btn-toggle">🖨️ Landscape</button>
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Laporan</button>
    </div>

    <div class="a4-container">
        
        <div class="avoid-break" style="border-bottom: 3px solid #1e293b; padding-bottom: 15px; margin-bottom: 25px; display: flex; flex-direction: column; align-items: center; text-align: center;">
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
                <div class="summary-box mt-4 mb-8 avoid-break">
                    <div class="summary-item"><p>Total Gedung</p><p>{{ $buildings->count() }} Gedung</p></div>
                    <div class="summary-item"><p>Total Aset Terdaftar</p><p>{{ $totalAssets }} Unit</p></div>
                </div>

                @foreach($categories as $cat)
                    @php
                        $catAssets = collect();
                        foreach($buildings as $b) {
                            foreach($b->floors as $f) {
                                foreach($f->assets->where('asset_category_id', $cat->id) as $a) {
                                    $a->b_name = $b->name; $a->f_name = $f->name;
                                    $catAssets->push($a);
                                }
                            }
                        }
                    $showIp = $cat->has_ip;
                    @endphp

                    @if($catAssets->count() > 0)
                        <div class="mb-8">
                            <h5 style="background: #1e293b; color: white; padding: 6px 10px; margin: 0; border: none;" class="avoid-break">
                                📦 KATEGORI: {{ $cat->name }} ({{ $cat->prefix }})
                            </h5>
                            <table class="table-striped mt-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 25px;">No</th>
                                        <th>Lokasi (Gedung-Lantai)</th>
                                        <th>Kode Aset</th>
                                        <th>Hostname / Nama</th>
                                        @if($showIp) <th>IP Address</th> @endif
                                        @foreach($cat->fields as $field) <th>{{ $field->field_name }}</th> @endforeach
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Tahun</th>
                                        <th>Keterangan</th> </tr>
                                </thead>
                                <tbody>
                                    @foreach($catAssets as $idx => $asset)
                                        <tr>
                                            <td class="text-center">{{ $idx + 1 }}</td>
                                            <td><strong>{{ $asset->b_name }}</strong><br><span class="text-xs text-slate-500">{{ $asset->f_name }}</span></td>
                                            <td class="font-mono text-xs">{{ $asset->asset_code }}</td>
                                            <td class="font-bold">{{ $asset->name }}</td>
                                            @if($showIp) <td class="font-mono">{{ $asset->ipAddress->ip_address ?? '-' }}</td> @endif
                                            @foreach($cat->fields as $field)
                                                @php $valObj = $asset->values->firstWhere('category_field_id', $field->id); @endphp
                                                <td>{{ $valObj ? $valObj->value : '-' }}</td>
                                            @endforeach
                                            <td class="text-center font-bold">{{ strtoupper($asset->status ?? '-') }}</td>
                                            <td class="text-center">{{ $asset->installation_year ?? '-' }}</td>
                                            <td>{{ $asset->description ?? '-' }}</td> </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endforeach
            </div>

        @else
            <div class="mb-8">
                <h3 class="avoid-break">1. Peta Kawasan (Global Map) & Rekapitulasi</h3>
                <div class="summary-box mt-4 avoid-break">
                    <div class="summary-item"><p>Total Gedung</p><p>{{ $buildings->count() }} Gedung</p></div>
                    <div class="summary-item"><p>Total Lantai</p><p>{{ $totalFloors }} Lantai</p></div>
                </div>

                <div class="map-container mt-4 avoid-break">
                    <canvas id="globalPrintCanvas" width="640" height="360"></canvas>
                    <div class="map-caption">Citra Topologi Kawasan PT. KBK</div>
                </div>
            </div>

            <h3 class="mb-4 avoid-break" style="margin-top: 30px;">2. Rincian Topologi Gedung & Lantai</h3>

            @foreach($buildings as $index => $building)
                <div class="mb-8"> 
                    <div class="building-header flex justify-between items-center avoid-break">
                        <h4>🏢 Gedung: {{ $building->name }}</h4>
                        <span class="text-sm font-bold text-slate-500">{{ $building->floors->count() }} Lantai</span>
                    </div>

                    @foreach($building->floors as $floor)
                        <div style="margin-left: 15px; margin-bottom: 40px;">
                            <h4 style="color: #1d4ed8; margin-bottom: 10px;" class="avoid-break">📍 LANTAI: {{ $floor->name }}</h4>
                            
                            @if($floor->image_path)
                                <div class="map-container avoid-break">
                                    <canvas id="floorCanvas_{{ $floor->id }}" width="640" height="360"></canvas>
                                    <div class="map-caption">Blueprint Denah: {{ $floor->name }} (Angka merujuk pada Nomor Tabel)</div>
                                </div>
                            @endif

                            @php
                                $floorAssetsByCat = $floor->assets->whereNotNull('pos_x')->groupBy('asset_category_id');
                            @endphp

                            @if($floorAssetsByCat->count() > 0)
                                @foreach($floorAssetsByCat as $catId => $assetsInCat)
                                    @php
                                        $cat = $categories->firstWhere('id', $catId);
                                        $showIp = $cat->has_ip;
                                    @endphp
                                    
                                    <h5 class="mt-4 avoid-break">{{ $cat->name ?? 'Lainnya' }}</h5>
                                    <table class="table-striped mt-2">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 45px;">No Peta</th>
                                                <th>Kode Aset</th>
                                                <th>Hostname / Nama</th>
                                                @if($showIp) <th>IP Address</th> @endif
                                                @if($cat)
                                                    @foreach($cat->fields as $field) <th>{{ $field->field_name }}</th> @endforeach
                                                @endif
                                                <th class="text-center">Status</th>
                                                <th>Keterangan</th> </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($assetsInCat as $asset)
                                                <tr>
                                                    <td class="text-center font-black text-blue-600 text-sm">{{ $asset->mapNumber ?? '-' }}</td>
                                                    <td class="font-mono text-xs">{{ $asset->asset_code }}</td>
                                                    <td class="font-bold">{{ $asset->name }}</td>
                                                    @if($showIp) <td class="font-mono">{{ $asset->ipAddress->ip_address ?? '-' }}</td> @endif
                                                    @if($cat)
                                                        @foreach($cat->fields as $field)
                                                            @php $valObj = $asset->values->firstWhere('category_field_id', $field->id); @endphp
                                                            <td>{{ $valObj ? $valObj->value : '-' }}</td>
                                                        @endforeach
                                                    @endif
                                                    <td class="text-center font-bold">{{ strtoupper($asset->status ?? '-') }}</td>
                                                    <td>{{ $asset->description ?? '-' }}</td> </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif

        <div class="avoid-break" style="text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #cbd5e1; font-size: 10px; font-weight: bold; color: #94a3b8; text-transform: uppercase;">
            *** Akhir dari Laporan Audit ***
        </div>
    </div>

    <script>
        function setOrientation(type) {
            document.getElementById('page-style').innerHTML = `@page { size: A4 ${type}; margin: 15mm; }`;
            alert('Format kertas berhasil diubah ke ' + type.toUpperCase() + '. Silakan klik tombol Cetak.');
        }
    </script>

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
                        let devices = f.assets || [];
                        
                        devices.forEach(dev => {
                            if(dev.pos_x === null) return;
                            let prefix = dev.category ? (dev.category.prefix || '').toUpperCase() : '';
                            let iconPath = prefix.includes('PC') ? '/img/pc.png' : '/img/switch.png';

                            fabric.Image.fromURL(iconPath, function(iconImg) {
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

                        let devices = f.assets || [];
                        
                        devices.forEach(dev => {
                            if(dev.pos_x === null) return;
                            let prefix = dev.category ? (dev.category.prefix || '').toUpperCase() : '';
                            let iconPath = prefix.includes('PC') ? '/img/pc.png' : '/img/switch.png';
                            let iconColor = prefix.includes('PC') ? '#0d9488' : '#2563eb';
                            
                            let currentNumber = dev.mapNumber; 

                            fabric.Image.fromURL(iconPath, function(iconImg) {
                                iconImg.scaleToWidth(25); iconImg.set({ originX: 'center', originY: 'center' });
                                
                                let badgeRect = new fabric.Rect({ width: 16, height: 16, fill: iconColor, rx: 8, ry: 8, originX: 'center', originY: 'center', top: 18 });
                                let badgeText = new fabric.Text(currentNumber.toString(), { fontSize: 10, fill: '#ffffff', fontWeight: 'bold', originX: 'center', originY: 'center', top: 18 });

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