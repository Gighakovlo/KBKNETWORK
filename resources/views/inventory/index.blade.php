@extends('layouts.itam')

@section('title', 'Dashboard Aset - ITAM KBK')

@section('content')
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
                <div class="bg-slate-800/50 hover:bg-slate-800 border border-slate-700 p-6 rounded-2xl transition cursor-pointer group shadow-lg relative overflow-hidden" 
                     onclick="window.location.href='{{ route('inventory.category', $cat->id) }}'" 
                     style="border-top: 4px solid {{ $cat->color ?? '#3b82f6' }};">
                    
                    @if($cat->icon_path)
                        <img src="{{ asset($cat->icon_path) }}" alt="icon" class="absolute -right-4 -bottom-4 w-32 h-32 opacity-5 group-hover:opacity-10 transition group-hover:scale-110 object-contain" style="filter: drop-shadow(0 0 10px {{ $cat->color ?? '#3b82f6' }});">
                    @else
                        <div class="absolute -right-4 -bottom-4 text-6xl opacity-5 group-hover:opacity-10 transition group-hover:scale-110" style="color: {{ $cat->color ?? '#3b82f6' }}">📦</div>
                    @endif

                    <div class="flex justify-between items-start mb-4 relative z-10">
                        <h3 class="text-lg font-bold text-white transition" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);">{{ $cat->name }}</h3>
                        <span class="bg-slate-950 text-[10px] font-bold tracking-widest px-2 py-1 rounded" style="color: {{ $cat->color ?? '#3b82f6' }}; border: 1px solid {{ $cat->color ?? '#3b82f6' }}40;">{{ $cat->prefix }}</span>
                    </div>
                    <p class="text-3xl font-black text-slate-300 relative z-10">{{ $cat->assets_count }} <span class="text-xs text-slate-500 font-medium uppercase tracking-widest">Unit</span></p>
                </div>
                @empty
                <div class="col-span-full p-10 border border-dashed border-slate-700 rounded-2xl text-center">
                    <p class="text-slate-500 font-bold">Belum ada kategori yang dikonfigurasi di Master Settings.</p>
                </div>
                @endforelse
            </div>

        </div>
    </main>
@endsection