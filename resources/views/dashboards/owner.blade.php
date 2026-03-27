@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-[#FFF9F2] -m-8 p-8 font-sans">
        <div class="w-full bg-white px-8 py-4 flex justify-between items-center shadow-sm mb-8 border-b border-gray-100 rounded-3xl">
            <div class="flex items-center gap-4">
                <div class="bg-[#FF9F1C] p-2 rounded-xl text-white font-bold text-2xl w-12 h-12 flex items-center justify-center shadow-md">P</div>
                <span class="text-2xl font-bold text-gray-800">Arkiest <span class="text-amber-500 text-sm font-black uppercase ml-2 tracking-widest">Owner</span></span>
            </div>
            <div class="flex items-center gap-6">
                <div class="text-right hidden md:block">
                    <p class="text-[10px] font-black text-orange-400 uppercase tracking-widest leading-none mb-1">Owner Mode</p>
                    <p class="font-bold text-gray-800 italic">{{ auth()->user()->name }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-50 text-red-500 px-6 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-red-500 hover:text-white transition-all shadow-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        {{-- FILTER TANGGAL --}}
        <div class="max-w-7xl mx-auto mb-8 flex flex-wrap items-center justify-between gap-4 bg-white p-6 rounded-[2rem] border border-amber-100 shadow-sm">
            <div>
                <h3 class="font-black text-gray-800 uppercase tracking-tighter">Periode Laporan</h3>
            </div>
            <form method="GET" class="flex items-center gap-3">
                <input type="date" name="start_date" value="{{ request('start_date', date('Y-m-01')) }}" class="bg-gray-50 border-none rounded-xl px-4 py-2 text-xs font-bold focus:ring-2 focus:ring-amber-400 text-gray-600">
                <span class="text-gray-400 font-black">-</span>
                <input type="date" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}" class="bg-gray-50 border-none rounded-xl px-4 py-2 text-xs font-bold focus:ring-2 focus:ring-amber-400 text-gray-600">
                <button type="submit" class="bg-amber-500 text-white px-6 py-2 rounded-xl font-black text-[10px] uppercase hover:bg-black transition-all shadow-md">Sinkronkan Data</button>
            </form>
        </div>

        {{-- STATS CARDS --}}
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-gradient-to-br from-[#2D3436] to-[#000000] p-8 rounded-[2.5rem] shadow-xl text-white">
                <p class="opacity-60 text-[10px] font-black uppercase tracking-widest mb-1">Total Pendapatan</p>
                <h2 class="text-4xl font-black">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h2>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-amber-100">
                <p class="text-amber-600 opacity-80 text-[10px] font-black uppercase tracking-widest mb-1">Total Kendaraan</p>
                <h2 class="text-5xl font-black text-gray-800 tracking-tighter">{{ $kendaraanPeriode }}</h2>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-amber-100">
                <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-1">Staff Aktif</p>
                <h2 class="text-5xl font-black text-gray-800 tracking-tighter">{{ $totalPetugas }}</h2>
            </div>
        </div>

        {{-- GRAFIK & RINCIAN --}}
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
            {{-- GRAFIK (KIRI) --}}
            <div class="lg:col-span-2 bg-white p-10 rounded-[3rem] shadow-sm border border-amber-100">
                <h3 class="text-xl font-black text-gray-800 uppercase tracking-tighter mb-8 ">
                    <i class="fas fa-chart-line text-amber-500 mr-2"></i> Grafik Pendapatan
                </h3>
                <div class="h-[350px]">
                    <canvas id="dbChart"></canvas>
                </div>
            </div>

            {{-- RINCIAN TRANSAKSI PENDAPATAN (KANAN) --}}
            <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-amber-100 flex flex-col">
                <h3 class="text-lg font-black text-gray-800 uppercase mb-6 ">Rincian Masuk</h3>
                <div class="space-y-4 overflow-y-auto max-h-[350px] pr-2 custom-scrollbar">
                    @forelse ($rincianPendapatan as $item)
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex justify-between items-center">
                        <div>
                            <p class="text-[10px] font-black text-emerald-700 uppercase leading-none mb-1">{{ $item->plat_nomor }}</p>
                            <p class="text-[9px] text-gray-400 font-bold">{{ \Carbon\Carbon::parse($item->created_at)->format('d M, H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-black text-gray-800 italic">Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-center py-10 text-xs font-bold text-gray-400 italic">Belum ada pendapatan di periode ini.</p>
                    @endforelse
                </div>
                <div class="mt-auto pt-4 border-t border-gray-100 italic text-[10px] font-black text-amber-600 text-center uppercase">
                    Menampilkan 10 Transaksi Terakhir
                </div>
            </div>
        </div>

        {{-- LOG AKTIVITAS (Bawah)--}}
        <div class="max-w-7xl mx-auto">
             <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-amber-100">
                <h3 class="text-lg font-black text-gray-800 uppercase mb-6"><i class="fas fa-history text-amber-500 mr-2"></i> Monitor Traffic Terakhir</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($logs as $log)
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm text-amber-500 text-xs font-black">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('H:i') }}
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-black text-gray-800 tracking-widest">{{ $log->plat_nomor }}</p>
                            <p class="text-[9px] text-gray-400 font-bold uppercase">{{ $log->nama_area }}</p>
                        </div>
                        <div class="text-[9px] font-black uppercase {{ $log->status == 'parkir' ? 'text-blue-500' : 'text-emerald-500' }} italic">
                            {{ $log->status }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('dbChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: {!! json_encode($chartData) !!},
                    backgroundColor: '#FF9F1C',
                    borderRadius: 12,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: '#F3F4F6' } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #FF9F1C; border-radius: 10px; }
    </style>
@endsection
