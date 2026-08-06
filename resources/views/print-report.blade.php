<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi Jaringan - {{ $floor->building->name ?? 'Gedung' }} ({{ $floor->name }})</title>
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
            
            /* Aturan Page Break yang Lebih Cerdas */
            tr, .category-header, .map-box { page-break-inside: avoid; break-inside: avoid; }
            h1, h2, h3, h5 { page-break-after: avoid; break-after: avoid; }
            
            /* Pastikan kanvas tidak meluber keluar kertas */
            canvas { max-width: 100% !important; height: auto !important; }
            
            /* Kurangi margin raksasa saat diprint */
            .print-mb-small { margin-bottom: 15px !important; }
        }

        .text-center { text-align: center; } .flex { display: flex; } .justify-between { justify-content: space-between; } .items-center { align-items: center; }
        .mt-2 { margin-top: 8px; } .mt-4 { margin-top: 16px; } .mb-4 { margin-bottom: 16px; } .mb-8 { margin-bottom: 32px; }
        
        h1 { font-size: 22px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        h3 { font-size: 16px; font-weight: bold; background-color: #1e293b; color: white; padding: 8px 12px; text-transform: uppercase; margin-bottom: 10px; }
        h5 { font-size: 12px; font-weight: bold; color: #1e293b; border-bottom: 2px solid #cbd5e1; padding-bottom: 4px; margin-bottom: 10px; text-transform: uppercase; }
        
        .text-xs { font-size: 10px; } .text-sm { font-size: 12px; } .text-slate-500 { color: #64748b; } .font-bold { font-weight: bold; }
        
        .map-container { border: 2px dashed #cbd5e1; padding: 5px; border-radius: 4px; text-align: center; background: #f8fafc; overflow: hidden; }
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
        $floorAssetsByCat = $floor->assets->whereNotNull('pos_x')->groupBy('asset_category_id');
        $counter = 1;
        foreach($floor->assets->whereNotNull('pos_x') as $a) {
            $a->mapNumber = $counter++;
        }
    @endphp

    <div class="action-buttons no-print">
        <button onclick="window.close()" class="btn-back">&larr; Tutup Tab & Kembali</button>
        <button onclick="setOrientation('portrait')" class="btn-toggle">📄 Portrait</button>
        <button onclick="setOrientation('landscape')" class="btn-toggle">🖨️ Landscape</button>
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Laporan PDF</button>
    </div>

    <div class="a4-container">
        
        <div class="print-mb-small" style="border-bottom: 3px solid #1e293b; padding-bottom: 15px; margin-bottom: 25px; display: flex; align-items: center; gap: 20px;">
            <div style="width: 60px; height: 60px; background: #1e40af; color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 24px; border-radius: 10px;">KBK</div>
            <div style="flex-grow: 1;">
                <h1 style="font-size: 20px;">PT. Krakatau Baja Konstruksi</h1>
                <p class="text-sm font-bold text-slate-500" style="margin: 0;">Departemen IT Infrastructure & Network</p>
            </div>
            <div style="text-align: right;">
                <p class="font-bold" style="margin: 0; font-size: 14px;">LAPORAN PEMETAAN LANTAI</p>
                <p class="text-xs text-slate-500" style="margin: 0;">Dicetak: {{ \Carbon\Carbon::now()->format('d M Y') }}</p>
            </div>
        </div>

        <div class="text-center mb-6 print-mb-small">
            <h2 style="font-size: 18px; font-weight: 900; text-transform: uppercase; margin: 0;">
                Dokumentasi Jaringan: {{ $floor->building->name ?? 'Gedung' }} - {{ $floor->name }}
            </h2>
        </div>

        @if($floor->image_path)
            <div class="mb-8 map-box print-mb-small">
                <h3>1. Peta Topologi (Visual Blueprint)</h3>
                <div class="map-container mt-2">
                    <canvas id="floorCanvas" width="720" height="405"></canvas>
                    <div class="map-caption">Angka pada peta merujuk pada "No Peta" di tabel daftar aset.</div>
                </div>
            </div>
        @endif

        <div class="mb-4">
            <h3 class="mb-4">2. Rincian Aset Berdasarkan Kategori</h3>

            @if($floorAssetsByCat->count() > 0)
                @foreach($floorAssetsByCat as $catId => $assetsInCat)
                    @php
                        $cat = $assetsInCat->first()->category;
                        $showIp = $cat ? $cat->has_ip : false;
                    @endphp
                    
                    <div class="mb-6">
                        <h5 class="category-header" style="background: #e2e8f0; color: #0f172a; padding: 6px 10px; margin: 0; border-left: 4px solid #1e40af;">
                            📦 {{ $cat->name ?? 'Kategori Lainnya' }} ({{ $cat->prefix ?? 'AST' }})
                        </h5>
                        <table class="table-striped mt-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 40px;">No Peta</th>
                                    <th style="width: 80px;">Kode Aset</th>
                                    <th>Hostname / Nama</th>
                                    @if($showIp) <th>IP Address</th> @endif
                                    @if($cat)
                                        @foreach($cat->fields as $field) <th>{{ $field->field_name }}</th> @endforeach
                                    @endif
                                    <th>Pengguna (User)</th>
                                    <th class="text-center">Status</th>
                                </tr>
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
                                        <td>{{ $asset->current_user ?? '-' }}</td>
                                        <td class="text-center font-bold">{{ strtoupper($asset->status ?? '-') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @else
                <div class="text-center p-6 border border-dashed border-slate-400 mt-4">
                    <p class="text-sm font-bold text-slate-500">Belum ada aset yang dipetakan di lantai ini.</p>
                </div>
            @endif
        </div>

        <div class="mt-8 text-right category-header" style="padding-top: 30px;">
            <p class="text-sm mb-16 text-slate-800">Mengetahui,</p>
            <p class="font-bold underline text-sm text-slate-900">Tim IT Infrastructure</p>
        </div>

    </div>

    <script>
        function setOrientation(type) {
            // Mengubah orientasi CSS print
            document.getElementById('page-style').innerHTML = `@page { size: A4 ${type}; margin: 15mm; }`;
            
            // Memunculkan notifikasi pop-up
            alert('Format kertas berhasil diubah ke mode ' + type.toUpperCase() + '. Silakan klik tombol Cetak Laporan PDF!');
        }

        const imageUrl = "{{ $floor->image_path }}";
        const assetsData = @json($floor->assets->whereNotNull('pos_x')->values());

        if(imageUrl) {
            const fCanvas = new fabric.StaticCanvas('floorCanvas');
            fabric.Image.fromURL(imageUrl, function(img) {
                // Dimensi Canvas Diperbarui menjadi 720 x 405 (Aspect Ratio 16:9 yang aman untuk A4)
                let finalScale = Math.min(720 / img.width, 405 / img.height);
                let panX = (720 - (img.width * finalScale)) / 2; 
                let panY = (405 - (img.height * finalScale)) / 2;

                img.set({ scaleX: finalScale, scaleY: finalScale, left: panX, top: panY });
                fCanvas.setBackgroundImage(img, fCanvas.renderAll.bind(fCanvas));

                assetsData.forEach(dev => {
                    let color = dev.category && dev.category.color ? dev.category.color : '#3b82f6';
                    let iconPath = dev.category && dev.category.icon_path ? '/' + dev.category.icon_path : null;
                    let currentNumber = dev.mapNumber; 

                    let leftPos = panX + (parseFloat(dev.pos_x) * finalScale);
                    let topPos = panY + (parseFloat(dev.pos_y) * finalScale);

                    if(iconPath) {
                        fabric.Image.fromURL(iconPath, function(iconImg) {
                            iconImg.scaleToWidth(25); iconImg.set({ originX: 'center', originY: 'center' });
                            
                            let badgeRect = new fabric.Rect({ width: 16, height: 16, fill: color, rx: 8, ry: 8, originX: 'center', originY: 'center', top: 18 });
                            let badgeText = new fabric.Text(currentNumber.toString(), { fontSize: 10, fill: '#ffffff', fontWeight: 'bold', originX: 'center', originY: 'center', top: 18 });

                            fCanvas.add(new fabric.Group([iconImg, badgeRect, badgeText], { left: leftPos, top: topPos, originX: 'center', originY: 'center' }));
                            fCanvas.renderAll();
                        });
                    } else {
                        let rect = new fabric.Rect({ width: 25, height: 25, fill: color, rx: 4, ry: 4, originX: 'center', originY: 'center' });
                        let badgeText = new fabric.Text(currentNumber.toString(), { fontSize: 12, fill: '#ffffff', fontWeight: 'bold', originX: 'center', originY: 'center' });
                        fCanvas.add(new fabric.Group([rect, badgeText], { left: leftPos, top: topPos, originX: 'center', originY: 'center' }));
                        fCanvas.renderAll();
                    }
                });
            });
        }
    </script>
</body>
</html>