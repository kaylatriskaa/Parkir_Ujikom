@extends('layouts.app')

@section('content')
    <div x-data="{
        tab: 'users',
        showModal: false,
        showEditModal: false,
        showTarifModal: false,
        editData: { id: '', name: '', username: '', role: '' },
        tarifData: { id: '', jenis: '', harga: '' },
        editAction: '',
        tarifAction: ''
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
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
            <div class="bg-gradient-to-br from-amber-400 to-orange-500 p-6 rounded-[2rem] shadow-xl shadow-amber-200 text-white group hover:scale-[1.02] transition-transform">
                <div class="flex justify-between items-center opacity-80 text-xs font-bold uppercase">
                    <span>Total Petugas</span>
                    <i class="fas fa-user-shield text-2xl"></i>
                </div>
                <h2 class="text-4xl font-black mt-2">{{ count($users) }}</h2>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-amber-100 group hover:scale-[1.02] transition-transform">
                <div class="flex justify-between items-center text-amber-600 opacity-80 text-xs font-bold uppercase">
                    <span>Area Parkir</span>
                    <i class="fas fa-parking text-2xl"></i>
                </div>
                <h2 class="text-4xl font-black mt-2 text-gray-800">{{ count($areas) }}</h2>
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

        {{-- TAB CONTENT: TARIF & MONITORING AREA --}}
        <div x-show="tab === 'tarif'" x-transition class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            {{-- SEKSI TARIF --}}
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-amber-100">
                <h3 class="text-xl font-bold text-gray-800 mb-6 "><i class="fas fa-coins text-amber-500 mr-2"></i> Pengaturan Tarif</h3>
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

            {{-- SEKSI MONITORING AREA --}}
            <div class="bg-[#2D3436] p-8 rounded-[2.5rem] shadow-xl text-white">
                <h3 class="text-xl font-bold mb-6  text-amber-400"><i class="fas fa-layer-group mr-2"></i> Monitoring Area</h3>
                <div class="space-y-8">
                    @foreach ($areas as $area)
                        @php
                            $persenTerisi = ($area->terisi / max(1, $area->kapasitas)) * 100;
                            $slotKosong = $area->kapasitas - $area->terisi;
                            $barColor = $persenTerisi > 85 ? 'bg-red-500' : ($persenTerisi > 50 ? 'bg-orange-500' : 'bg-emerald-500');
                        @endphp
                        <div class="mb-6">
                            <div class="flex justify-between items-end mb-2">
                                <div>
                                    <p class="text-sm font-bold text-gray-200">{{ $area->nama_area }}</p>
                                    <p class="text-[10px] text-gray-400 uppercase font-black">{{ $slotKosong }} Slot Kosong / {{ $area->kapasitas }} Total</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-lg font-black {{ $persenTerisi > 90 ? 'text-red-400' : 'text-amber-400' }}">{{ round($persenTerisi) }}%</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-3 overflow-hidden shadow-inner">
                                <div class="{{ $barColor }} h-full transition-all duration-500 ease-out" style="width: {{ $persenTerisi }}%"></div>
                            </div>
                            <p class="text-[9px] mt-1 text-gray-500 italic font-bold uppercase tracking-wider">{{ $area->terisi }} Kendaraan Terparkir</p>
                        </div>
                    @endforeach
                </div>
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
    </div>
@endsection
