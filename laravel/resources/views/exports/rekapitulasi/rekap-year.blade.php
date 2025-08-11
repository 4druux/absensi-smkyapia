<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Rekapitulasi Tahunan</title>
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
            padding: 2px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        th {
            font-weight: bold;
        }

        .header-bg {
            background-color: #FABF8F;
        }

        .text-left {
            text-align: left;
        }

        .vertical-text-container {
            height: 50px;
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
            <h1>REKAPITULASI DATA SISWA TAHUNAN</h1>
            <h2>SMK YAPIA PARUNG</h2>
            <h2>Kelas {{ $kelas }} - {{ $jurusan }}</h2>
            <h2>Tahun Ajaran {{ $tahun }}</h2>
        </div>
    </div>

    <div class="wrapper-table">
        <table>
            <thead>
                <tr>
                    <th rowspan="2" class="header-bg" style="width: 1%;">NO</th>
                    <th rowspan="2" class="header-bg" style="width: 9%;">NAMA SISWA</th>
                    @php
                        [$startYear, $endYear] = explode('-', $tahun);
                        $periods = [
                            "REKAPITULASI JULI-SEPT {$startYear}<br>INI UNTUK DATA RAPORT PTS GANJIL",
                            'REKAPITULASI JULI-DESEMBER<br>INI UNTUK DATA RAPORT PAS GANJIL',
                            "REKAPITULASI JAN-MARET {$endYear}<br>INI UNTUK DATA RAPORT PTS GENAP",
                            "REKAPITULASI JAN-JUNI {$endYear}<br>INI UNTUK DATA RAPORT PAT GENAP",
                            "REKAPITULASI JULI {$startYear} - JUNI {$endYear}<br>INI UNTUK DATA KENAIKAN KELAS",
                        ];
                    @endphp
                    @foreach ($periods as $periodName)
                        <th colspan="6" class="header-bg">{!! $periodName !!}</th>
                    @endforeach
                    <th rowspan="2" class="header-bg" style="width: 4%;">
                        <div>
                            <span>PERSENTASE<br>
                                KEHADIRAN<br>SELAMA<br>SETAHUN<br>ALFA, SAKIT &<br> IJIN DIHITUNG
                            </span>
                        </div>
                    </th>
                    <th rowspan="2" class="header-bg" style="width: 4%;">
                        <div>
                            <span>PERSENTASE<br>
                                KEHADIRAN<br>SELAMA<br> SETAHUN<br>HANYA ALFA
                            </span>
                        </div>
                    </th>
                </tr>
                <tr>
                    @foreach ($periods as $periodName)
                        <th class="header-bg" style="width: 1%;">
                            <div class="vertical-text-container"><span class="vertical-text">TELAT</span></div>
                        </th>
                        <th class="header-bg" style="width: 1%;">
                            <div class="vertical-text-container"><span class="vertical-text">ALFA</span></div>
                        </th>
                        <th class="header-bg" style="width: 1%;">
                            <div class="vertical-text-container"><span class="vertical-text">SAKIT</span></div>
                        </th>
                        <th class="header-bg" style="width: 1%;">
                            <div class="vertical-text-container"><span class="vertical-text">IZIN</span></div>
                        </th>
                        <th class="header-bg" style="width: 1%;">
                            <div class="vertical-text-container"><span class="vertical-text">BOLOS</span></div>
                        </th>
                        <th class="header-bg" style="width: 2%;">TOTAL<br>POINT<br>KUMULATIF</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rekapData as $index => $student)
                    <tr style="height: 25px;">
                        <td>{{ $index + 1 }}</td>
                        <td class="text-left">{{ $student['nama'] }}</td>
                        @foreach ($student['periods'] as $periodData)
                            <td>{{ $periodData['telat'] ?: '-' }}</td>
                            <td>{{ $periodData['alfa'] ?: '-' }}</td>
                            <td>{{ $periodData['sakit'] ?: '-' }}</td>
                            <td>{{ $periodData['izin'] ?: '-' }}</td>
                            <td>{{ $periodData['bolos'] ?: '-' }}</td>
                            <td>
                                @php $point = $periodData['total_point_kumulatif']; @endphp
                                {{ $point > 0 ? ($point == (int) $point ? number_format($point, 0, ',', '.') : number_format($point, 1, ',', '.')) : '-' }}
                            </td>
                        @endforeach
                        <td>{{ round($student['persentase_sia']) }}%</td>
                        <td>{{ round($student['persentase_efektif']) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
