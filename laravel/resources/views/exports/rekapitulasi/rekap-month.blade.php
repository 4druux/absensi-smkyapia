<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Rekapitulasi Bulanan</title>
    <style>
        @page {
            margin: 25px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            margin: 0;
        }

        .header {
            margin-bottom: 15px;
            width: 100%;
        }

        .logo-container {
            display: inline-block;
            vertical-align: top;
            width: 80px;
        }

        .logo-container img {
            width: 100%;
        }

        .header-text {
            display: inline-block;
            vertical-align: top;
            margin-left: 15px;
        }

        .header-text h1,
        .header-text h2 {
            margin: 0;
        }

        h1 {
            font-size: 14px;
        }

        h2 {
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .main-header-bg {
            background-color: #FABF8F;
        }

        .alt-header-bg {
            background-color: #C4D79B;
        }

        .text-left {
            text-align: left;
        }

        .vertical-text-container {
            height: 70px;
            position: relative;
        }

        .vertical-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-90deg);
            white-space: nowrap;
        }

        .wrapper-table {
            page-break-inside: avoid;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .no-border {
            border: none !important;
        }
    </style>
</head>

<body>
    <div class="header">
        @if (isset($logoPath) && file_exists(public_path($logoPath)))
            <div class="logo-container">
                <img src="{{ public_path($logoPath) }}" alt="Logo SMK">
            </div>
        @endif
        <div class="header-text">
            <h1>DATA REKAPITULASI SISWA</h1>
            <h2>SMK YAPIA PARUNG</h2>
            <h2>Kelas {{ $kelas }} - {{ $jurusan }}</h2>
            <h2>Periode {{ $namaBulan }} {{ $year }}</h2>
        </div>
    </div>

    <div>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th rowspan="2" class="main-header-bg" style="width: 3%;">NO</th>
                    <th rowspan="2" class="main-header-bg" style="width: 12%;">NAMA SISWA</th>
                    <th colspan="9" class="main-header-bg">REKAPITULASI</th>
                    <th rowspan="2" class="alt-header-bg" style="width: 6%;">POINT <br>LAINNYA</th>
                    <th rowspan="2" class="alt-header-bg" style="width: 15%;">KETERANGAN<br>(Tulis Jenis Pelanggaran
                        Disertai dengan Tanggal Kejadian)</th>
                    <th rowspan="2" class="main-header-bg" style="width: 6%;">TOTAL POINT<br>BULAN LALU</th>
                    <th rowspan="2" class="main-header-bg" style="width: 6%;">TOTAL POINT<br>SAMPAI BULAN INI</th>
                    <th rowspan="2" class="main-header-bg" style="width: 15%;">DESKRIPSI</th>
                </tr>
                <tr>
                    <th class="main-header-bg">
                        <div class="vertical-text-container"><span class="vertical-text">Telat</span></div>
                    </th>
                    <th class="main-header-bg">
                        <div class="vertical-text-container"><span class="vertical-text">Alfa</span></div>
                    </th>
                    <th class="main-header-bg">
                        <div class="vertical-text-container"><span class="vertical-text">Sakit</span></div>
                    </th>
                    <th class="main-header-bg">
                        <div class="vertical-text-container"><span class="vertical-text">Izin</span></div>
                    </th>
                    <th class="main-header-bg">
                        <div class="vertical-text-container"><span class="vertical-text">Bolos</span></div>
                    </th>
                    <th class="main-header-bg">
                        <div class="vertical-text-container"><span class="vertical-text">Point Telat</span></div>
                    </th>
                    <th class="main-header-bg">
                        <div class="vertical-text-container"><span class="vertical-text">Point Alfa</span></div>
                    </th>
                    <th class="main-header-bg">
                        <div class="vertical-text-container"><span class="vertical-text">Point Bolos</span></div>
                    </th>
                    <th class="main-header-bg">
                        <div class="vertical-text-container"><span class="vertical-text">Persentase</span></div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rekapData as $index => $student)
                    @php
                        $absensi = $student['absensi'];
                        $pointTelat = $absensi['telat'] * 0.5;
                        $pointAlfa = $absensi['alfa'] * 1.5;
                        $pointBolos = $absensi['bolos'] * 2;

                        $totalAbsen = $absensi['alfa'] + $absensi['sakit'] + $absensi['izin'];
                        $persentase =
                            $activeDaysInMonth > 0
                                ? (($activeDaysInMonth - $totalAbsen) / $activeDaysInMonth) * 100
                                : 0;

                        $totalPointBulanIni = $pointTelat + $pointAlfa + $pointBolos;
                        $totalPointSampaiBulanIni = $student['total_point_bulan_lalu'] + $totalPointBulanIni;

                        $deskripsi = '';
                        if ($totalPointSampaiBulanIni >= 50) {
                            $deskripsi = 'Ananda diberikan Surat Peringatan 3';
                        } elseif ($totalPointSampaiBulanIni >= 43) {
                            $deskripsi = 'Ananda diberikan Surat Peringatan 2';
                        } elseif ($totalPointSampaiBulanIni >= 35) {
                            $deskripsi = 'Ananda diberikan Surat Peringatan 2';
                        } elseif ($totalPointSampaiBulanIni >= 28) {
                            $deskripsi = 'Ananda diberikan Surat Peringatan 1';
                        } elseif ($totalPointSampaiBulanIni >= 20) {
                            $deskripsi = 'Ananda diberikan Surat Peringatan 1';
                        } elseif ($totalPointSampaiBulanIni >= 15) {
                            $deskripsi = 'Ananda diberikan Surat Komitmen Ke-3';
                        } elseif ($totalPointSampaiBulanIni >= 10) {
                            $deskripsi = 'Ananda diberikan Surat Komitmen Ke-2';
                        } elseif ($totalPointSampaiBulanIni >= 5) {
                            $deskripsi = 'Ananda diberikan Surat Komitmen Ke-1';
                        } elseif ($totalPointSampaiBulanIni >= 1) {
                            $deskripsi = 'Motivasi dan Teguran dari Wali Kelas';
                        } else {
                            $deskripsi = 'Aman';
                        }

                        $rowColor = '';
                        if ($totalPointSampaiBulanIni >= 50) {
                            $rowColor = 'background-color: #ff0000;';
                        } elseif ($totalPointSampaiBulanIni >= 43) {
                            $rowColor = 'background-color: #538dd5;';
                        } elseif ($totalPointSampaiBulanIni >= 35) {
                            $rowColor = 'background-color: #ffff00;';
                        } elseif ($totalPointSampaiBulanIni >= 28) {
                            $rowColor = 'background-color: #7030a0;';
                        } elseif ($totalPointSampaiBulanIni >= 20) {
                            $rowColor = 'background-color: #92d050;';
                        } elseif ($totalPointSampaiBulanIni >= 15) {
                            $rowColor = 'background-color: #ff7c80;';
                        } elseif ($totalPointSampaiBulanIni >= 10) {
                            $rowColor = 'background-color: #ffc000;';
                        } elseif ($totalPointSampaiBulanIni >= 5) {
                            $rowColor = 'background-color: #ebf1de;';
                        } elseif ($totalPointSampaiBulanIni >= 1) {
                            $rowColor = 'background-color: #c5d9f1;';
                        }
                    @endphp
                    <tr style="height: 25px;">
                        <td>{{ $index + 1 }}</td>
                        <td class="text-left">{{ $student['nama'] }}</td>
                        <td>{{ $absensi['telat'] ?: '-' }}</td>
                        <td>{{ $absensi['alfa'] ?: '-' }}</td>
                        <td>{{ $absensi['sakit'] ?: '-' }}</td>
                        <td>{{ $absensi['izin'] ?: '-' }}</td>
                        <td>{{ $absensi['bolos'] ?: '-' }}</td>
                        <td>{{ $pointTelat > 0 ? ($pointTelat == (int) $pointTelat ? number_format($pointTelat, 0, ',', '.') : number_format($pointTelat, 1, ',', '.')) : '-' }}
                        </td>
                        <td>{{ $pointAlfa > 0 ? ($pointAlfa == (int) $pointAlfa ? number_format($pointAlfa, 0, ',', '.') : number_format($pointAlfa, 1, ',', '.')) : '-' }}
                        </td>
                        <td>{{ $pointBolos > 0 ? ($pointBolos == (int) $pointBolos ? number_format($pointBolos, 0, ',', '.') : number_format($pointBolos, 1, ',', '.')) : '-' }}
                        </td>
                        <td>{{ round($persentase) }}%</td>
                        <td></td>
                        <td></td>
                        <td>{{ $student['total_point_bulan_lalu'] > 0 ? ($student['total_point_bulan_lalu'] == (int) $student['total_point_bulan_lalu'] ? number_format($student['total_point_bulan_lalu'], 0, ',', '.') : number_format($student['total_point_bulan_lalu'], 1, ',', '.')) : '-' }}
                        </td>
                        <td>{{ $totalPointSampaiBulanIni > 0 ? ($totalPointSampaiBulanIni == (int) $totalPointSampaiBulanIni ? number_format($totalPointSampaiBulanIni, 0, ',', '.') : number_format($totalPointSampaiBulanIni, 1, ',', '.')) : '-' }}
                        </td>
                        <td class="text-left" style="{{ $rowColor }}">{{ $deskripsi }}</td>
                    </tr>
                @endforeach
                <tr style="height: 30px;">
                    <td colspan="10" class="main-header-bg" style="font-weight: bold;">RATA-RATA KEHADIRAN PERBULAN
                    </td>
                    @php
                        $totalPersentase = 0;
                        if ($rekapData->count() > 0) {
                            foreach ($rekapData as $student) {
                                $totalAbsen =
                                    $student['absensi']['alfa'] +
                                    $student['absensi']['sakit'] +
                                    $student['absensi']['izin'];
                                $totalPersentase +=
                                    $activeDaysInMonth > 0
                                        ? (($activeDaysInMonth - $totalAbsen) / $activeDaysInMonth) * 100
                                        : 0;
                            }
                            $rataRata = $totalPersentase / $rekapData->count();
                        } else {
                            $rataRata = 0;
                        }
                    @endphp
                    <td class="main-header-bg" style="font-weight: bold; font-size: 10px;">{{ round($rataRata) }}%</td>
                    <td colspan="5" class="main-header-bg"></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="clearfix"></div>

    <table style="margin-top: 20px; width: 100%;">
        <tr>
            <td class="no-border" style="width: 50%; vertical-align: top;">
                <div>
                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <th colspan="2" class="alt-header-bg">KATEGORI DESKRIPSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="background-color: #ff0000;">
                                <td>>=50</td>
                                <td class="text-left">Ananda diberikan Surat Peringatan 3</td>
                            </tr>
                            <tr style="background-color: #538dd5;">
                                <td>>=43</td>
                                <td class="text-left">Ananda diberikan Surat Peringatan 2</td>
                            </tr>
                            <tr style="background-color: #ffff00;">
                                <td>>=35</td>
                                <td class="text-left">Ananda diberikan Surat Peringatan 2</td>
                            </tr>
                            <tr style="background-color: #7030a0;">
                                <td>>=28</td>
                                <td class="text-left">Ananda diberikan Surat Peringatan 1</td>
                            </tr>
                            <tr style="background-color: #92d050;">
                                <td>>=20</td>
                                <td class="text-left">Ananda diberikan Surat Peringatan 1</td>
                            </tr>
                            <tr style="background-color: #ff7c80;">
                                <td>>=15</td>
                                <td class="text-left">Ananda diberikan Surat Komitmen Ke-3</td>
                            </tr>
                            <tr style="background-color: #ffc000;">
                                <td>>=10</td>
                                <td class="text-left">Ananda diberikan Surat Komitmen Ke-2</td>
                            </tr>
                            <tr style="background-color: #ebf1de;">
                                <td>>=5</td>
                                <td class="text-left">Ananda diberikan Surat Komitmen Ke-1</td>
                            </tr>
                            <tr style="background-color: #c5d9f1;">
                                <td>>=1</td>
                                <td class="text-left">Motivasi dan Teguran dari Wali Kelas</td>
                            </tr>
                            <tr>
                                <td>0</td>
                                <td class="text-left">Aman</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </td>
            <td class="no-border" style="width: 50%; vertical-align: top;">
                <div>
                    <table style="width: 80%; margin-left: auto; margin-right: 0;">
                        <thead>
                            <tr>
                                <th class="alt-header-bg" colspan="2">HARI EFEKTIF</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Jumlah</td>
                                <td style="font-size: 14px; font-weight: bold;">{{ $activeDaysInMonth }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
