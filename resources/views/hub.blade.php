<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Center - Network KBK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0b1120; background-image: radial-gradient(circle at 50% 0%, #1e3a8a 0%, transparent 70%); }
        .glass-card { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body class="text-slate-300 font-sans min-h-screen flex flex-col">

    <nav class="p-8 flex justify-between items-center z-20 border-b border-slate-800/50 glass-card">
        <div class="flex items-center gap-5">
            <div class="mr-6 flex items-center justify-center shrink-0">
                    <img src="{{ asset('img/KBK LOGO PUTIH.png') }}" alt="Logo PT KBK" class="h-12 w-auto object-contain drop-shadow-[0_0_10px_rgba(255,255,255,0.2)]">
            </div>           
            <div>
                <h1 class="text-2xl font-black text-white tracking-widest uppercase">Command Center</h1>
                <p class="text-xs text-blue-400 font-bold uppercase tracking-[0.3em] mt-1">Sistem Pemetaan Terpusat</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="/management" class="text-slate-300 hover:text-white font-bold px-5 py-2 border border-slate-700 hover:bg-slate-800 rounded-xl transition-all duration-300 flex items-center gap-2">
                ⚙️ Data Management
            </a>
            <form action="/logout" method="POST">
                @csrf
                <button type="submit" class="text-slate-400 font-bold px-6 py-2 border border-slate-700 hover:bg-red-500/20 hover:text-red-400 hover:border-red-500/50 rounded-xl transition-all duration-300">LOGOUT</button>
            </form>
        </div>
    </nav>

    <main class="flex-grow flex items-center justify-center p-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-7xl w-full">
            
            <a href="/live-monitor" class="glass-card p-10 rounded-3xl hover:-translate-y-2 hover:bg-slate-800/80 hover:border-blue-500/50 transition-all duration-300 group shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl group-hover:bg-blue-500/30 transition-all"></div>
                <div class="text-6xl mb-6 group-hover:scale-110 transition-transform origin-left">🌐</div>
                <h2 class="text-2xl font-black text-white mb-3">Live Monitor</h2>
                <p class="text-sm text-slate-400 leading-relaxed">Pantau seluruh infrastruktur jaringan, switch, dan PC dalam satu peta kawasan. (Mode Read-Only).</p>
                <div class="mt-8 text-blue-400 font-bold text-xs uppercase tracking-widest flex items-center gap-2">Akses Peta <span class="group-hover:translate-x-2 transition-transform">&rarr;</span></div>
            </a>

            <a href="/macro-editor" class="glass-card p-10 rounded-3xl hover:-translate-y-2 hover:bg-slate-800/80 hover:border-amber-500/50 transition-all duration-300 group shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 rounded-full blur-3xl group-hover:bg-amber-500/30 transition-all"></div>
                <div class="text-6xl mb-6 group-hover:scale-110 transition-transform origin-left">📐</div>
                <h2 class="text-2xl font-black text-white mb-3">Macro Editor</h2>
                <p class="text-sm text-slate-400 leading-relaxed">Upload denah master, lakukan cropping (Polygon Hitbox) pada area gedung, dan atur struktur kawasan.</p>
                <div class="mt-8 text-amber-400 font-bold text-xs uppercase tracking-widest flex items-center gap-2">Edit Kawasan <span class="group-hover:translate-x-2 transition-transform">&rarr;</span></div>
            </a>

            <button onclick="openMicroModal()" class="text-left w-full glass-card p-10 rounded-3xl hover:-translate-y-2 hover:bg-slate-800/80 hover:border-emerald-500/50 transition-all duration-300 group shadow-2xl relative overflow-hidden cursor-pointer block">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full blur-3xl group-hover:bg-emerald-500/30 transition-all"></div>
                <div class="text-6xl mb-6 group-hover:scale-110 transition-transform origin-left">🎛️</div>
                <h2 class="text-2xl font-black text-white mb-3">Micro Studio</h2>
                <p class="text-sm text-slate-400 leading-relaxed">Masuk ke dalam spesifik lantai. Upload denah presisi dan atur tata letak perangkat via Split-Screen.</p>
                <div class="mt-8 text-emerald-400 font-bold text-xs uppercase tracking-widest flex items-center gap-2">Kelola Perangkat <span class="group-hover:translate-x-2 transition-transform">&rarr;</span></div>
            </button>

        </div>
    </main>

    <footer class="p-6 text-center text-slate-600 text-xs font-bold tracking-widest uppercase">
        &copy; 2026 PT. Krakatau Baja Konstruksi - Network Division
    </footer>

    <div id="microModal" class="fixed inset-0 z-50 bg-[#0f172a]/90 backdrop-blur-md hidden flex flex-col items-center justify-center p-10 opacity-0 transition-opacity duration-300">
        <button onclick="closeMicroModal()" class="absolute top-10 right-10 w-14 h-14 bg-slate-800 hover:bg-red-500 hover:scale-110 rounded-full text-white text-3xl flex items-center justify-center transition-all shadow-xl">&times;</button>
        
        <div id="step1-buildings" class="w-full max-w-5xl transition-all duration-500 transform scale-100">
            <h2 class="text-4xl font-black text-white mb-2 tracking-tight text-center">Micro Studio</h2>
            <p class="text-emerald-400 uppercase tracking-[0.4em] font-bold text-xs mb-12 text-center">Pilih Gedung Target</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                @forelse($buildings as $b)
                    <button onclick="openStep2({{ $b->id }}, '{{ addslashes($b->name) }}')" class="bg-slate-900 border border-slate-700 p-6 rounded-2xl hover:border-emerald-500 hover:bg-emerald-900/20 transition-all text-left group shadow-lg">
                        <div class="text-3xl mb-3 group-hover:scale-110 transition-transform origin-left">🏢</div>
                        <h3 class="text-lg font-bold text-white">{{ $b->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ $b->floors->count() }} Lantai Terdaftar</p>
                    </button>
                @empty
                    <div class="col-span-full text-center text-slate-500 italic py-10">Belum ada gedung di Macro Editor.</div>
                @endforelse
            </div>
        </div>

        <div id="step2-floors" class="w-full max-w-6xl transition-all duration-500 transform scale-95 hidden absolute">
            <button onclick="backToStep1()" class="mb-6 text-slate-400 hover:text-white font-bold flex items-center gap-2 transition">&larr; Kembali Pilih Gedung</button>
            <h2 id="modalBuildingName" class="text-4xl font-black text-white mb-2 tracking-tight">Nama Gedung</h2>
            <p class="text-blue-400 uppercase tracking-[0.4em] font-bold text-xs mb-8">Daftar Lantai & Ruang Operasi</p>
            
            <div id="floorsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                </div>
        </div>
    </div>

    <script>
        const allBuildings = @json($buildings);

        function openMicroModal() {
            const modal = document.getElementById('microModal');
            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.remove('opacity-0'), 10);
            backToStep1(); 
        }

        function closeMicroModal() {
            const modal = document.getElementById('microModal');
            modal.classList.add('opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        function openStep2(buildingId, buildingName) {
            document.getElementById('step1-buildings').classList.add('scale-95', 'opacity-0', 'pointer-events-none');
            setTimeout(() => {
                document.getElementById('step1-buildings').classList.add('hidden');
                document.getElementById('step2-floors').classList.remove('hidden');
                
                setTimeout(() => {
                    document.getElementById('step2-floors').classList.remove('scale-95', 'opacity-0');
                }, 10);
            }, 300);

            document.getElementById('modalBuildingName').innerText = buildingName;
            
            const building = allBuildings.find(b => b.id === buildingId);
            const grid = document.getElementById('floorsGrid');
            grid.innerHTML = '';

            // 1. KARTU TAMBAH LANTAI
            grid.innerHTML += `
                <a href="/micro-studio/${buildingId}" class="border-2 border-dashed border-slate-600 hover:border-amber-500 hover:bg-amber-500/10 rounded-3xl p-8 flex flex-col items-center justify-center text-center transition-all group min-h-[200px]">
                    <div class="text-4xl mb-3 text-slate-500 group-hover:text-amber-500 group-hover:scale-110 transition-all">+</div>
                    <div class="text-slate-400 group-hover:text-amber-400 font-bold">Atur / Tambah Lantai Baru</div>
                    <div class="text-[10px] text-slate-500 mt-2">(Masuk ke Bounding Box Editor)</div>
                </a>
            `;

            // 2. KARTU LANTAI (SNEAK PEEK)
            if(building.floors && building.floors.length > 0) {
                building.floors.forEach(f => {
                    let bgImage = f.image_path ? `url('${f.image_path}')` : 'none';
                    
                    grid.innerHTML += `
                        <a href="/mapping/${f.id}" class="relative rounded-3xl overflow-hidden border border-slate-700 hover:border-blue-500 hover:shadow-[0_0_30px_rgba(59,130,246,0.3)] transition-all group min-h-[200px] flex items-end">
                            <div class="absolute inset-0 bg-slate-800 bg-cover bg-center opacity-40 group-hover:opacity-70 group-hover:scale-110 transition-all duration-500" style="background-image: ${bgImage};"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-transparent"></div>
                            
                            <div class="relative p-6 w-full transform translate-y-2 group-hover:translate-y-0 transition-transform">
                                <h3 class="text-xl font-black text-white shadow-black drop-shadow-md">${f.name}</h3>
                                <div class="text-blue-400 text-[10px] mt-2 uppercase tracking-widest font-bold flex items-center justify-between">
                                    <span>Buka Ruang Operasi</span>
                                    <span class="text-lg opacity-0 group-hover:opacity-100 group-hover:translate-x-2 transition-all">&rarr;</span>
                                </div>
                            </div>
                        </a>
                    `;
                });
            }
        }

        function backToStep1() {
            document.getElementById('step2-floors').classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                document.getElementById('step2-floors').classList.add('hidden');
                document.getElementById('step1-buildings').classList.remove('hidden', 'pointer-events-none');
                setTimeout(() => {
                    document.getElementById('step1-buildings').classList.remove('scale-95', 'opacity-0');
                }, 10);
            }, 300);
        }
    </script>
</body>
</html>