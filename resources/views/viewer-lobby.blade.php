<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viewer Portal - Network Center KBK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0f172a; }
        .glass-card { background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .glass-nav { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .hover-glow:hover { box-shadow: 0 0 20px rgba(20, 184, 166, 0.3); border-color: rgba(20, 184, 166, 0.5); }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="text-slate-300 font-sans min-h-screen flex flex-col relative overflow-x-hidden">

    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] right-[-10%] w-96 h-96 bg-teal-900 rounded-full mix-blend-screen filter blur-[100px] opacity-20"></div>
    </div>

    <nav class="glass-nav sticky top-0 z-50 px-8 py-4 flex justify-between items-center shadow-lg">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-teal-600 to-emerald-400 text-white flex items-center justify-center font-black text-xl rounded-lg shadow-[0_0_15px_rgba(20,184,166,0.4)] border border-teal-400">KBK</div>
            <div>
                <h1 class="text-xl font-bold text-white tracking-wide">Network <span class="text-teal-500">Monitoring</span></h1>
                <p class="text-xs text-slate-400 uppercase tracking-widest font-semibold">Read-Only Access Portal</p>
            </div>
        </div>
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
                <span class="relative flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span></span>
                <span class="text-sm font-bold text-green-400 tracking-wide">SYSTEM ONLINE</span>
            </div>
            <div class="h-6 w-px bg-slate-700"></div>
            <a href="/login" class="text-sm font-bold text-slate-400 hover:text-blue-400 transition">LOGIN ADMIN &rarr;</a>
        </div>
    </nav>

    <main class="flex-grow p-8 max-w-5xl mx-auto w-full relative z-10 mt-6">
        <div class="glass-card p-10 rounded-3xl shadow-2xl h-full border-t-4 border-teal-600 relative overflow-hidden">
            
            <div class="flex justify-between items-end mb-10 border-b border-slate-700 pb-5 relative z-10">
                <div>
                    <h2 class="text-3xl font-black text-white tracking-wide">Direktori Topologi</h2>
                    <p class="text-sm text-slate-400 mt-2">Pilih lokasi lantai untuk memantau status perangkat jaringan secara real-time.</p>
                </div>
            </div>

            @if($buildings->isEmpty())
                <div class="text-center py-24 border-2 border-dashed border-slate-700 rounded-2xl bg-slate-800/30">
                    <div class="text-6xl mb-5 opacity-50">📡</div>
                    <h3 class="text-xl font-bold text-slate-300">Menunggu Data Sinkronisasi</h3>
                    <p class="text-sm text-slate-500 mt-2">Belum ada topologi jaringan yang dipublikasikan oleh Administrator.</p>
                </div>
            @else
                <div class="space-y-8">
                    @foreach($buildings as $b)
                        <div class="bg-slate-900/60 rounded-2xl border border-slate-700/60 overflow-hidden shadow-lg">
                            <div class="bg-slate-800/90 px-8 py-5 border-b border-slate-700 flex items-center gap-4">
                                <span class="text-3xl drop-shadow-md">🏢</span>
                                <h3 class="text-xl font-black text-white tracking-wide">{{ $b->name }}</h3>
                            </div>
                            
                            <div class="p-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @forelse($b->floors as $floor)
                                    <a href="/viewer/mapping/{{ $floor->id }}" class="glass-card hover-glow p-6 rounded-2xl transition duration-300 group relative overflow-hidden block">
                                        <div class="absolute right-0 top-0 h-full w-1.5 bg-gradient-to-b from-teal-400 to-emerald-500 transform translate-x-2 group-hover:translate-x-0 transition duration-300"></div>
                                        <div class="flex flex-col gap-4">
                                            <div class="w-12 h-12 rounded-xl bg-teal-900/50 text-teal-400 flex items-center justify-center font-bold border border-teal-800/50 group-hover:bg-teal-500 group-hover:text-white transition duration-300 shadow-inner">
                                                🖥️
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-bold text-slate-200 group-hover:text-white transition">{{ $floor->name }}</h4>
                                                <p class="text-xs text-teal-500 uppercase tracking-widest mt-1 font-semibold flex items-center gap-1">Pantau Jaringan <span class="group-hover:translate-x-1 transition">&rarr;</span></p>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="col-span-full text-center py-8 text-sm text-slate-500 italic bg-slate-900/40 rounded-xl border border-slate-800 dashed">
                                        Denah lantai untuk gedung ini belum tersedia.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>
</body>
</html>