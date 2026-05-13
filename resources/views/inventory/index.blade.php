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
        .sidebar-link { transition: all 0.2s ease-in-out; }
        .sidebar-link:hover { background: rgba(59, 130, 246, 0.1); border-left-color: #3b82f6; color: #60a5fa; }
        .sidebar-active { background: rgba(59, 130, 246, 0.15); border-left-color: #3b82f6; color: #60a5fa; }
    </style>
</head>
<body class="text-slate-300 font-sans h-screen flex overflow-hidden">

    <aside class="w-72 bg-slate-900 border-r border-slate-800 flex flex-col z-30 shadow-2xl shrink-0">
        <div class="p-6 border-b border-slate-800">
            <h1 class="text-2xl font-black text-white uppercase tracking-widest">ITAM Data</h1>
            <p class="text-emerald-400 text-[10px] mt-1 font-bold tracking-widest uppercase">Enterprise Asset Management</p>
        </div>
        
        <div class="flex-grow py-6 flex flex-col gap-2">
            <a href="{{ route('inventory.index') }}" class="sidebar-link sidebar-active flex items-center gap-4 px-6 py-4 border-l-4 border-transparent text-sm font-bold tracking-wide">
                <span class="text-xl">🗄️</span> Dashboard Aset
            </a>
            <a href="{{ route('movements.index') }}" class="sidebar-link flex items-center gap-4 px-6 py-4 border-l-4 border-transparent text-sm font-bold tracking-wide text-slate-400">
                <span class="text-xl">📍</span> Log Mutasi
            </a>
            <a href="{{ route('requests.index') }}" class="sidebar-link flex items-center gap-4 px-6 py-4 border-l-4 border-transparent text-sm font-bold tracking-wide text-slate-400">
                <span class="text-xl">🎫</span> Ticketing
            </a>
            <a href="{{ route('ipam.index') }}" class="sidebar-link flex items-center gap-4 px-6 py-4 border-l-4 border-transparent text-sm font-bold tracking-wide text-slate-400">
                <span class="text-xl">🌐</span> IP Manager
            </a>
            <a href="{{ route('settings.index') }}" class="sidebar-link flex items-center gap-4 px-6 py-4 border-l-4 border-transparent text-sm font-bold tracking-wide text-slate-400">
                <span class="text-xl">⚙️</span> Master Settings
            </a>
        </div>

        <div class="p-6 border-t border-slate-800">
            <a href="/hub" class="text-slate-500 hover:text-white font-bold transition flex items-center gap-2 text-sm">
                <span class="text-lg">&larr;</span> Kembali ke HUB
            </a>
        </div>
    </aside>

    <div class="flex-grow flex flex-col relative overflow-hidden">
        
        <nav class="p-6 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 flex justify-between items-center z-20 shadow-lg">
            <div class="flex items-center gap-4">
                <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                <h2 class="text-lg font-black text-white uppercase tracking-widest">Dashboard Aset</h2>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('inventory.print') }}" target="_blank" class="bg-blue-600/20 border border-blue-500 text-blue-400 hover:bg-blue-600 hover:text-white px-5 py-2.5 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(59,130,246,0.2)]">
                    🗺️ Print Visual
                </a>
                <a href="{{ route('inventory.print') }}?type=data" target="_blank" class="bg-indigo-600/20 border border-indigo-500 text-indigo-400 hover:bg-indigo-600 hover:text-white px-5 py-2.5 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(79,70,229,0.2)]">
                    📄 Print Data
                </a>
                <a href="{{ route('inventory.export') }}" class="bg-emerald-600/20 border border-emerald-500 text-emerald-400 hover:bg-emerald-600 hover:text-white px-5 py-2.5 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(16,185,129,0.2)]">
                    📊 Export Excel
                </a>
            </div>
        </nav>

        <main class="flex-grow p-10 overflow-y-auto custom-scrollbar">
            <div class="max-w-7xl mx-auto space-y-8">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="glass-panel p-8 rounded-2xl border-t-4 border-emerald-500 flex flex-col justify-center relative overflow-hidden shadow-xl">
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-emerald-500/10 rounded-full blur-3xl"></div>
                        <p class="text-sm text-emerald-500 uppercase tracking-widest font-black mb-2">Total Aset Terdaftar</p>
                        <p class="text-6xl font-black text-white">{{ $totalAssets }} <span class="text-lg text-slate-500 font-medium">Unit</span></p>
                    </div>
                    
                    <a href="{{ route('inventory.create') }}" class="bg-gradient-to-br from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 p-8 rounded-2xl flex flex-col justify-center items-center group transition-all shadow-[0_10px_30px_rgba(16,185,129,0.3)] hover:scale-[1.02]">
                        <span class="text-5xl mb-3 text-emerald-100 group-hover:scale-125 transition-transform duration-300">+</span>
                        <h3 class="text-xl font-black text-white uppercase tracking-widest">Tambah Aset Baru</h3>
                        <p class="text-emerald-200 text-xs mt-2 font-medium">Register perangkat ke dalam sistem inventaris</p>
                    </a>
                </div>

                <h2 class="text-lg font-black text-slate-400 tracking-widest border-b border-slate-800 pb-3 mt-10 uppercase">Kategori Perangkat Tersedia</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @forelse($categories as $cat)
                    <div class="bg-slate-800/50 hover:bg-slate-800 border border-slate-700 hover:border-blue-500 p-6 rounded-2xl transition cursor-pointer group shadow-lg relative overflow-hidden" onclick="window.location.href='{{ route('inventory.category', $cat->id) }}'">
                        <div class="absolute -right-4 -bottom-4 text-6xl opacity-5 group-hover:opacity-10 transition group-hover:scale-110">📦</div>
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-bold text-white group-hover:text-blue-400 transition">{{ $cat->name }}</h3>
                            <span class="bg-slate-950 text-[10px] font-bold tracking-widest text-blue-400 border border-blue-500/30 px-2 py-1 rounded">{{ $cat->prefix }}</span>
                        </div>
                        <p class="text-3xl font-black text-slate-300">{{ $cat->assets_count }} <span class="text-xs text-slate-500 font-medium uppercase tracking-widest">Unit</span></p>
                    </div>
                    @empty
                    <div class="col-span-full p-10 border border-dashed border-slate-700 rounded-2xl text-center">
                        <p class="text-slate-500 font-bold">Belum ada kategori yang dikonfigurasi di Master Settings.</p>
                    </div>
                    @endforelse
                </div>

            </div>
        </main>
    </div>
</body>
</html>