<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendapatan Parkir - {{ $start }} s/d {{ $end }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; color: #333; padding: 30px; background: #fff; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #FF9F1C; padding-bottom: 20px; }
        .header h1 { font-size: 24px; font-weight: 800; color: #2D3436; margin-bottom: 5px; }
        .header p { font-size: 12px; color: #666; }
        .periode { background: #FFF9F2; border: 1px solid #FFD99A; border-radius: 8px; padding: 12px 20px; margin-bottom: 25px; text-align: center; font-size: 13px; }
        .stats { display: flex; gap: 20px; margin-bottom: 25px; }
        .stat-card { flex: 1; background: #f8f8f8; border-radius: 8px; padding: 15px; text-align: center; border: 1px solid #eee; }
        .stat-card .label { font-size: 10px; text-transform: uppercase; font-weight: 700; color: #888; letter-spacing: 1px; }
        .stat-card .value { font-size: 22px; font-weight: 800; color: #2D3436; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }
        th { background: #2D3436; color: #fff; padding: 10px 12px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; }
        td { padding: 9px 12px; border-bottom: 1px solid #eee; }
        tr:nth-child(even) { background: #fafafa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; border-top: 2px solid #eee; padding-top: 15px; display: flex; justify-content: space-between; font-size: 11px; color: #888; }
        .total-row { background: #FF9F1C !important; color: #fff; font-weight: 800; }
        .total-row td { border: none; padding: 12px; }
        .badge { padding: 3px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; }
        .badge-keluar { background: #d4edda; color: #155724; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background: #f5f5f5; padding: 12px; text-align: center; border-radius: 8px; margin-bottom: 25px;">
        <button onclick="window.print()" style="padding: 10px 30px; font-weight: bold; cursor: pointer; background: #FF9F1C; color: #fff; border: none; border-radius: 8px; font-size: 14px;">
            📄 CETAK / SIMPAN PDF
        </button>
        <p style="font-size: 11px; color: #888; margin-top: 5px;">Gunakan "Save as PDF" pada dialog print untuk menyimpan sebagai PDF</p>
    </div>

    <div class="header">
        <h1>LAPORAN PENDAPATAN PARKIR</h1>
        <p>PARKIEST — Smart Parking Management System</p>
    </div>

    <div class="periode">
        <strong>Periode:</strong> {{ \Carbon\Carbon::parse($start)->format('d M Y') }} — {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
    </div>

    <div class="stats">
        <div class="stat-card">
            <div class="label">Total Pendapatan</div>
            <div class="value" style="color: #FF9F1C;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ $totalKendaraan }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Plat Nomor</th>
                <th>Jenis</th>
                <th>Area</th>
                <th>Waktu Masuk</th>
                <th>Waktu Keluar</th>
                <th class="text-center">Durasi</th>
                <th class="text-right">Biaya</th>
                <th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaksis as $i => $t)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td style="font-weight: 700; letter-spacing: 1px;">{{ $t->kendaraan->plat_nomor ?? '-' }}</td>
                <td>{{ ucfirst($t->kendaraan->jenis_kendaraan ?? '-') }}</td>
                <td>{{ $t->area->nama_area ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($t->waktu_masuk)->format('d/m/Y H:i') }}</td>
                <td>{{ $t->waktu_keluar ? \Carbon\Carbon::parse($t->waktu_keluar)->format('d/m/Y H:i') : '-' }}</td>
                <td class="text-center">{{ $t->durasi_jam ?? 0 }} jam</td>
                <td class="text-right" style="font-weight: 700;">Rp {{ number_format($t->biaya_total, 0, ',', '.') }}</td>
                <td>{{ $t->user->nama_lengkap ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center" style="padding: 30px; color: #888;">Tidak ada data transaksi pada periode ini</td></tr>
            @endforelse

            @if($transaksis->count() > 0)
            <tr class="total-row">
                <td colspan="7" class="text-right">TOTAL PENDAPATAN</td>
                <td class="text-right">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <div>Dicetak pada: {{ now()->format('d M Y, H:i:s') }}</div>
        <div>Oleh: {{ auth()->user()->nama_lengkap ?? 'Owner' }}</div>
    </div>
</body>
</html>
