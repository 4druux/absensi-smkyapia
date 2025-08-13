<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Permasalahan</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
        }

        .header {
            text-align: left;
            margin-bottom: 20px;
        }

        .logo-container {
            display: inline-block;
            vertical-align: top;
            margin-right: 15px;
        }

        .logo-container img {
            height: 80px;
        }

        .header-text {
            display: inline-block;
            vertical-align: top;
            text-align: left;
        }

        h1 {
            font-size: 14px;
            margin: 0;
        }

        h2 {
            font-size: 12px;
            margin: 0;
        }

        h3 {
            font-size: 11px;
            text-align: center;
            margin-top: 0;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        th {
            font-weight: bold;
            background-color: #C4D79B;
        }

        .text-left {
            text-align: left;
        }

        .table-wrapper {
            margin-bottom: 30px;
        }

        .new-page {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="new-page">
        <div class="header">
            @if (isset($logoPath) && file_exists(public_path($logoPath)))
                <div class="logo-container"><img src="{{ public_path($logoPath) }}" alt="Logo"></div>
            @endif
            <div class="header-text">
                <h1>LAPORAN KEADAAN PERMASALAHAN KELAS</h1>
                <h2>SMK YAPIA PARUNG</h2>
                <h2>Kelas {{ $kelas }} {{ $kelompok }} - {{ $jurusan }}</h2>
                <h2>Tahun Ajaran {{ $tahun }}</h2>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 4%;">NO</th>
                        <th style="width: 10%;">TGL/BLN/TAHUN</th>
                        <th style="width: 12%;">KELAS</th>
                        <th style="width: 27%;">MASALAH KELAS</th>
                        <th style="width: 27%;">PEMECAHAN MASALAH</th>
                        <th style="width: 20%;">KET</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($permasalahanKelas->count() > 0)
                        @foreach ($permasalahanKelas as $index => $problem)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($problem->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $kelas }} {{ $jurusan }}</td>
                                <td class="text-left">{{ $problem->masalah }}</td>
                                <td class="text-left">{{ $problem->pemecahan }}</td>
                                <td class="text-left">{{ $problem->keterangan }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6">Tidak ada data permasalahan kelas.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div class="header">
            @if (isset($logoPath) && file_exists(public_path($logoPath)))
                <div class="logo-container"><img src="{{ public_path($logoPath) }}" alt="Logo"></div>
            @endif
            <div class="header-text">
                <h1>LAPORAN KEADAAN PERMASALAHAN SISWA</h1>
                <h2>SMK YAPIA PARUNG</h2>
                <h2>Kelas {{ $kelas }} {{ $kelompok }} - {{ $jurusan }}</h2>
                <h2>Tahun Ajaran {{ $tahun }}</h2>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 4%;">NO</th>
                        <th style="width: 10%;">TGL/BLN/TAHUN</th>
                        <th style="width: 20%;">NAMA SISWA</th>
                        <th style="width: 12%;">KELAS</th>
                        <th style="width: 17%;">MASALAH</th>
                        <th style="width: 17%;">TINDAKAN WALAS</th>
                        <th style="width: 20%;">KET</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($permasalahanSiswa->count() > 0)
                        @foreach ($permasalahanSiswa as $index => $problem)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($problem->tanggal)->format('d/m/Y') }}</td>
                                <td class="text-left">{{ $problem->siswa->nama }}</td>
                                <td>{{ $kelas }} {{ $jurusan }}</td>
                                <td class="text-left">{{ $problem->masalah }}</td>
                                <td class="text-left">{{ $problem->tindakan_walas }}</td>
                                <td class="text-left">{{ $problem->keterangan }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7">Tidak ada data permasalahan siswa.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
