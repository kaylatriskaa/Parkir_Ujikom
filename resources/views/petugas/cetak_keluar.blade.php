<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Keluar - {{ $transaksi->kendaraan->plat_nomor ?? '-' }}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 70mm; margin: 0 auto; padding: 10px;
            color: #000; line-height: 1.3;
        }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
        .plat { font-size: 24px; font-weight: bold; text-align: center; border: 2px solid #000; margin: 10px 0; padding: 8px; letter-spacing: 2px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 13px; }
        .total-box { border: 2px solid #000; padding: 10px; margin: 10px 0; text-align: center; }
        .lunas { text-align: center; margin: 10px 0; font-size: 18px; font-weight: bold; border: 3px double #000; padding: 8px; letter-spacing: 4px; }
        .footer { margin-top: 15px; text-align: center; font-size: 11px; border-top: 1px dashed #000; padding-top: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="background: #f0f0f0; padding: 10px; text-align: center; border-radius: 5px; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 15px; font-weight: bold; cursor: pointer;">CETAK STRUK</button>
        <p style="font-size: 10px; color: #666;">Tab akan menutup otomatis setelah dicetak.</p>
    </div>

    <div class="header">
        <h2 style="margin: 0; letter-spacing: 2px;">PARKIEST</h2>
        <p style="margin: 5px 0; font-size: 12px; font-weight: bold;">STRUK PEMBAYARAN KELUAR</p>
        <p style="margin: 0; font-size: 10px;">{{ \Carbon\Carbon::parse($transaksi->waktu_keluar)->format('d M Y - H:i:s') }}</p>
    </div>

    <div class="plat">{{ strtoupper($transaksi->kendaraan->plat_nomor ?? '-') }}</div>

    <div class="info-row"><span>No. Tiket:</span><span>#{{ str_pad($transaksi->id_parkir, 6, '0', STR_PAD_LEFT) }}</span></div>
    <div class="info-row"><span>Jenis:</span><span>{{ strtoupper($transaksi->kendaraan->jenis_kendaraan ?? '-') }}</span></div>
    <div class="info-row"><span>Area:</span><span>{{ $transaksi->area->nama_area ?? '-' }}</span></div>
    <div class="info-row"><span>Petugas:</span><span>{{ $transaksi->user->nama_lengkap ?? auth()->user()->nama_lengkap }}</span></div>

    <div style="border-top: 1px dashed #000; margin: 10px 0; padding-top: 8px;">
        <div class="info-row"><span>Masuk:</span><span>{{ \Carbon\Carbon::parse($transaksi->waktu_masuk)->format('d/m/Y H:i') }}</span></div>
        <div class="info-row"><span>Keluar:</span><span>{{ \Carbon\Carbon::parse($transaksi->waktu_keluar)->format('d/m/Y H:i') }}</span></div>
        <div class="info-row"><span>Durasi:</span><span>{{ $transaksi->durasi_jam }} Jam</span></div>
        <div class="info-row"><span>Tarif/Jam:</span><span>Rp {{ number_format($transaksi->tarif->tarif_per_jam ?? 0, 0, ',', '.') }}</span></div>
    </div>

    <div class="total-box">
        <p style="margin: 0; font-size: 11px;">TOTAL BAYAR</p>
        <p style="margin: 5px 0 0; font-size: 22px; font-weight: bold;">Rp {{ number_format($transaksi->biaya_total, 0, ',', '.') }}</p>
    </div>

    <div class="lunas">✓ LUNAS ✓</div>

    <div class="footer">
        <p style="margin: 0; font-weight: bold;">Terima Kasih Atas Kunjungan Anda</p>
        <p style="margin: 5px 0 0;">Hati-hati di jalan!</p>
    </div>

    <script>window.onafterprint = function() { window.close(); };</script>
</body>
</html>
