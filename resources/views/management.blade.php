<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data Management - Network KBK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0b1120; }
        .glass-panel { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body class="text-slate-300 font-sans h-screen flex flex-col">

    <nav class="p-6 bg-slate-900 border-b border-slate-800 flex justify-between items-center z-20 shadow-xl">
        <div class="w-full flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 border-b border-slate-700/50 pb-6 mt-6 gap-6">
            
            <div class="flex items-center gap-6">
                <a href="/hub" class="text-slate-500 hover:text-white font-bold transition flex items-center gap-2 whitespace-nowrap shrink-0">
                    <span class="text-xl">&larr;</span> Kembali ke Hub
                </a>
                
                <div class="h-12 w-px bg-slate-700 hidden md:block"></div>
                
                <div>
                    <h1 class="text-4xl font-black text-white uppercase tracking-widest drop-shadow-md">Data Management</h1>
                    <p class="text-slate-400 text-sm mt-1 font-bold tracking-wide">Kelola Master Data Gedung, Lantai, dan Topologi Perangkat</p>
                </div>
            </div>
            
           <div class="flex flex-row gap-4 shrink-0">
                <a href="{{ route('print.report') }}?type=visual" target="_blank" class="bg-slate-800/50 backdrop-blur-md border border-blue-500/30 hover:border-blue-400 text-blue-100 hover:text-white px-5 py-3 rounded-xl font-bold transition-all duration-300 shadow-[0_0_15px_rgba(59,130,246,0.2)] hover:shadow-[0_0_25px_rgba(59,130,246,0.4)] flex items-center gap-2 group">
                    <span class="text-xl group-hover:scale-110 transition-transform">🗺️</span> 
                    Print Visual
                </a>
                
                <a href="{{ route('print.report') }}?type=data" target="_blank" class="bg-slate-800/50 backdrop-blur-md border border-emerald-500/30 hover:border-emerald-400 text-emerald-100 hover:text-white px-5 py-3 rounded-xl font-bold transition-all duration-300 shadow-[0_0_15px_rgba(16,185,129,0.2)] hover:shadow-[0_0_25px_rgba(16,185,129,0.4)] flex items-center gap-2 group">
                    <span class="text-xl group-hover:scale-110 transition-transform">📄</span> 
                    Print Data
                </a>

                <a href="{{ route('export.inventory') }}" class="bg-slate-800/50 backdrop-blur-md border border-amber-500/30 hover:border-amber-400 text-amber-100 hover:text-white px-5 py-3 rounded-xl font-bold transition-all duration-300 shadow-[0_0_15px_rgba(245,158,11,0.2)] hover:shadow-[0_0_25px_rgba(245,158,11,0.4)] flex items-center gap-2 group">
                    <span class="text-xl group-hover:scale-110 transition-transform">📊</span> 
                    Export Excel
                </a>
            </div>

        </div>
    </nav>

    <main class="flex-grow p-10 overflow-y-auto custom-scrollbar">
        <div class="max-w-7xl mx-auto space-y-10">

            <div class="glass-panel rounded-2xl overflow-hidden shadow-2xl">
                <div class="bg-slate-800/80 p-6 border-b border-slate-700 flex flex-col md:flex-row justify-between items-center gap-4">
                    <h2 class="text-xl font-black text-white tracking-wide">Data Gedung (Macro)</h2>
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <input type="text" id="searchBldg" onkeyup="filterTable('searchBldg', 'tableBldg')" placeholder="Cari Gedung..." class="bg-slate-900 border border-slate-600 text-sm rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500 w-full md:w-64">
                        <button onclick="deleteBatch('building')" class="bg-red-900/40 text-red-400 border border-red-500 hover:bg-red-600 hover:text-white px-4 py-2 rounded-lg font-bold text-sm transition hidden whitespace-nowrap" id="btnBatchBldg">Hapus Terpilih</button>
                    </div>
                </div>
                <div class="p-6 max-h-[400px] overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left text-sm" id="tableBldg">
                        <thead class="text-slate-400 uppercase tracking-widest text-xs border-b border-slate-700">
                            <tr>
                                <th class="pb-3 w-10"><input type="checkbox" onchange="toggleAll(this, 'cb-bldg', 'btnBatchBldg')" class="accent-blue-500"></th>
                                <th class="pb-3">ID</th>
                                <th class="pb-3">Nama Gedung</th>
                                <th class="pb-3">Total Lantai</th>
                                <th class="pb-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            @foreach($buildings as $b)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-4"><input type="checkbox" value="{{ $b->id }}" class="cb-bldg accent-blue-500" onchange="checkBatchBtn('cb-bldg', 'btnBatchBldg')"></td>
                                <td class="py-4 text-slate-500">#{{ $b->id }}</td>
                                <td class="py-4 font-bold text-white search-target">{{ $b->name }}</td>
                                <td class="py-4"><span class="bg-blue-900/50 text-blue-400 px-2 py-1 rounded text-xs">{{ $b->floors_count }} Lantai</span></td>
                                <td class="py-4 text-right space-x-2">
                                    <button onclick="editData('building', {{ $b->id }}, '{{ addslashes($b->name) }}')" class="text-amber-400 hover:text-amber-300 font-bold px-3 py-1 rounded border border-amber-900/50 hover:bg-amber-900/20 transition">Edit Nama</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-panel rounded-2xl overflow-hidden shadow-2xl">
                <div class="bg-slate-800/80 p-6 border-b border-slate-700 flex flex-col md:flex-row justify-between items-center gap-4">
                    <h2 class="text-xl font-black text-white tracking-wide">Data Lantai (Micro)</h2>
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <input type="text" id="searchFloor" onkeyup="filterTable('searchFloor', 'tableFloor')" placeholder="Cari Lantai..." class="bg-slate-900 border border-slate-600 text-sm rounded-lg px-4 py-2 text-white focus:outline-none focus:border-blue-500 w-full md:w-64">
                        <button onclick="deleteBatch('floor')" class="bg-red-900/40 text-red-400 border border-red-500 hover:bg-red-600 hover:text-white px-4 py-2 rounded-lg font-bold text-sm transition hidden whitespace-nowrap" id="btnBatchFloor">Hapus Terpilih</button>
                    </div>
                </div>
                <div class="p-6 max-h-[400px] overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left text-sm" id="tableFloor">
                        <thead class="text-slate-400 uppercase tracking-widest text-xs border-b border-slate-700">
                            <tr>
                                <th class="pb-3 w-10"><input type="checkbox" onchange="toggleAll(this, 'cb-floor', 'btnBatchFloor')" class="accent-blue-500"></th>
                                <th class="pb-3">ID</th>
                                <th class="pb-3">Nama Lantai</th>
                                <th class="pb-3">Gedung Induk</th>
                                <th class="pb-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            @foreach($floors as $f)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-4"><input type="checkbox" value="{{ $f->id }}" class="cb-floor accent-blue-500" onchange="checkBatchBtn('cb-floor', 'btnBatchFloor')"></td>
                                <td class="py-4 text-slate-500">#{{ $f->id }}</td>
                                <td class="py-4 font-bold text-white search-target">{{ $f->name }}</td>
                                <td class="py-4 text-slate-400">{{ $f->building->name ?? 'Terlepas' }}</td>
                                <td class="py-4 text-right space-x-2">
                                    <button onclick="editData('floor', {{ $f->id }}, '{{ addslashes($f->name) }}')" class="text-amber-400 hover:text-amber-300 font-bold px-3 py-1 rounded border border-amber-900/50 hover:bg-amber-900/20 transition">Edit Nama</button>
                                    @if($f->building_id)
                                        <a href="/micro-studio/floor/{{ $f->id }}" class="inline-block text-blue-400 hover:text-blue-300 font-bold px-3 py-1 rounded border border-blue-900/50 hover:bg-blue-900/20 transition">Ubah Visual</a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <div id="toast" class="fixed bottom-10 right-10 px-6 py-3 rounded-lg shadow-2xl transform translate-y-20 opacity-0 transition-all duration-300 z-50 font-bold text-sm text-white"></div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // --- FITUR SEARCH ---
        function filterTable(inputId, tableId) {
            let filter = document.getElementById(inputId).value.toLowerCase();
            let rows = document.getElementById(tableId).getElementsByTagName("tbody")[0].getElementsByTagName("tr");
            for (let i = 0; i < rows.length; i++) {
                let targetCell = rows[i].querySelector(".search-target");
                if (targetCell) {
                    let text = targetCell.textContent || targetCell.innerText;
                    rows[i].style.display = text.toLowerCase().indexOf(filter) > -1 ? "" : "none";
                }
            }
        }

        // --- FITUR CHECKBOX & BATCH DELETE ---
        function toggleAll(source, cbClass, btnId) {
            let checkboxes = document.querySelectorAll('.' + cbClass);
            checkboxes.forEach(cb => {
                if(cb.closest('tr').style.display !== 'none') cb.checked = source.checked;
            });
            checkBatchBtn(cbClass, btnId);
        }

        function checkBatchBtn(cbClass, btnId) {
            let checked = document.querySelectorAll(`.${cbClass}:checked`).length > 0;
            let btn = document.getElementById(btnId);
            if(checked) { btn.classList.remove('hidden'); btn.classList.add('inline-block'); } 
            else { btn.classList.add('hidden'); btn.classList.remove('inline-block'); }
        }

        function deleteBatch(type) {
            let cbClass = type === 'building' ? 'cb-bldg' : 'cb-floor';
            let selected = Array.from(document.querySelectorAll(`.${cbClass}:checked`)).map(cb => cb.value);
            
            if (selected.length === 0) return;
            if (!confirm(`Yakin ingin menghapus ${selected.length} data terpilih secara permanen?`)) return;

            fetch(`/${type}/batch-delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ ids: selected })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    showToast(data.message, false);
                    setTimeout(() => location.reload(), 1500);
                }
            });
        }

        // --- FITUR EDIT NAMA ---
        function editData(type, id, currentName) {
            let newName = prompt(`Ubah nama ${type === 'building' ? 'Gedung' : 'Lantai'}:`, currentName);
            if (newName && newName.trim() !== "" && newName !== currentName) {
                fetch(`/${type}/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ name: newName })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) { showToast(data.message, false); setTimeout(() => location.reload(), 1000); }
                });
            }
        }

        function showToast(msg, isError = false) {
            const t = document.getElementById('toast');
            t.innerText = msg;
            t.className = `fixed bottom-10 right-10 px-6 py-3 rounded-lg shadow-2xl transform transition-all duration-300 z-[999] font-bold text-sm ${isError ? 'bg-red-600' : 'bg-emerald-600'} text-white translate-y-0 opacity-100`;
            setTimeout(() => { t.classList.remove('translate-y-0', 'opacity-100'); t.classList.add('translate-y-20', 'opacity-0'); }, 3000);
        }
    </script>
</body>
</html>