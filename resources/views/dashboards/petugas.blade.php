<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petugas Dashboard | Parkiest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8F1E7;
        }

        [x-cloak] {
            display: none !important;
        }

        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</head>

<body x-data="{
    tab: '{{ session('active_tab', request('tanggal') ? 'aktivitas' : 'masuk') }}',
    showModal: false,
    inputPlat: '{{ session('terbayar') ?? '' }}',
    platTerbayar: '{{ session('terbayar') ?? '' }}',
    inputBayar: 0,
    totalTagihan: {{ session('total_bayar', 0) }},
    statusBayar: '{{ session('terbayar') ? 'PAID' : 'UNPAID' }}',
    foundData: null,
    listKendaraan: {{ $kendaraanAktif->toJson() }},

    init() {
        // Kalau ada session terbayar, langsung jalankan pencarian
        if (this.inputPlat) { this.cariKendaraan(); }
    },

    cariKendaraan() {
        let inputClean = this.inputPlat.replace(/\s/g, '').toUpperCase();

        // 1. Reset data pencarian setiap kali ngetik
        this.foundData = null;
        this.statusBayar = 'UNPAID';

        if (!inputClean) return;

        // 2. Cek apakah plat ini baru saja dibayar (Status PAID)
        if (this.platTerbayar && inputClean === this.platTerbayar.replace(/\s/g, '').toUpperCase()) {
            this.statusBayar = 'PAID';
            // Kita tidak boleh return di sini, kita tetap butuh cari datanya di list
            // atau set data manual agar struk tidak kosong.
        }

        // 3. Cari data kendaraan di list aktif
        let match = this.listKendaraan.find(k => k.plat_nomor.replace(/\s/g, '').toUpperCase() === inputClean);

        if (match) {
            this.foundData = match;
            let masuk = new Date(match.jam_masuk);
            let sekarang = new Date();
            this.foundData.waktu_masuk_format = masuk.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });

            // Hitung durasi
            let selisihMs = sekarang - masuk;
            let selisihJam = Math.ceil(selisihMs / (1000 * 60 * 60));
            this.foundData.durasi = selisihJam > 0 ? selisihJam : 1;

            // Set total tagihan (kecuali kalau sudah PAID, pakai yang dari session)
            if (this.statusBayar !== 'PAID') {
                this.totalTagihan = this.foundData.durasi * parseInt(match.harga_per_jam);
            }
        } else if (this.statusBayar === 'PAID') {
            // Jika data sudah lunas tapi tidak ada di listAktif (karena sudah pindah status di DB)
            // Kita buatkan data bayangan supaya struk tetap terisi
            this.foundData = {
                waktu_masuk_format: '--:--',
                durasi: '--'
            };
            this.totalTagihan = {{ session('total_bayar', 0) }};
        }
    },
    formatRupiah(number) { return new Intl.NumberFormat('id-ID').format(number); }
}">

    <div class="w-full bg-white px-8 py-4 flex justify-between items-center shadow-sm mb-8 border-b border-gray-100">
        <div class="flex items-center gap-4">
            <div
                class="bg-[#FF9F1C] p-2 rounded-xl text-white font-bold text-2xl w-12 h-12 flex items-center justify-center shadow-md">
                P</div>
            <span class="text-3xl font-black text-gray-800 tracking-tighter">Arkiest</span>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-right">
                <p class="text-[10px] font-black text-orange-400 uppercase tracking-widest leading-none mb-1">Petugas
                    Aktif</p>
                <p class="font-bold text-gray-800 italic">{{ auth()->user()->name }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="bg-red-50 text-red-500 px-6 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-red-500 hover:text-white transition-all">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 pb-12">
        <div
            class="bg-[#EEBB4D] rounded-[3rem] py-10 px-16 mb-10 shadow-xl flex justify-between items-center text-white border-4 border-white/20">
            <div>
                <p class="font-bold opacity-80  text-sm">Waktu Sekarang :</p>
                <h2 id="clock" class="text-6xl font-black tracking-tighter">00:00:00</h2>
            </div>
            <div class="text-right">
                <p class="font-bold opacity-80 text-sm">Tanggal Hari Ini :</p>
                <h2 id="current-date" class="text-4xl font-black tracking-tighter">--/--/----</h2>
            </div>
        </div>

        <div
            class="flex flex-wrap gap-4 mb-10 justify-center md:justify-start bg-amber-100/30 p-2 rounded-3xl w-fit border border-amber-100">
            <button @click="tab = 'masuk'"
                :class="tab === 'masuk' ? 'bg-[#FF9F1C] text-white shadow-lg' : 'text-amber-700'"
                class="px-8 py-3 rounded-2xl font-bold text-sm transition-all uppercase">
                <i class="fas fa-sign-in-alt mr-2"></i> Masuk
            </button>
            <button @click="tab = 'keluar'"
                :class="tab === 'keluar' ? 'bg-red-500 text-white shadow-lg' : 'text-amber-700'"
                class="px-8 py-3 rounded-2xl font-bold text-sm transition-all uppercase">
                <i class="fas fa-sign-out-alt mr-2"></i> Keluar
            </button>
            <button @click="tab = 'aktivitas'"
                :class="tab === 'aktivitas' ? 'bg-[#2D3436] text-white shadow-lg' : 'text-amber-700'"
                class="px-8 py-3 rounded-2xl font-bold text-sm transition-all uppercase">
                <i class="fas fa-history mr-2"></i> Aktivitas
            </button>
            <button @click="tab = 'area'"
                :class="tab === 'area' ? 'bg-[#2D3436] text-white shadow-lg' : 'text-amber-700'"
                class="px-8 py-3 rounded-2xl font-bold text-sm transition-all uppercase">
                <i class="fas fa-th-large mr-2"></i> Area
            </button>
        </div>

        <div x-show="tab === 'masuk'" x-transition x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch">
                <div class="bg-[#FF9F1C] p-8 md:p-12 rounded-[3.5rem] shadow-2xl flex flex-col justify-center">
                    <h3 class="text-2xl font-black text-white mb-10 text-center uppercase ">Input Kendaraan Masuk
                    </h3>
                    <form action="{{ route('petugas.masuk') }}" method="POST" class="space-y-6">
                        @csrf
                        <select name="tarif_id" id="tarif_select" required
                            class="w-full px-6 py-4 rounded-2xl font-bold text-gray-700 shadow-lg outline-none">
                            <option value="" disabled selected>— Pilih Jenis Kendaraan —</option>
                            @foreach ($tarifs as $tarif)
                                <option value="{{ $tarif->id }}" data-jenis="{{ $tarif->jenis_kendaraan }}">
                                    {{ $tarif->jenis_kendaraan }}</option>
                            @endforeach
                        </select>
                        <select name="area_id" id="area_select" required
                            class="w-full px-6 py-4 rounded-2xl font-bold text-gray-700 shadow-lg outline-none">
                            <option value="" disabled selected>— Pilih Area Parkir —</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->area_id }}" data-nama="{{ $area->nama_area }}">
                                    {{ $area->nama_area }} (Tersedia: {{ $area->slot_tersedia }})</option>
                            @endforeach
                        </select>
                        <input type="text" name="plat_nomor" id="plat_input" placeholder="B 1234 ABC" required
                            class="w-full px-8 py-5 rounded-2xl font-black text-3xl uppercase text-center text-gray-800 shadow-inner outline-none">
                        <button type="submit"
                            class="w-full py-6 bg-[#2D3436] text-white rounded-2xl font-black text-xl shadow-2xl hover:bg-black transition-all uppercase">CETAK
                            KARCIS</button>
                    </form>
                </div>
                <div class="bg-white rounded-[3.5rem] shadow-xl overflow-hidden border-4 border-white flex flex-col">
                    <div class="bg-[#2D3436] py-4 text-center text-white text-xs font-black uppercase">Preview Karcis
                        Masuk</div>
                    <div class="p-8 bg-gray-50 flex justify-center items-center flex-grow">
                        <div id="struk_preview"
                            class="w-full max-w-[300px] bg-white p-8 border-2 border-dashed border-orange-300 font-mono text-gray-800">
                            <div class="text-center font-black text-xl mb-4 text-[#FF9F1C]">PARKIEST</div>
                            <div class="text-[10px] space-y-2">
                                <div class="flex justify-between"><span>Tgl:</span><span>{{ date('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between"><span>Jam:</span><span
                                        id="p_jam">{{ date('H:i:s') }}</span></div>
                                <div class="border-t border-gray-100 my-2"></div>
                                <div id="p_plat"
                                    class="text-center font-black text-2xl py-2 bg-gray-50 uppercase tracking-widest">
                                    -------</div>
                                <div class="flex justify-between mt-4"><span>Jenis:</span><span id="p_jenis">-</span>
                                </div>
                                <div class="flex justify-between"><span>Area:</span><span id="p_area">-</span></div>
                                <div class="border-t border-gray-100 my-2"></div>
                                <div class="text-center opacity-50 ">Selamat Datang</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="tab === 'keluar'" x-transition x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch">
                <div class="bg-red-500 p-8 md:p-12 rounded-[3.5rem] shadow-2xl flex flex-col justify-center">
                    <h3 class="text-2xl font-black text-white mb-10 text-center uppercase">Input Kendaraan Keluar
                    </h3>
                    <div class="space-y-6">
                        <input type="text" x-model="inputPlat" @input="cariKendaraan()"
                            placeholder="MASUKKAN PLAT NOMOR"
                            class="w-full px-8 py-5 rounded-2xl font-black text-3xl uppercase text-center text-gray-800 shadow-inner outline-none">
                        <div
                            class="bg-white/20 p-6 rounded-2xl text-white font-bold space-y-2 min-h-[100px] flex flex-col justify-center text-center">
                            <template x-if="foundData">
                                <div>
                                    <div class="flex justify-between text-sm opacity-90"><span>Waktu Masuk:</span><span
                                            x-text="foundData.waktu_masuk_format"></span></div>
                                    <div class="flex justify-between border-t border-white/20 pt-2 mt-2 text-lg">
                                        <span>Total Durasi:</span><span x-text="foundData.durasi + ' Jam'"></span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="statusBayar === 'PAID'">
                                <div>
                                    <p class="font-black text-green-300 uppercase">BERHASIL DIBAYAR!</p>
                                    <p class="text-xs italic opacity-80">Silahkan arahkan kendaraan keluar.</p>
                                </div>
                            </template>
                            <template x-if="!foundData && statusBayar === 'UNPAID'">
                                <p class="opacity-60 italic text-sm">Menunggu Plat Nomor Aktif...</p>
                            </template>
                        </div>
                        <button type="button"
                            @click="if(foundData) { showModal = true } else { alert('Ketik plat nomor yang terdaftar!') }"
                            class="w-full py-6 bg-[#2D3436] text-white rounded-2xl font-black text-xl shadow-2xl hover:bg-black transition-all uppercase">PROSES
                            PEMBAYARAN</button>
                    </div>
                </div>
                <div
                    class="bg-white rounded-[3.5rem] shadow-xl overflow-hidden border-4 border-white flex flex-col relative">
                    <div class="bg-[#2D3436] py-4 text-center text-white text-xs font-black uppercase">Struk Pembayaran
                    </div>
                    <div class="p-8 bg-gray-50 flex justify-center items-center flex-grow relative">
                        <div x-show="statusBayar === 'PAID'" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-150 rotate-45"
                            x-transition:enter-end="opacity-100 scale-100 -rotate-12"
                            class="absolute inset-0 flex items-center justify-center pointer-events-none z-50">
                            <div
                                class="border-[8px] border-green-500 text-green-500 font-black text-5xl p-4 rounded-2xl shadow-2xl bg-white/80 backdrop-blur-sm tracking-widest uppercase">
                                LUNAS</div>
                        </div>
                        <div
                            class="w-full max-w-[300px] bg-white p-8 border-2 border-dashed border-red-300 font-mono text-gray-800">
                            <div class="text-center font-black text-xl mb-4 text-red-500">PARKIEST</div>
                            <div class="text-[10px] space-y-2">
                                <div class="flex justify-between"><span>Jam Masuk:</span><span
                                        x-text="foundData ? foundData.waktu_masuk_format : '--:--'"></span></div>
                                <div class="flex justify-between"><span>Jam Keluar:</span><span
                                        x-text="document.getElementById('clock').innerText"></span></div>
                                <div class="border-t border-gray-100 my-2"></div>
                                <div class="text-center font-black text-2xl py-2 bg-gray-50 uppercase"
                                    x-text="inputPlat || '-------'"></div>
                                <div class="flex justify-between mt-4 items-center"><span>Total Biaya:</span><span
                                        class="font-black text-lg text-red-600"
                                        x-text="'Rp ' + formatRupiah(totalTagihan)"></span></div>
                                <div class="border-t border-gray-100 my-2"></div>
                                <div class="text-center opacity-50 uppercase font-bold tracking-widest">Terima
                                    Kasih</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="tab === 'aktivitas'" x-transition x-cloak>
            <div class="bg-white rounded-[3rem] shadow-xl overflow-hidden border-4 border-white">
                <div
                    class="bg-[#2D3436] px-10 py-6 flex flex-col md:flex-row justify-between items-center text-white gap-4">
                    <div class="flex items-center gap-4">
                        <h3 class="text-xl font-black uppercase italic">Riwayat Parkir</h3>
                        <span class="bg-amber-400 text-black px-4 py-1 rounded-full text-xs font-black uppercase">DATA:
                            {{ count($transaksis) }}</span>
                    </div>

                    <form action="{{ route('petugas.dashboard') }}" method="GET" class="flex items-center gap-2">
                        <input type="date" name="tanggal" value="{{ $tanggalTerpilih }}"
                            class="px-4 py-2 rounded-xl text-black font-bold outline-none border-2 border-amber-400 focus:ring-2 focus:ring-amber-200">
                        <button type="submit"
                            class="bg-amber-400 hover:bg-amber-500 text-black px-6 py-2 rounded-xl font-black transition-all uppercase text-xs">Cari</button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase">Kendaraan</th>
                                <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase">Jenis</th>
                                <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase">Masuk</th>
                                <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase">Keluar</th>
                                <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase">Biaya</th>
                                <th class="px-8 py-5 text-xs font-black text-gray-400 uppercase text-center">Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($transaksis as $t)
                                <tr class="hover:bg-amber-50 transition-colors">
                                    <td class="px-8 py-5">
                                        <p class="font-black uppercase text-gray-800">{{ $t->plat_nomor }}</p>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-500 uppercase">
                                        {{ $t->jenis_kendaraan }}</td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-600">
                                        {{ date('H:i', strtotime($t->jam_masuk)) }}</td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-600">
                                        {{ $t->jam_keluar ? date('H:i', strtotime($t->jam_keluar)) : '--:--' }}</td>
                                    <td class="px-8 py-5 font-black text-gray-800">Rp
                                        {{ number_format($t->total_bayar, 0, ',', '.') }}</td>
                                    <td class="px-8 py-5 text-center">
                                        <span
                                            class="{{ $t->status == 'parkir' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600' }} px-4 py-1.5 rounded-xl text-[10px] font-black uppercase">
                                            {{ $t->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-8 py-20 text-center italic text-gray-400">Tidak ada
                                        data transaksi pada tanggal ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-data="{ selectedArea: '' }" x-show="tab === 'area'" x-transition x-cloak>
            <div class="bg-white rounded-[3rem] shadow-xl overflow-hidden border-4 border-white">

                <div class="bg-[#2D3436] px-10 py-8">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                        <div>
                            <h3 class="text-2xl font-black text-white uppercase">Denah Parkir Aktif</h3>
                            <p class="text-amber-400 text-xs font-bold uppercase tracking-widest">Pilih area untuk
                                memantau slot</p>
                        </div>

                        <div class="w-full md:w-72">
                            <select x-model="selectedArea"
                                class="w-full bg-white/10 border-2 border-white/20 rounded-2xl px-6 py-3 text-white font-bold outline-none focus:border-amber-400 transition-all cursor-pointer">
                                <option value="" class="text-black">-- PILIH AREA --</option>
                                @foreach ($areas as $area)
                                    <option value="area_{{ $area->area_id }}" class="text-black">
                                        {{ $area->nama_area }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="p-8 bg-gray-50 min-h-[400px]">
                    <div x-show="!selectedArea" class="flex flex-col items-center justify-center py-20 text-gray-400">
                        <i class="fas fa-map-marked-alt text-6xl mb-4 opacity-20"></i>
                        <p class="font-bold italic text-sm uppercase tracking-widest">Silahkan pilih area terlebih
                            dahulu</p>
                    </div>

                    @foreach ($areas as $area)
                        <div x-show="selectedArea === 'area_{{ $area->area_id }}'"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4">

                            <div class="flex justify-between items-end mb-8 border-b border-gray-200 pb-6">
                                <div>
                                    <span
                                        class="bg-amber-400 text-black px-4 py-1 rounded-full text-[10px] font-black uppercase shadow-sm">
                                        Kapasitas: {{ $area->kapasitas }} Slot
                                    </span>
                                    <h4 class="text-3xl font-black text-gray-800 mt-2">{{ $area->nama_area }}</h4>
                                </div>
                                <div class="flex gap-4">
                                    <div class="text-right">
                                        <p class="text-[10px] font-black text-emerald-600 uppercase">Kosong</p>
                                        <p class="text-lg font-bold text-gray-800">{{ $area->slot_tersedia }}</p>
                                    </div>
                                    <div class="text-right border-l pl-4">
                                        <p class="text-[10px] font-black text-red-600 uppercase">Terisi</p>
                                        <p class="text-lg font-bold text-gray-800">
                                            {{ max(0, $area->kapasitas - $area->slot_tersedia) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-5 sm:grid-cols-8 md:grid-cols-10 lg:grid-cols-12 gap-3">
                                @php
                                    // Gunakan max(0, ...) untuk jaga-jaga kalau slot_tersedia ngaco lagi
                                    $terisiCount = max(0, $area->kapasitas - $area->slot_tersedia);
                                @endphp

                                @for ($i = 1; $i <= $area->kapasitas; $i++)
                                    <div
                                        class="aspect-[2/3] rounded-lg border-2 flex items-center justify-center transition-all duration-500
                            {{ $i <= $terisiCount
                                ? 'bg-red-500 border-red-700 shadow-[inset_0_2px_10px_rgba(0,0,0,0.1)]'
                                : 'bg-emerald-500 border-emerald-700 shadow-sm' }}">

                                        @if ($i <= $terisiCount)
                                            <i class="fas fa-car text-white text-xs animate-pulse"></i>
                                        @else
                                            <span
                                                class="text-[8px] font-black text-white/30">{{ $i }}</span>
                                        @endif
                                    </div>
                                @endfor
                            </div>

                            <div class="mt-10 p-4 bg-white rounded-2xl border border-gray-200 flex items-center gap-4">
                                <div class="flex-grow h-3 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-red-500 transition-all duration-1000"
                                        style="width: {{ ($terisiCount / $area->kapasitas) * 100 }}%"></div>
                                </div>
                                <span
                                    class="font-black text-gray-800 text-sm">{{ round(($terisiCount / $area->kapasitas) * 100) }}%
                                    Penuh</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div x-show="showModal" x-cloak
        class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
        <div @click.away="showModal = false" class="bg-white w-full max-w-md rounded-[3rem] p-10 shadow-2xl">
            <div class="flex justify-between items-center mb-8">
                <h4 class="text-2xl font-black text-gray-800 uppercase italic">Konfirmasi Bayar</h4>
                <button @click="showModal = false" class="text-gray-400 hover:text-red-500 transition-colors"><i
                        class="fas fa-times text-xl"></i></button>
            </div>
            <form action="{{ route('petugas.keluar') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="plat_nomor" :value="inputPlat">
                <input type="hidden" name="total_tagihan" :value="totalTagihan">
                <div class="bg-gray-100 p-6 rounded-3xl text-center">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Total Tagihan</p>
                    <h2 class="text-4xl font-extrabold text-red-500">Rp <span
                            x-text="formatRupiah(totalTagihan)"></span></h2>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 ml-4 uppercase">Jumlah Bayar (Rp)</label>
                    <input type="number" x-model.number="inputBayar" required placeholder="0"
                        class="w-full p-5 rounded-2xl border-2 border-gray-100 text-3xl font-black text-center outline-none focus:border-green-500">
                </div>
                <div class="flex justify-between items-center bg-green-50 p-5 rounded-2xl border border-green-100">
                    <span class="font-bold text-green-700 uppercase text-xs">Kembalian</span>
                    <span class="text-2xl font-black text-green-700">Rp <span
                            x-text="formatRupiah(Math.max(0, inputBayar - totalTagihan))"></span></span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <button type="button" @click="showModal = false"
                        class="py-5 bg-gray-100 rounded-2xl font-bold text-gray-500 uppercase">BATAL</button>
                    <button type="submit" :disabled="inputBayar < totalTagihan"
                        class="py-5 bg-green-500 text-white rounded-2xl font-black text-lg hover:bg-green-600 disabled:opacity-30 uppercase">Bayar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateDateTime() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('en-GB');
            document.getElementById('current-date').innerText = now.toLocaleDateString('en-GB');
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();

        document.getElementById('plat_input')?.addEventListener('input', e => {
            document.getElementById('p_plat').innerText = e.target.value.toUpperCase() || '-------';
        });
        document.getElementById('tarif_select')?.addEventListener('change', e => {
            document.getElementById('p_jenis').innerText = e.target.options[e.target.selectedIndex].getAttribute(
                'data-jenis');
        });
        document.getElementById('area_select')?.addEventListener('change', e => {
            document.getElementById('p_area').innerText = e.target.options[e.target.selectedIndex].getAttribute(
                'data-nama');
        });

        @if (session('cetak_id'))
            window.open("{{ route('petugas.cetak', session('cetak_id')) }}", "_blank");
        @endif
    </script>
</body>

</html>
