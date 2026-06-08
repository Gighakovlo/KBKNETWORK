<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ITAM - PT. KBK')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0b1120; }
        .glass-panel { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .sidebar-link { transition: all 0.2s ease-in-out; }
        .sidebar-link:hover { background: rgba(59, 130, 246, 0.1); border-left-color: #3b82f6; color: #60a5fa; }
        .sidebar-active { background: rgba(59, 130, 246, 0.15); border-left-color: #3b82f6; color: #60a5fa; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(59, 130, 246, 0.5); border-radius: 10px; }
    </style>
    @stack('styles')
</head>
<body class="text-slate-300 font-sans h-screen flex overflow-hidden">

    <aside class="w-72 bg-slate-900 border-r border-slate-800 flex flex-col z-30 shadow-2xl shrink-0">
        <div class="p-6 border-b border-slate-800">
            <h1 class="text-2xl font-black text-white uppercase tracking-widest">ITAM Data</h1>
            <p class="text-emerald-400 text-[10px] mt-1 font-bold tracking-widest uppercase">Enterprise Asset Management</p>
        </div>
        
        <div class="flex-grow py-6 flex flex-col gap-2">
            <a href="{{ route('inventory.index') }}" class="sidebar-link {{ request()->routeIs('inventory.*') ? 'sidebar-active' : 'text-slate-400 border-transparent' }} flex items-center gap-4 px-6 py-4 border-l-4 text-sm font-bold tracking-wide">
                <span class="text-xl">🗄️</span> Dashboard Aset
            </a>
            <a href="{{ route('movements.index') }}" class="sidebar-link {{ request()->routeIs('movements.*') ? 'sidebar-active' : 'text-slate-400 border-transparent' }} flex items-center gap-4 px-6 py-4 border-l-4 text-sm font-bold tracking-wide">
                <span class="text-xl">📍</span> Log Mutasi
            </a>
            <a href="{{ route('requests.index') }}" class="sidebar-link {{ request()->routeIs('requests.*') ? 'sidebar-active' : 'text-slate-400 border-transparent' }} flex items-center gap-4 px-6 py-4 border-l-4 text-sm font-bold tracking-wide">
                <span class="text-xl">🎫</span> Ticketing
            </a>
            <a href="{{ route('ipam.index') }}" class="sidebar-link {{ request()->routeIs('ipam.*') ? 'sidebar-active' : 'text-slate-400 border-transparent' }} flex items-center gap-4 px-6 py-4 border-l-4 text-sm font-bold tracking-wide">
                <span class="text-xl">🌐</span> IP Manager
            </a>

            <a href="{{ route('documents.index') }}" class="sidebar-link {{ request()->routeIs('documents.*') ? 'sidebar-active' : 'text-slate-400 border-transparent' }} flex items-center gap-4 px-6 py-4 border-l-4 text-sm font-bold tracking-wide">
                <span class="text-xl">📄</span> Arsip Dokumen
            </a>

            <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'sidebar-active' : 'text-slate-400 border-transparent' }} flex items-center gap-4 px-6 py-4 border-l-4 text-sm font-bold tracking-wide">
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
        @yield('content')
    </div>

    <div id="toast" class="fixed top-6 right-6 px-6 py-4 rounded-xl shadow-2xl transition-all duration-300 opacity-0 z-[9999] font-bold text-sm text-white transform -translate-y-4 pointer-events-none"></div>

    @stack('scripts')
</body>
</html>