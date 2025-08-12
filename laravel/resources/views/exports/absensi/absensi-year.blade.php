<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi Tahunan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
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

        th,
        td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        th {
            background-color: #c4d79b;
            font-weight: bold;
        }

        .telat-cell {
            background-color: #c4d79b;
        }

        .nama-siswa-cell {
            width: 12%;
        }

        .month-cell {
            width: 7%;
        }

        .vertical-text-container {
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .vertical-text {
            display: inline-block;
            transform: rotate(-90deg);
            transform-origin: center center;
            white-space: nowrap;
            line-height: 1;
            margin-top: 10px;
        }

        .month-header {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: fit-content;
            width: fit-content;
            margin: auto;
            text-align: center;
            line-height: 1;
        }
    </style>
</head>

<body>
    <div class="header">
        @if (isset($logoPath))
            <div class="logo-container">
                <img src="{{ public_path($logoPath) }}" alt="Logo SMK">
            </div>
        @endif
        <div class="header-text">
            <h1>DATA KEHADIRAN SISWA TAHUNAN</h1>
            <h2>SMK YAPIA PARUNG</h2>
            <h2>Kelas {{ $kelas }} {{ $kelompok }} - {{ $jurusan }}</h2>
            <h2>Tahun Ajaran {{ $tahun }}</h2>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">NO</th>
                <th rowspan="2" class="nama-siswa-cell">NAMA SISWA</th>
                @php
                    $months = [
                        'Juli',
                        'Agustus',
                        'September',
                        'Oktober',
                        'November',
                        'Desember',
                        'Januari',
                        'Februari',
                        'Maret',
                        'April',
                        'Mei',
                        'Juni',
                    ];
                    $colspan = 5;
                    [$startYear, $endYear] = explode('-', $tahun);
                @endphp
                @foreach ($months as $month)
                    @php
                        $monthNumber = array_search($month, $months) + 7;
                        if ($monthNumber > 12) {
                            $monthNumber -= 12;
                        }
                        $year = $monthNumber >= 7 ? $startYear : $endYear;
                    @endphp
                    <th colspan="{{ $colspan }}" class="month-cell">
                        <div class="month-header">
                            <div>{{ strtoupper($month) }}</div>
                            <div>{{ $year }}</div>
                        </div>
                    </th>
                @endforeach
            </tr>
            <tr>
                @for ($i = 0; $i < 12; $i++)
                    <th class="telat-cell">
                        <div class="vertical-text-container"><span class="vertical-text">Telat</span></div>
                    </th>
                    <th>
                        <div class="vertical-text-container"><span class="vertical-text">Sakit</span></div>
                    </th>
                    <th>
                        <div class="vertical-text-container"><span class="vertical-text">Izin</span></div>
                    </th>
                    <th>
                        <div class="vertical-text-container"><span class="vertical-text">Alfa</span></div>
                    </th>
                    <th>
                        <div class="vertical-text-container"><span class="vertical-text">Bolos</span></div>
                    </th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @if (isset($rekapAbsensi) && !empty($rekapAbsensi))
                @foreach ($rekapAbsensi as $index => $studentRekap)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="text-align: left;">{{ $studentRekap['nama'] }}</td>
                        @foreach ($studentRekap['total_bulanan'] as $bulanData)
                            <td class="telat-cell">
                                {{ $bulanData['counts']['telat'] > 0 ? $bulanData['counts']['telat'] : '' }}</td>
                            <td>{{ $bulanData['counts']['sakit'] > 0 ? $bulanData['counts']['sakit'] : '' }}</td>
                            <td>{{ $bulanData['counts']['izin'] > 0 ? $bulanData['counts']['izin'] : '' }}</td>
                            <td>{{ $bulanData['counts']['alfa'] > 0 ? $bulanData['counts']['alfa'] : '' }}</td>
                            <td>{{ $bulanData['counts']['bolos'] > 0 ? $bulanData['counts']['bolos'] : '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
                <tr style="font-weight: bold; height: 30px;">
                    <td colspan="2" style="text-align: center;">Jumlah</td>
                    @if (isset($rekapAbsensi[0]) && isset($rekapAbsensi[0]['total_bulanan']))
                        @php
                            $months = [
                                'Juli',
                                'Agustus',
                                'September',
                                'Oktober',
                                'November',
                                'Desember',
                                'Januari',
                                'Februari',
                                'Maret',
                                'April',
                                'Mei',
                                'Juni',
                            ];
                        @endphp
                        @foreach ($months as $monthKey => $month)
                            @php
                                $telat_total = collect($rekapAbsensi)->sum(
                                    fn($s) => $s['total_bulanan'][$monthKey]['counts']['telat'] ?? 0,
                                );
                                $sakit_total = collect($rekapAbsensi)->sum(
                                    fn($s) => $s['total_bulanan'][$monthKey]['counts']['sakit'] ?? 0,
                                );
                                $izin_total = collect($rekapAbsensi)->sum(
                                    fn($s) => $s['total_bulanan'][$monthKey]['counts']['izin'] ?? 0,
                                );
                                $alfa_total = collect($rekapAbsensi)->sum(
                                    fn($s) => $s['total_bulanan'][$monthKey]['counts']['alfa'] ?? 0,
                                );
                                $bolos_total = collect($rekapAbsensi)->sum(
                                    fn($s) => $s['total_bulanan'][$monthKey]['counts']['bolos'] ?? 0,
                                );
                            @endphp
                            <td class="telat-cell">{{ $telat_total }}</td>
                            <td>{{ $sakit_total }}</td>
                            <td>{{ $izin_total }}</td>
                            <td>{{ $alfa_total }}</td>
                            <td>{{ $bolos_total }}</td>
                        @endforeach
                    @endif
                </tr>
            @else
                <tr>
                    <td colspan="{{ 2 + 5 * 12 }}">Tidak ada data absensi untuk tahun ajaran ini.</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>

</html>
