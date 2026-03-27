@extends('layouts.app')

@section('content')
    <div x-data="{
        tab: '{{ session('active_tab', 'users') }}',
        showModal: false,
        showEditModal: false,
        showTarifModal: false,
        showAddTarifModal: false,
        showAddAreaModal: false,
        showEditAreaModal: false,
        editData: { id: '', name: '', username: '', role: '' },
        tarifData: { id: '', jenis: '', harga: '' },
        areaData: { id: '', nama: '', kapasitas: '' },
        editAction: '',
        tarifAction: '',
        areaAction: ''
    }" class="min-h-screen bg-[#FFF9F2] -m-8 p-8 font-sans">

        {{-- NAVBAR --}}
        <div class="w-full bg-white px-8 py-4 flex justify-between items-center shadow-sm mb-8 border-b border-gray-100 rounded-3xl">
            <div class="flex items-center gap-2">
                <div class="bg-[#FF9F1C] p-2 rounded-xl text-white font-bold text-2xl w-12 h-12 flex items-center justify-center shadow-md">P</div>
                <span class="text-2xl font-bold text-gray-800">Arkiest</span>
            </div>
            <div class="flex items-center gap-6">
                <div class="text-right hidden md:block">
                    <p class="text-[10px] font-black text-orange-400 uppercase tracking-widest leading-none mb-1">Admin Aktif</p>
                    <p class="font-bold text-gray-800 italic">{{ auth()->user()->nama_lengkap }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-50 text-red-500 px-6 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-red-500 hover:text-white transition-all shadow-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        {{-- STATS CARDS --}}
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-gradient-to-br from-[#2D3436] to-[#000000] p-6 rounded-[2rem] shadow-xl text-white group hover:scale-[1.02] transition-transform">
                <div class="flex justify-between items-center opacity-60 text-[10px] font-black uppercase tracking-widest">
                    <span>Pendapatan Hari Ini</span>
                    <i class="fas fa-coins text-lg"></i>
                </div>
                <h2 class="text-2xl font-black mt-2">Rp {{ number_format($totalPendapatanHariIni, 0, ',', '.') }}</h2>
            </div>
            <div class="bg-gradient-to-br from-amber-400 to-orange-500 p-6 rounded-[2rem] shadow-xl shadow-amber-200 text-white group hover:scale-[1.02] transition-transform">
                <div class="flex justify-between items-center opacity-80 text-[10px] font-black uppercase tracking-widest">
                    <span>Total Petugas</span>
                    <i class="fas fa-user-shield text-lg"></i>
                </div>
                <h2 class="text-4xl font-black mt-2">{{ count($users) }}</h2>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-amber-100 group hover:scale-[1.02] transition-transform">
                <div class="flex justify-between items-center text-amber-600 opacity-80 text-[10px] font-black uppercase tracking-widest">
                    <span>Sisa Slot Parkir</span>
                    <i class="fas fa-parking text-lg"></i>
                </div>
                <h2 class="text-4xl font-black mt-2 text-gray-800">{{ $totalSlotKosong }} <span class="text-sm font-bold text-gray-400">/ {{ $totalKapasitas }}</span></h2>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-amber-100 group hover:scale-[1.02] transition-transform">
                <div class="flex justify-between items-center text-amber-600 opacity-80 text-[10px] font-black uppercase tracking-widest">
                    <span>Transaksi Hari Ini</span>
                    <i class="fas fa-exchange-alt text-lg"></i>
                </div>
                <h2 class="text-4xl font-black mt-2 text-gray-800">{{ $totalLogsCount }}</h2>
            </div>
        </div>

        {{-- NAVIGATION TABS --}}
        <div class="max-w-7xl mx-auto px-4 flex flex-wrap gap-2 mb-8 bg-amber-100/30 p-2 rounded-3xl w-fit border border-amber-100">
            <button @click="tab = 'users'" :class="tab === 'users' ? 'bg-amber-400 text-white shadow-lg' : 'text-amber-700 hover:bg-white'" class="px-6 py-3 rounded-2xl font-bold text-sm transition-all duration-300">
                <i class="fas fa-users mr-2"></i> Manajemen User
            </button>
            <button @click="tab = 'tarif'" :class="tab === 'tarif' ? 'bg-amber-400 text-white shadow-lg' : 'text-amber-700 hover:bg-white'" class="px-6 py-3 rounded-2xl font-bold text-sm transition-all duration-300">
                <i class="fas fa-tags mr-2"></i> Tarif & Area
            </button>
            <button @click="tab = 'logs'" :class="tab === 'logs' ? 'bg-amber-400 text-white shadow-lg' : 'text-amber-700 hover:bg-white'" class="px-6 py-3 rounded-2xl font-bold text-sm transition-all duration-300">
                <i class="fas fa-history mr-2"></i> Log Aktivitas
            </button>
        </div>

        {{-- TAB CONTENT: USERS --}}
        <div x-show="tab === 'users'" x-transition class="max-w-7xl mx-auto bg-white rounded-[2.5rem] shadow-sm border border-amber-100 overflow-hidden mb-12">
            <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">User Management</h3>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Kontrol Petugas & Status Akun</p>
                </div>
                <button @click="showModal = true" class="bg-amber-400 hover:bg-amber-500 text-white px-6 py-3 rounded-2xl font-bold text-sm transition-all shadow-md active:scale-95">+ TAMBAH AKUN</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-amber-50 text-amber-800 text-[10px] uppercase font-black tracking-widest">
                        <tr>
                            <th class="px-8 py-5">Identitas</th>
                            <th class="px-8 py-5 text-center">Role</th>
                            <th class="px-8 py-5 text-center">Status</th>
                            <th class="px-8 py-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($users as $user)
                            <tr class="hover:bg-amber-50/30 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-800">{{ $user->nama_lengkap }}</span>
                                        <span class="text-xs text-gray-400">{{ $user->username }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="px-3 py-1 rounded-full bg-white text-gray-600 text-[10px] font-black uppercase tracking-widest border border-gray-200">{{ $user->role }}</span>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $user->status_aktif ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                        {{ $user->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-3">
                                        <form action="{{ route('admin.users.toggle', $user->id_user) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-xl transition-all shadow-sm {{ $user->status_aktif ? 'bg-red-50 text-red-500 hover:bg-red-500 hover:text-white' : 'bg-green-50 text-green-500 hover:bg-green-500 hover:text-white' }}">
                                                <i class="fas {{ $user->status_aktif ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                            </button>
                                        </form>
                                        <button @click="showEditModal = true; editData = { id: '{{ $user->id_user }}', name: '{{ $user->nama_lengkap }}', username: '{{ $user->username }}', role: '{{ $user->role }}' }; editAction = '/admin/users/{{ $user->id_user }}'" class="w-10 h-10 flex items-center justify-center rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB CONTENT: TARIF & AREA --}}
        <div x-show="tab === 'tarif'" x-transition class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            {{-- SEKSI TARIF --}}
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-amber-100">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800"><i class="fas fa-coins text-amber-500 mr-2"></i> Pengaturan Tarif</h3>
                    <button @click="showAddTarifModal = true" class="bg-amber-400 hover:bg-amber-500 text-white px-4 py-2 rounded-xl font-bold text-xs transition-all shadow-md">+ TAMBAH</button>
                </div>
                <div class="space-y-4">
                    @foreach ($tarifs as $tarif)
                        <div class="flex items-center justify-between p-5 bg-[#FFF9F2] rounded-3xl border border-amber-100 group hover:border-amber-400 transition-all">
                            <div>
                                <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest">{{ $tarif->jenis_kendaraan }}</p>
                                <p class="text-xl font-bold text-gray-800">Rp {{ number_format($tarif->tarif_per_jam, 0, ',', '.') }}<span class="text-xs text-gray-400 font-normal">/jam</span></p>
                            </div>
                            <button @click="showTarifModal = true; tarifData = { id: '{{ $tarif->id_tarif }}', jenis: '{{ $tarif->jenis_kendaraan }}', harga: '{{ $tarif->tarif_per_jam }}' }; tarifAction = '/admin/tarifs/{{ $tarif->id_tarif }}'" class="bg-white p-3 rounded-2xl shadow-sm text-amber-500 hover:bg-amber-400 hover:text-white transition-all border border-amber-50">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- SEKSI AREA PARKIR --}}
            <div class="bg-[#2D3436] p-8 rounded-[2.5rem] shadow-xl text-white">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-amber-400"><i class="fas fa-layer-group mr-2"></i> Area Parkir</h3>
                    <button @click="showAddAreaModal = true" class="bg-amber-400 hover:bg-amber-500 text-black px-4 py-2 rounded-xl font-bold text-xs transition-all shadow-md">+ TAMBAH</button>
                </div>
                <div class="space-y-4">
                    @foreach ($areas as $area)
                        @php
                            $persenTerisi = $area->kapasitas > 0 ? ($area->terisi / $area->kapasitas) * 100 : 0;
                            $slotKosong = $area->kapasitas - $area->terisi;
                            $isDisabled = $area->kapasitas == 0;
                            $barColor = $isDisabled ? 'bg-gray-500' : ($persenTerisi > 85 ? 'bg-red-500' : ($persenTerisi > 50 ? 'bg-orange-500' : 'bg-emerald-500'));
                        @endphp
                        <div class="p-4 rounded-2xl {{ $isDisabled ? 'bg-gray-700/50 opacity-60' : 'bg-white/5' }} border border-white/10">
                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <p class="text-sm font-bold {{ $isDisabled ? 'text-gray-400 line-through' : 'text-gray-200' }}">{{ $area->nama_area }}</p>
                                    <p class="text-[10px] text-gray-400 uppercase font-black">{{ $isDisabled ? 'NONAKTIF' : $slotKosong . ' Slot Kosong / ' . $area->kapasitas . ' Total' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if(!$isDisabled)
                                    <span class="text-sm font-black {{ $persenTerisi > 90 ? 'text-red-400' : 'text-amber-400' }}">{{ round($persenTerisi) }}%</span>
                                    @endif
                                    <button @click="showEditAreaModal = true; areaData = { id: '{{ $area->id_area }}', nama: '{{ $area->nama_area }}', kapasitas: '{{ $area->kapasitas }}' }; areaAction = '/admin/areas/{{ $area->id_area }}'" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 text-amber-400 hover:bg-amber-400 hover:text-black transition-all text-xs">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.areas.toggle', $area->id_area) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg transition-all text-xs {{ $isDisabled ? 'bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500 hover:text-white' : 'bg-red-500/20 text-red-400 hover:bg-red-500 hover:text-white' }}">
                                            <i class="fas {{ $isDisabled ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @if(!$isDisabled)
                            <div class="w-full bg-gray-700 rounded-full h-2 overflow-hidden">
                                <div class="{{ $barColor }} h-full transition-all duration-500 ease-out" style="width: {{ $persenTerisi }}%"></div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- TAB CONTENT: LOG AKTIVITAS --}}
        <div x-show="tab === 'logs'" x-transition class="max-w-7xl mx-auto bg-white rounded-[2.5rem] shadow-sm border border-amber-100 overflow-hidden mb-12">
            <div class="p-8 border-b border-gray-50 bg-gray-50/50">
                <h3 class="text-xl font-bold text-gray-800">Log Aktivitas Sistem</h3>
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Riwayat semua transaksi terbaru</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-amber-50 text-amber-800 text-[10px] uppercase font-black tracking-widest">
                        <tr>
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Plat Nomor</th>
                            <th class="px-6 py-4">Jenis</th>
                            <th class="px-6 py-4">Area</th>
                            <th class="px-6 py-4">Waktu Masuk</th>
                            <th class="px-6 py-4">Waktu Keluar</th>
                            <th class="px-6 py-4 text-right">Biaya</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($logs as $i => $log)
                        <tr class="hover:bg-amber-50/30 transition-colors">
                            <td class="px-6 py-4 text-xs text-gray-400 font-bold">{{ $i + 1 }}</td>
                            <td class="px-6 py-4 font-black text-gray-800 tracking-wider">{{ $log->kendaraan->plat_nomor ?? '-' }}</td>
                            <td class="px-6 py-4 text-xs text-gray-500 uppercase font-bold">{{ $log->kendaraan->jenis_kendaraan ?? '-' }}</td>
                            <td class="px-6 py-4 text-xs text-gray-500 font-bold">{{ $log->area->nama_area ?? '-' }}</td>
                            <td class="px-6 py-4 text-xs text-gray-500">{{ \Carbon\Carbon::parse($log->waktu_masuk)->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-xs text-gray-500">{{ $log->waktu_keluar ? \Carbon\Carbon::parse($log->waktu_keluar)->format('d/m/Y H:i') : '-' }}</td>
                            <td class="px-6 py-4 text-right text-xs font-bold text-gray-800">{{ $log->biaya_total ? 'Rp ' . number_format($log->biaya_total, 0, ',', '.') : '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $log->status == 'masuk' ? 'bg-blue-100 text-blue-600' : 'bg-emerald-100 text-emerald-600' }}">
                                    {{ $log->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500 font-bold">{{ $log->user->nama_lengkap ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="px-6 py-12 text-center text-gray-400 text-sm italic">Belum ada transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODAL: TAMBAH USER --}}
        <div x-show="showModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showModal = false"></div>
                <div class="relative bg-white rounded-[2.5rem] w-full max-w-md p-10 shadow-2xl border border-amber-100">
                    <h3 class="text-2xl font-black text-gray-800 text-center mb-8 uppercase tracking-tighter">Tambah User Baru</h3>
                    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" required class="w-full px-5 py-4 rounded-2xl bg-amber-50 border-none focus:ring-2 focus:ring-amber-400 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Username</label>
                            <input type="text" name="username" required class="w-full px-5 py-4 rounded-2xl bg-amber-50 border-none focus:ring-2 focus:ring-amber-400 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Role</label>
                            <select name="role" class="w-full px-5 py-4 rounded-2xl bg-amber-50 border-none font-bold text-gray-700">
                                <option value="petugas">PETUGAS</option>
                                <option value="owner">OWNER</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Password</label>
                            <input type="password" name="password" required class="w-full px-5 py-4 rounded-2xl bg-amber-50 border-none focus:ring-2 focus:ring-amber-400 font-bold">
                        </div>
                        <div class="flex gap-3 pt-6">
                            <button type="button" @click="showModal = false" class="flex-1 py-4 font-black text-gray-400 uppercase text-xs">Batal</button>
                            <button type="submit" class="flex-1 py-4 bg-amber-400 text-white rounded-2xl font-black shadow-lg shadow-amber-200 uppercase text-xs tracking-widest">Simpan Akun</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL: EDIT USER --}}
        <div x-show="showEditModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showEditModal = false"></div>
                <div class="relative bg-white rounded-[2.5rem] w-full max-w-md p-10 shadow-2xl border border-amber-100">
                    <h3 class="text-2xl font-black text-gray-800 text-center mb-8 uppercase tracking-tighter">Edit User</h3>
                    <form :action="editAction" method="POST" class="space-y-5">
                        @csrf @method('PUT')
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" x-model="editData.name" required class="w-full px-5 py-4 rounded-2xl bg-amber-50 border-none focus:ring-2 focus:ring-amber-400 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Username</label>
                            <input type="text" name="username" x-model="editData.username" required class="w-full px-5 py-4 rounded-2xl bg-amber-50 border-none focus:ring-2 focus:ring-amber-400 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Role</label>
                            <select name="role" x-model="editData.role" class="w-full px-5 py-4 rounded-2xl bg-amber-50 border-none font-bold text-gray-700">
                                <option value="petugas">PETUGAS</option>
                                <option value="owner">OWNER</option>
                            </select>
                        </div>
                        <div class="flex gap-3 pt-6">
                            <button type="button" @click="showEditModal = false" class="flex-1 py-4 font-black text-gray-400 uppercase text-xs">Batal</button>
                            <button type="submit" class="flex-1 py-4 bg-orange-500 text-white rounded-2xl font-black shadow-lg shadow-orange-200 uppercase text-xs tracking-widest">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL: EDIT TARIF --}}
        <div x-show="showTarifModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showTarifModal = false"></div>
                <div class="relative bg-white rounded-[2.5rem] w-full max-w-md p-10 shadow-2xl border border-amber-100">
                    <h3 class="text-2xl font-black text-gray-800 text-center mb-8 uppercase tracking-tighter">Edit Tarif</h3>
                    <form :action="tarifAction" method="POST" class="space-y-5">
                        @csrf @method('PUT')
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Jenis Kendaraan</label>
                            <input type="text" x-model="tarifData.jenis" readonly class="w-full px-5 py-4 rounded-2xl bg-gray-100 border-none font-bold text-gray-400">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Harga per Jam</label>
                            <input type="number" name="tarif_per_jam" x-model="tarifData.harga" required class="w-full px-5 py-4 rounded-2xl bg-amber-50 border-none focus:ring-2 focus:ring-amber-400 font-bold">
                        </div>
                        <div class="flex gap-3 pt-6">
                            <button type="button" @click="showTarifModal = false" class="flex-1 py-4 font-black text-gray-400 uppercase text-xs">Batal</button>
                            <button type="submit" class="flex-1 py-4 bg-amber-400 text-white rounded-2xl font-black shadow-lg shadow-amber-200 uppercase text-xs tracking-widest">Simpan Tarif</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL: TAMBAH TARIF --}}
        <div x-show="showAddTarifModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showAddTarifModal = false"></div>
                <div class="relative bg-white rounded-[2.5rem] w-full max-w-md p-10 shadow-2xl border border-amber-100">
                    <h3 class="text-2xl font-black text-gray-800 text-center mb-8 uppercase tracking-tighter">Tambah Tarif Baru</h3>
                    <form action="{{ route('admin.tarifs.store') }}" method="POST" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Jenis Kendaraan</label>
                            <input type="text" name="jenis_kendaraan" required placeholder="Contoh: Truk" class="w-full px-5 py-4 rounded-2xl bg-amber-50 border-none focus:ring-2 focus:ring-amber-400 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Harga per Jam (Rp)</label>
                            <input type="number" name="tarif_per_jam" required placeholder="5000" class="w-full px-5 py-4 rounded-2xl bg-amber-50 border-none focus:ring-2 focus:ring-amber-400 font-bold">
                        </div>
                        <div class="flex gap-3 pt-6">
                            <button type="button" @click="showAddTarifModal = false" class="flex-1 py-4 font-black text-gray-400 uppercase text-xs">Batal</button>
                            <button type="submit" class="flex-1 py-4 bg-amber-400 text-white rounded-2xl font-black shadow-lg shadow-amber-200 uppercase text-xs tracking-widest">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL: TAMBAH AREA --}}
        <div x-show="showAddAreaModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showAddAreaModal = false"></div>
                <div class="relative bg-white rounded-[2.5rem] w-full max-w-md p-10 shadow-2xl border border-amber-100">
                    <h3 class="text-2xl font-black text-gray-800 text-center mb-8 uppercase tracking-tighter">Tambah Area Baru</h3>
                    <form action="{{ route('admin.areas.store') }}" method="POST" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Nama Area</label>
                            <input type="text" name="nama_area" required placeholder="Contoh: Lantai 3" class="w-full px-5 py-4 rounded-2xl bg-amber-50 border-none focus:ring-2 focus:ring-amber-400 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Kapasitas (Slot)</label>
                            <input type="number" name="kapasitas" required min="1" placeholder="50" class="w-full px-5 py-4 rounded-2xl bg-amber-50 border-none focus:ring-2 focus:ring-amber-400 font-bold">
                        </div>
                        <div class="flex gap-3 pt-6">
                            <button type="button" @click="showAddAreaModal = false" class="flex-1 py-4 font-black text-gray-400 uppercase text-xs">Batal</button>
                            <button type="submit" class="flex-1 py-4 bg-amber-400 text-white rounded-2xl font-black shadow-lg shadow-amber-200 uppercase text-xs tracking-widest">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL: EDIT AREA --}}
        <div x-show="showEditAreaModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showEditAreaModal = false"></div>
                <div class="relative bg-white rounded-[2.5rem] w-full max-w-md p-10 shadow-2xl border border-amber-100">
                    <h3 class="text-2xl font-black text-gray-800 text-center mb-8 uppercase tracking-tighter">Edit Area</h3>
                    <form :action="areaAction" method="POST" class="space-y-5">
                        @csrf @method('PUT')
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Nama Area</label>
                            <input type="text" name="nama_area" x-model="areaData.nama" required class="w-full px-5 py-4 rounded-2xl bg-amber-50 border-none focus:ring-2 focus:ring-amber-400 font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-amber-600 uppercase mb-2 ml-2 tracking-widest">Kapasitas (Slot)</label>
                            <input type="number" name="kapasitas" x-model="areaData.kapasitas" required min="1" class="w-full px-5 py-4 rounded-2xl bg-amber-50 border-none focus:ring-2 focus:ring-amber-400 font-bold">
                        </div>
                        <div class="flex gap-3 pt-6">
                            <button type="button" @click="showEditAreaModal = false" class="flex-1 py-4 font-black text-gray-400 uppercase text-xs">Batal</button>
                            <button type="submit" class="flex-1 py-4 bg-orange-500 text-white rounded-2xl font-black shadow-lg shadow-orange-200 uppercase text-xs tracking-widest">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
