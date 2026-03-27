<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Karcis - {{ $transaksi->kendaraan->plat_nomor ?? '-' }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 70mm;
            margin: 0 auto;
            padding: 10px;
            color: #000;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .content { font-size: 14px; }
        .plat {
            font-size: 26px;
            font-weight: bold;
            text-align: center;
            border: 2px solid #000;
            margin: 10px 0;
            padding: 10px;
            letter-spacing: 2px;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 12px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .barcode {
            text-align: center;
            margin: 10px 0;
            font-size: 10px;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="background: #f0f0f0; padding: 10px; text-align: center; border-radius: 5px; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 15px; font-weight: bold; cursor: pointer;">CETAK SEKARANG</button>
        <p style="font-size: 10px; color: #666;">Tab ini akan menutup otomatis setelah dicetak.</p>
    </div>

    <div class="header">
        <h2 style="margin: 0; letter-spacing: 2px;">PARKIEST</h2>
        <p style="margin: 5px 0; font-size: 11px;">Struk Masuk Kendaraan</p>
        <p style="margin: 0; font-size: 10px;">{{ date('d M Y - H:i:s') }}</p>
    </div>

    <div class="content">
        <div class="info-row">
            <span>No. Tiket:</span>
            <span>#{{ str_pad($transaksi->id_parkir, 6, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="plat">
            {{ strtoupper($transaksi->kendaraan->plat_nomor ?? '-') }}
        </div>

        <div class="info-row">
            <span>Jenis:</span>
            <span>{{ strtoupper($transaksi->kendaraan->jenis_kendaraan ?? '-') }}</span>
        </div>
        <div class="info-row">
            <span>Area:</span>
            <span>{{ $transaksi->area->nama_area ?? 'Area Umum' }}</span>
        </div>
        <div class="info-row">
            <span>Petugas:</span>
            <span>{{ auth()->user()->nama_lengkap }}</span>
        </div>

        <div class="barcode">
            <p style="border-top: 1px solid #eee; padding-top: 5px;">* TARIF: Rp {{ number_format($transaksi->tarif->tarif_per_jam ?? 0, 0, ',', '.') }}/JAM *</p>
        </div>
    </div>

    <div class="footer">
        <p style="margin: 0; font-weight: bold;">SIMPAN TIKET INI</p>
        <p style="margin: 5px 0 0 0;">Terima Kasih Atas Kunjungan Anda</p>
    </div>

    <script>
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>
