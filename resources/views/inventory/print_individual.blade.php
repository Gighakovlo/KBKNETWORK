<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aset - {{ $asset->asset_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style id="page-style">
        @page { size: A4 portrait; margin: 15mm; }
    </style>
    <style>
        body { background-color: #f8fafc; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; }
        .a4-container { max-width: 210mm; background: white; margin: 20px auto; padding: 15mm; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        
        @media print {
            body { background-color: white !important; }
            .a4-container { box-shadow: none !important; margin: 0 !important; padding: 0 !important; max-width: 100% !important; }
            .no-print { display: none !important; }
            
            /* Aturan Page Break yang Lebih Fleksibel */
            .header-box, .section-title, .grid-info, .info-card { page-break-inside: avoid; break-inside: avoid; }
            h1, h3 { page-break-after: avoid; break-after: avoid; }
            
            /* Timeline item diatur agar tidak terpotong di tengah-tengah tulisan */
            .timeline-item { page-break-inside: avoid; break-inside: avoid; }
        }

        .header-box { border-bottom: 4px solid #1e40af; padding-bottom: 15px; margin-bottom: 25px; display: flex; align-items: center; gap: 20px; }
        .section-title { font-size: 14px; font-weight: 900; background: #1e293b; color: white; padding: 6px 12px; text-transform: uppercase; margin-bottom: 15px; }
        
        .grid-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 25px; }
        .info-card { border: 1px solid #e2e8f0; padding: 10px; background: #f8fafc; border-radius: 6px; }
        .info-label { font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase; }
        .info-value { font-size: 14px; font-weight: bold; color: #0f172a; margin-top: 3px; }

        .timeline { border-left: 2px solid #cbd5e1; margin-left: 10px; padding-left: 20px; margin-top: 10px; }
        .timeline-item { position: relative; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px dashed #e2e8f0; }
        .timeline-item:last-child { border-bottom: none; }
        .timeline-dot { position: absolute; left: -27px; top: 0; width: 12px; height: 12px; border-radius: 50%; background: #3b82f6; border: 2px solid white; }
        
        .action-buttons { position: fixed; top: 20px; right: 20px; display: flex; gap: 10px; z-index: 100; }
        .btn-print, .btn-back { padding: 10px 15px; border-radius: 8px; font-weight: bold; cursor: pointer; border: none; color: white; font-size: 12px; }
        .btn-print { background: #2563eb; } .btn-back { background: #475569; }
    </style>
</head>
<body>

    <div class="action-buttons no-print">
        <button onclick="window.close()" class="btn-back">Tutup</button>
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Dokumen</button>
    </div>

    <div class="a4-container">
        
        <div class="header-box">
            <div style="width: 60px; height: 60px; background: #1e40af; color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 24px; border-radius: 10px;">KBK</div>
            <div style="flex-grow: 1;">
                <h1 style="font-size: 22px; font-weight: 900; margin: 0; text-transform: uppercase;">Laporan Riwayat Aset</h1>
                <p style="font-size: 12px; font-weight: bold; color: #64748b; margin: 0;">PT. Krakatau Baja Konstruksi - IT Infrastructure</p>
            </div>
            <div style="text-align: right;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data={{ $asset->asset_code }}" alt="QR Code">
                <p style="font-size: 10px; margin-top: 5px; font-weight: bold; font-family: monospace;">{{ $asset->asset_code }}</p>
            </div>
        </div>

        <h3 class="section-title">1. Informasi Dasar Perangkat</h3>
        <div class="grid-info">
            <div class="info-card"><div class="info-label">Kategori / Tipe</div><div class="info-value">{{ $asset->category->name ?? 'Unknown' }} ({{ $asset->category->prefix ?? '-' }})</div></div>
            <div class="info-card"><div class="info-label">Hostname / Nama Aset</div><div class="info-value">{{ $asset->name }}</div></div>
            <div class="info-card"><div class="info-label">Merek / Model</div><div class="info-value">{{ $asset->brand_model ?? '-' }}</div></div>
            <div class="info-card"><div class="info-label">Status Operasional</div><div class="info-value" style="color: {{ $asset->status == 'aktif' ? '#059669' : '#dc2626' }};">{{ strtoupper($asset->status) }}</div></div>
            <div class="info-card"><div class="info-label">IP Address</div><div class="info-value font-mono">{{ $asset->ipAddress->ip_address ?? 'Belum Dialokasikan' }}</div></div>
            <div class="info-card"><div class="info-label">Tahun Pasang</div><div class="info-value">{{ $asset->installation_year ?? '-' }}</div></div>
            <div class="info-card"><div class="info-label">Lokasi Penempatan</div><div class="info-value">{{ $asset->building->name ?? 'Gudang' }} - {{ $asset->floor->name ?? '-' }}</div></div>
            <div class="info-card"><div class="info-label">Pengguna Saat Ini</div><div class="info-value">{{ $asset->current_user ?? '-' }}</div></div>
        </div>

        <h3 class="section-title">2. Spesifikasi Teknis Khusus</h3>
        <div class="grid-info">
            @forelse($asset->category->fields ?? [] as $field)
                @php $valObj = $asset->values->firstWhere('category_field_id', $field->id); @endphp
                <div class="info-card">
                    <div class="info-label">{{ $field->field_name }}</div>
                    <div class="info-value">{{ $valObj ? $valObj->value : '-' }}</div>
                </div>
            @empty
                <div style="grid-column: span 2; font-size: 12px; color: #64748b; padding: 10px;">Tidak ada spesifikasi khusus (EAV) untuk kategori ini.</div>
            @endforelse
        </div>

        <div style="background: #f8fafc; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 30px; page-break-inside: avoid;">
            <div class="info-label">Keterangan / Catatan Fisik</div>
            <div style="font-size: 12px; margin-top: 5px;">{{ $asset->description ?? 'Tidak ada catatan khusus.' }}</div>
        </div>

        <h3 class="section-title">3. Riwayat Mutasi & Perubahan (Timeline Log)</h3>
        
        <div class="timeline">
            @forelse($timeline as $log)
                <div class="timeline-item">
                    <div class="timeline-dot" style="background: {{ $log['type'] == 'MUTASI LOKASI / USER' ? '#10b981' : '#f59e0b' }}"></div>
                    
                    <div class="local-time" data-utc="{{ \Carbon\Carbon::parse($log['date'])->toISOString() }}" style="font-size: 10px; font-weight: bold; color: #64748b;">
                        Menghitung waktu...
                    </div>
                    
                    <div style="font-size: 12px; font-weight: 900; color: #0f172a; margin-top: 3px;">[{{ $log['type'] }}]</div>
                    <div style="font-size: 12px; color: #334155; margin-top: 3px;">{{ $log['desc'] }}</div>
                </div>
            @empty
                <div style="font-size: 12px; color: #64748b; margin-top: 10px;">Belum ada riwayat mutasi atau perubahan spesifikasi pada aset ini.</div>
            @endforelse
        </div>

        <div class="mt-8 text-right" style="padding-top: 40px; page-break-inside: avoid;">
            <p style="font-size: 12px; margin-bottom: 60px; color: #1e293b;">Divalidasi Oleh,</p>
            <p style="font-weight: bold; font-size: 12px; text-decoration: underline; color: #0f172a;">Sistem ITAM KBK</p>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const timeElements = document.querySelectorAll('.local-time');
            
            timeElements.forEach(el => {
                const isoString = el.getAttribute('data-utc');
                const date = new Date(isoString);
                
                // Konversi ke format lokal dengan menyebutkan zona waktu (WIB/WITA/WIT)
                const options = { 
                    day: '2-digit', 
                    month: 'long', 
                    year: 'numeric', 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    timeZoneName: 'short' 
                };
                
                // locale 'id-ID' memaksa format bahasa Indonesia
                let formattedTime = date.toLocaleDateString('id-ID', options);
                
                // Merapikan output (menghilangkan kata 'pukul' bawaan browser tertentu)
                formattedTime = formattedTime.replace(' pukul ', ' - ');
                
                el.innerText = formattedTime;
            });
        });
    </script>
</body>
</html>