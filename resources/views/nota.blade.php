<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Nota Transaksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #000;
        }

        .header {
            background-color: #2d2dbf;
            color: #fff;
            padding: 10px 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
        }

        .header .right {
            float: right;
            text-align: right;
        }

        .content {
            padding: 20px;
        }

        .info {
            margin-top: 10px;
        }

        .info p {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 8px 5px;
            text-align: left;
        }

        th {
            border-bottom: 1px solid #000;
        }

        .bold {
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .border-bottom {
            border-bottom: 1px solid #000;
        }

        .footer {
            margin-top: 40px;
            font-style: italic;
            font-size: 12px;
            text-align: right;
        }

        .table-summary td {
            padding-top: 5px;
        }

        .text-blue {
            color: #2d2dbf;
        }
    </style>
</head>

<body>
    <div class="header">
        <div style="float: left;">
            <h1>UD. Nama Perusahaan</h1>
        </div>
        <div class="right">
            <p>Nota Transaksi</p>
            <p>No. {{ $nota['nomor'] }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="content">
        <div class="info">
            <p><strong>Diterbitkan atas nama</strong><br>
                {{ $nota['nama_pencetak'] }}</p>

            <p style="float: right; text-align: right;">
                Nama Toko : <strong>{{ $nota['toko'] }}</strong><br>
                Tanggal masuk : <strong>{{ $nota['tanggal_masuk'] }}</strong><br>
                Tanggal keluar : <strong>{{ $nota['tanggal_keluar'] }}</strong>
            </p>
            <div style="clear: both;"></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Transaksi</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-blue">{{ $nota['nama_produk'] }}</td>
                    <td>{{ $nota['jumlah_awal'] }}</td>
                    <td>Rp. {{ number_format($nota['harga_satuan'], 0, ',', '.') }}</td>
                    <td>Rp. {{ number_format($nota['total_awal'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Barang Kembali</td>
                    <td> {{ $nota['jumlah_kembali'] }}</td>
                    <td></td>
                    <td>Rp. {{ number_format($nota['total_kembali'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Tagihan</td>
                    <td class="bold"> {{ $nota['jumlah_bayar'] }}</td>
                    <td></td>
                    <td class="bold">Rp. {{ number_format($nota['total_bayar'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            Nota ini dicetak pada tanggal {{ $nota['waktu_cetak'] }}
        </div>
    </div>
</body>

</html>