<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengeluaran</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            font-size: 32px;
            margin-bottom: 0;
        }

        p.periode {
            text-align: center;
            font-size: 16px;
            margin: 0; 
            font-weight: bold;
        }

        p.tgl {
            text-align: center;
            font-size: 16px;
            margin-top: 10px;
            margin-bottom: 80px; 
            font-weight: bold;
            text-transform: uppercase; 
        }

        table {
            width: 80%; 
            border-collapse: collapse;
            margin: 0 auto; 
            font-family: Arial, sans-serif;
        }

        th, td {
            border: 1px solid #000;
            padding: 2px 4px; 
        }

        th {
            background-color: #cccccc; 
            font-weight: bold;
            font-size: 16px; 
            text-align: center;
        }

        td {
            font-size: 16px;
        }


        td.tanggal {
            text-align: center;
        }

        td.harga, 
        td.keterangan {
            text-align: left;
        }

        colgroup col.keterangan {
            width: 50%;
        }

        colgroup col.tanggal {
            width: 25%;
        }

        colgroup col.harga {
            width: 25%;
        }

        @page {
            margin: 20px;
        }
    </style>
</head>
<body>
    <h1>DAFTAR PENGELUARAN</h1>
    <p class="periode">PERIODE</p>
    <p class="tgl">{{ date('j F Y', strtotime($start_date)) }} - {{ date('j F Y', strtotime($end_date)) }}</p>

    <table>
        <colgroup>
            <col class="tanggal">
            <col class="harga">
            <col class="keterangan">
        </colgroup>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Total Harga</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($expenses as $expense)
                <tr>
                    <td class="tanggal">{{ date('d/m/Y', strtotime($expense->date)) }}</td>
                    <td class="harga">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                    <td class="keterangan">{{ $expense->description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Tidak ada data untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
