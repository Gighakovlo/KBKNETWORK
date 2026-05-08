<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Asset Management - KBK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0b1120; }
        .glass-panel { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="text-slate-300 font-sans h-screen flex flex-col overflow-hidden">

    <nav class="p-6 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 flex justify-between items-center z-20 shadow-xl">
        <div class="flex items-center gap-6">
            <a href="/hub" class="text-slate-500 hover:text-white font-bold transition flex items-center gap-2">
                <span class="text-xl">&larr;</span> Kembali
            </a>
            <div class="h-8 w-px bg-slate-700"></div>
            <div>
                <h1 class="text-2xl font-black text-white uppercase tracking-widest drop-shadow-md">Data Center & Inventory</h1>
                <p class="text-emerald-400 text-xs mt-1 font-bold tracking-wide uppercase">Enterprise Asset Management</p>
            </div>
        </div>
        
        <div class="flex gap-4">
            <a href="{{ route('requests.index') }}" class="bg-fuchsia-600/20 border border-fuchsia-500 text-fuchsia-400 hover:bg-fuchsia-600 hover:text-white px-5 py-2.5 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(217,70,239,0.2)]">
                🎫 Ticketing
            </a>

            <a href="{{ route('movements.index') }}" class="bg-amber-600/20 border border-amber-500 text-amber-500 hover:bg-amber-600 hover:text-white px-5 py-2.5 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(245,158,11,0.2)]">
                📍 Log Mutasi
            </a>

            <a href="{{ route('ipam.index') }}" class="bg-cyan-600/20 border border-cyan-500 text-cyan-400 hover:bg-cyan-600 hover:text-white px-5 py-2.5 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(6,182,212,0.2)]">
                🌐 IP Manager
            </a>
            <a href="{{ route('settings.index') }}" class="bg-blue-600/20 border border-blue-500 text-blue-400 hover:bg-blue-600 hover:text-white px-5 py-2.5 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(59,130,246,0.2)]">
                ⚙️ Master Settings
            </a>
            <a href="{{ route('inventory.create') }}" class="bg-emerald-600/20 border border-emerald-500 text-emerald-400 hover:bg-emerald-600 hover:text-white px-5 py-2.5 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(16,185,129,0.2)]">
                + Tambah Aset Baru
            </a>
        </div>
    </nav>

    <main class="flex-grow p-10 overflow-y-auto">
        <div class="max-w-7xl mx-auto space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="glass-panel p-6 rounded-2xl border-t-4 border-emerald-500">
                    <p class="text-xs text-slate-500 uppercase tracking-widest font-bold mb-2">Total Aset Terdaftar</p>
                    <p class="text-4xl font-black text-white">{{ $totalAssets }} <span class="text-sm text-slate-400 font-medium">Unit</span></p>
                </div>
                </div>

            <h2 class="text-xl font-black text-white tracking-wide border-b border-slate-800 pb-3 mt-10">Kategori Perangkat</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                @forelse($categories as $cat)
                <div class="bg-slate-800/50 hover:bg-slate-800 border border-slate-700 hover:border-blue-500 p-6 rounded-2xl transition cursor-pointer group" onclick="window.location.href='{{ route('inventory.category', $cat->id) }}'">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-bold text-white group-hover:text-blue-400 transition">{{ $cat->name }}</h3>
                        <span class="bg-slate-900 text-xs font-mono text-slate-400 px-2 py-1 rounded">{{ $cat->prefix }}-XXXX</span>
                    </div>
                    <p class="text-3xl font-black text-slate-300">{{ $cat->assets_count }} <span class="text-sm text-slate-500 font-medium">Item</span></p>
                </div>
                @empty
                <div class="col-span-4 p-8 border border-dashed border-slate-700 rounded-2xl text-center">
                    <p class="text-slate-500 font-bold">Belum ada kategori yang dikonfigurasi.</p>
                </div>
                @endforelse
            </div>

        </div>
    </main>
</body>
</html>