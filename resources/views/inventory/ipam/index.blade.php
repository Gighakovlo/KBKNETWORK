@extends('layouts.itam')

@section('title', 'IP Address Management - ITAM KBK')

@section('content')
    <nav class="p-6 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 flex justify-between items-center z-20 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="w-3 h-3 bg-cyan-500 rounded-full animate-pulse"></div>
            <div>
                <h2 class="text-lg font-black text-white uppercase tracking-widest">IP Manager (IPAM)</h2>
                <p class="text-cyan-400 text-[10px] mt-1 font-bold tracking-wide uppercase">IPv4 Network Allocation</p>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <button onclick="document.getElementById('modalAddIp').classList.remove('hidden')" class="bg-cyan-600/20 border border-cyan-500 text-cyan-400 hover:bg-cyan-600 hover:text-white px-5 py-2.5 rounded-xl font-bold transition flex items-center gap-2 text-sm shadow-[0_0_15px_rgba(6,182,212,0.2)]">
                + Daftarkan IP Baru
            </button>
        </div>
    </nav>

    <main class="flex-grow p-8 overflow-y-auto custom-scrollbar flex flex-col gap-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 shrink-0">
            <div class="glass-panel p-6 rounded-2xl border-t-4 border-slate-500 shadow-xl">
                <p class="text-xs text-slate-400 uppercase tracking-widest font-bold mb-1">Total IP Terdaftar</p>
                <p class="text-4xl font-black text-white">{{ $totalIp }}</p>
            </div>
            <div class="glass-panel p-6 rounded-2xl border-t-4 border-emerald-500 relative overflow-hidden shadow-xl">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl"></div>
                <p class="text-xs text-emerald-400 uppercase tracking-widest font-bold mb-1">Available (Kosong)</p>
                <p class="text-4xl font-black text-emerald-400">{{ $ipAvailable }}</p>
            </div>
            <div class="glass-panel p-6 rounded-2xl border-t-4 border-red-500 relative overflow-hidden shadow-xl">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-500/10 rounded-full blur-xl"></div>
                <p class="text-xs text-red-400 uppercase tracking-widest font-bold mb-1">In Use (Terpakai)</p>
                <p class="text-4xl font-black text-red-400">{{ $ipInUse }}</p>
            </div>
        </div>

        <div class="glass-panel rounded-2xl flex-grow overflow-hidden flex flex-col shadow-2xl border border-slate-700/50">
            <div class="p-4 border-b border-slate-700 flex justify-between items-center bg-slate-900/50">
                <div class="relative group w-72">
                    <input type="text" id="searchInput" placeholder="Cari IP / Nama Aset..." class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2.5 rounded-xl text-sm focus:border-cyan-500 focus:outline-none transition-all shadow-inner">
                    <span class="absolute right-3 top-2.5 text-slate-500">🔍</span>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-bold text-slate-500 uppercase">Show:</label>
                    <select onchange="window.location.href='?per_page=' + this.value" class="bg-slate-900 border border-slate-700 text-white px-2 py-2 rounded-xl text-xs focus:outline-none transition-all cursor-pointer">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>
            
            <div class="overflow-x-auto overflow-y-auto custom-scrollbar flex-grow">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="text-slate-300 uppercase tracking-widest text-[10px] border-b-2 border-slate-600 bg-slate-900 sticky top-0">
                        <tr>
                            <th class="p-4 font-bold pl-6">IP Address</th>
                            <th class="p-4 font-bold">Gateway</th>
                            <th class="p-4 font-bold">Status</th>
                            <th class="p-4 font-bold">Terhubung Ke Aset</th>
                            <th class="p-4 font-bold">Keterangan</th>
                            <th class="p-4 font-bold text-right pr-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="divide-y divide-slate-700/80">
                        @forelse($ips as $ip)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-4 pl-6 font-mono font-bold text-cyan-400 text-base">{{ $ip->ip_address }}</td>
                                <td class="p-4 font-mono text-slate-400">{{ $ip->gateway ?? '-' }}</td>
                                <td class="p-4">
                                    @if($ip->status == 'available')
                                        <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-900/50 text-emerald-400 border border-emerald-500/30">Available</span>
                                    @elseif($ip->status == 'in_use')
                                        <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider bg-red-900/50 text-red-400 border border-red-500/30">In Use</span>
                                    @else
                                        <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider bg-amber-900/50 text-amber-400 border border-amber-500/30">Reserved</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($ip->asset)
                                        <a href="{{ route('inventory.edit', $ip->asset->id) }}" class="font-bold text-blue-400 hover:text-blue-300 transition">{{ $ip->asset->asset_code }}</a>
                                        <span class="text-slate-500 text-xs ml-2">({{ $ip->asset->name }})</span>
                                    @else
                                        <span class="text-slate-600 italic">Belum disematkan</span>
                                    @endif
                                </td>
                                <td class="p-4 text-xs text-slate-400">{{ $ip->description ?? '-' }}</td>
                                <td class="p-4 pr-6 text-right space-x-2">
                                    <button onclick="openEditModal({{ $ip->id }}, '{{ $ip->ip_address }}', '{{ $ip->gateway }}', '{{ $ip->status }}', '{{ addslashes($ip->description) }}')" class="text-blue-400 hover:text-blue-300 font-bold px-3 py-1 transition text-xs border border-blue-900/50 rounded hover:bg-blue-900/20">Edit</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-10 text-center text-slate-500 font-bold border border-dashed border-slate-700 m-4 rounded-xl">Belum ada IP Address yang didaftarkan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-800 bg-slate-900/50 shrink-0">{{ $ips->links() }}</div>
        </div>
    </main>

    <div id="modalEditIp" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden flex justify-center items-center z-50">
        <div class="glass-panel p-8 rounded-2xl shadow-2xl border-t-4 border-blue-500 w-full max-w-md relative">
            <button onclick="document.getElementById('modalEditIp').classList.add('hidden')" class="absolute top-4 right-5 text-slate-500 hover:text-red-400 font-black text-xl transition">&times;</button>
            <h2 class="text-xl font-black text-white mb-6 uppercase tracking-widest">Update IP <span id="editIpTitle" class="text-blue-400"></span></h2>
            
            <form id="formEditIp" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">IPv4 Address *</label>
                    <input type="text" id="edit_ip_address" name="ip_address" required 
                           pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$" title="Format IP Address tidak valid!" oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                           class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg font-mono text-sm focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Gateway (Opsional)</label>
                    <input type="text" id="edit_gateway" name="gateway" 
                           pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$" title="Format Gateway tidak valid!" oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                           class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg font-mono text-sm focus:border-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Status *</label>
                    <select id="edit_status" name="status" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-blue-500 transition">
                        </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Keterangan (Opsional)</label>
                    <textarea id="edit_description" name="description" rows="2" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-blue-500 transition"></textarea>
                </div>
                <button type="submit" class="w-full mt-4 bg-blue-600/20 hover:bg-blue-600 text-blue-400 hover:text-white border border-blue-500/50 py-3 rounded-lg font-bold uppercase tracking-widest text-sm transition-all shadow-[0_0_15px_rgba(59,130,246,0.3)]">
                    Perbarui IP Address
                </button>
            </form>
        </div>
    </div>

    <div id="modalAddIp" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm hidden flex justify-center items-center z-50">
        <div class="glass-panel p-8 rounded-2xl shadow-2xl border-t-4 border-cyan-500 w-full max-w-md relative">
            <button onclick="document.getElementById('modalAddIp').classList.add('hidden')" class="absolute top-4 right-5 text-slate-500 hover:text-red-400 font-black text-xl transition">&times;</button>
            <h2 class="text-xl font-black text-white mb-6 uppercase tracking-widest">Daftarkan IP Baru</h2>
            
            <form action="{{ route('ipam.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">IPv4 Address *</label>
                    <input type="text" name="ip_address" required placeholder="192.168.1.10" 
                           pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$" title="Format IP Address tidak valid!" oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                           class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg font-mono text-sm focus:border-cyan-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Gateway (Opsional)</label>
                    <input type="text" name="gateway" placeholder="192.168.1.1" 
                           pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$" title="Format Gateway tidak valid!" oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                           class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg font-mono text-sm focus:border-cyan-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Status Awal *</label>
                    <select name="status" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-cyan-500 transition">
                        <option value="available">🟢 Available (Tersedia)</option>
                        <option value="reserved">🟡 Reserved (Dipesan Khusus)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">Keterangan (Opsional)</label>
                    <textarea name="description" rows="2" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-2 rounded-lg text-sm focus:border-cyan-500 transition" placeholder="Cth: IP khusus untuk Direksi"></textarea>
                </div>
                <button type="submit" class="w-full mt-4 bg-cyan-600/20 hover:bg-cyan-600 text-cyan-400 hover:text-white border border-cyan-500/50 py-3 rounded-lg font-bold uppercase tracking-widest text-sm transition-all shadow-[0_0_15px_rgba(6,182,212,0.3)]">
                    Simpan IP Address
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showToast(msg, colorClass) {
        const t = document.getElementById('toast');
        if(t) {
            t.innerText = msg;
            t.className = `fixed top-6 right-6 px-6 py-4 rounded-xl shadow-2xl transition-all duration-300 z-[9999] font-bold text-sm text-white transform translate-y-0 opacity-100 ${colorClass}`;
            setTimeout(() => { t.classList.remove('translate-y-0', 'opacity-100'); t.classList.add('-translate-y-4', 'opacity-0'); }, 4000);
        }
    }

    @if(session('success')) showToast("{{ session('success') }}", 'bg-emerald-600'); @endif
    @if(session('error')) showToast("{!! addslashes(session('error')) !!}", 'bg-red-600'); @endif

    // Search IP
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#tableBody tr');
        rows.forEach(row => {
            if(row.innerText.includes("Belum ada IP")) return;
            row.style.display = row.innerText.toLowerCase().includes(filter) ? '' : 'none';
        });
    });

    

    // Modal Edit
    function openEditModal(id, ip, gateway, status, description) {
        document.getElementById('formEditIp').action = `/inventory/ipam/${id}`;
        document.getElementById('editIpTitle').innerText = ip;
        document.getElementById('edit_ip_address').value = ip;
        document.getElementById('edit_gateway').value = gateway !== '-' ? gateway : '';
        document.getElementById('edit_description').value = description !== '-' ? description : '';
        
        let statusDropdown = document.getElementById('edit_status');
        // Kuncian keamanan "In Use" tetap dipertahankan karena ini adalah standar Enterprise!
        // IP yang sudah terikat tidak boleh diubah manual dari IPAM, harus dilepas dari asetnya.
        if (status === 'in_use') {
            statusDropdown.innerHTML = '<option value="in_use" selected>🔴 In Use (Dikunci Sistem)</option>';
            statusDropdown.setAttribute('readonly', true);
            statusDropdown.style.pointerEvents = 'none';
        } else {
            statusDropdown.removeAttribute('readonly');
            statusDropdown.style.pointerEvents = 'auto';
            statusDropdown.innerHTML = `
                <option value="available" ${status === 'available' ? 'selected' : ''}>🟢 Available (Tersedia)</option>
                <option value="reserved" ${status === 'reserved' ? 'selected' : ''}>🟡 Reserved (Dipesan Khusus)</option>
            `;
        }
        document.getElementById('modalEditIp').classList.remove('hidden');
    }
</script>
@endpush