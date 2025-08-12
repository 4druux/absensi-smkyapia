<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi Bulanan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
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
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #c4d79b;
            font-weight: bold;
        }

        .legend-table {
            width: 30%;
            margin-top: 20px;
            margin-left: 0;
            font-size: 10px;
        }

        .holiday-cell {
            background-color: #D21A1A;
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
            <h1>DATA KEHADIRAN SISWA BULANAN</h1>
            <h2>SMK YAPIA PARUNG</h2>
            <h2>Kelas {{ $kelas }} {{ $kelompok }} - {{ $jurusan }}</h2>
            <h2>Periode {{ $namaBulan }} {{ $year }}</h2>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">NO</th>
                <th rowspan="2" style="width: 15%;">NAMA SISWA</th>
                <th colspan="{{ $daysInMonth }}">TANGGAL</th>
                <th colspan="5">TOTAL</th>
            </tr>
            <tr>
                @foreach (range(1, $daysInMonth) as $day)
                    @php
                        $isHolidayHeader = $allHolidays->contains($day);
                    @endphp
                    <th class="{{ $isHolidayHeader ? 'holiday-cell' : '' }}">{{ $day }}</th>
                @endforeach
                <th>T</th>
                <th>S</th>
                <th>I</th>
                <th>A</th>
                <th>B</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalT = 0;
                $grandTotalS = 0;
                $grandTotalI = 0;
                $grandTotalA = 0;
                $grandTotalB = 0;
            @endphp
            @foreach ($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: left;">{{ $student->nama }}</td>
                    @php
                        $counts = ['H' => 0, 'T' => 0, 'S' => 0, 'I' => 0, 'A' => 0, 'B' => 0];
                    @endphp
                    @foreach (range(1, $daysInMonth) as $day)
                        @php
                            $status = $absensiData->get($student->id . '_' . $day, '');
                            $isHoliday = $allHolidays->contains($day);
                            $cellClass = '';
                            $displayStatus = '';

                            if ($isHoliday) {
                                $cellClass = 'holiday-cell';
                                $displayStatus = '';
                            } else {
                                switch ($status) {
                                    case 'hadir':
                                        $displayStatus = '✓';
                                        $counts['H']++;
                                        break;
                                    case 'telat':
                                        $displayStatus = 'T';
                                        $counts['T']++;
                                        break;
                                    case 'sakit':
                                        $displayStatus = 'S';
                                        $counts['S']++;
                                        break;
                                    case 'izin':
                                        $displayStatus = 'I';
                                        $counts['I']++;
                                        break;
                                    case 'alfa':
                                        $displayStatus = 'A';
                                        $counts['A']++;
                                        break;
                                    case 'bolos':
                                        $displayStatus = 'B';
                                        $counts['B']++;
                                        break;
                                    default:
                                        $displayStatus = '';
                                        break;
                                }
                            }
                        @endphp
                        <td class="{{ $cellClass }}">{{ $displayStatus }}</td>
                    @endforeach
                    <td>{{ $counts['T'] == 0 ? '' : $counts['T'] }}</td>
                    <td>{{ $counts['S'] == 0 ? '' : $counts['S'] }}</td>
                    <td>{{ $counts['I'] == 0 ? '' : $counts['I'] }}</td>
                    <td>{{ $counts['A'] == 0 ? '' : $counts['A'] }}</td>
                    <td>{{ $counts['B'] == 0 ? '' : $counts['B'] }}</td>
                </tr>
                @php
                    $grandTotalT += $counts['T'];
                    $grandTotalS += $counts['S'];
                    $grandTotalI += $counts['I'];
                    $grandTotalA += $counts['A'];
                    $grandTotalB += $counts['B'];
                @endphp
            @endforeach
            <tr style="height: 30px;">
                <td colspan="{{ 2 + $daysInMonth }}" style="text-align: center; font-weight: bold;">Jumlah</td>
                <td>{{ $grandTotalT }}</td>
                <td>{{ $grandTotalS }}</td>
                <td>{{ $grandTotalI }}</td>
                <td>{{ $grandTotalA }}</td>
                <td>{{ $grandTotalB }}</td>
            </tr>
        </tbody>
    </table>

    <table class="legend-table">
        <thead>
            <tr>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>✓</td>
                <td>Hadir</td>
            </tr>
            <tr>
                <td>T</td>
                <td>Telat</td>
            </tr>
            <tr>
                <td>S</td>
                <td>Sakit</td>
            </tr>
            <tr>
                <td>I</td>
                <td>Izin</td>
            </tr>
            <tr>
                <td>A</td>
                <td>Alfa</td>
            </tr>
            <tr>
                <td>B</td>
                <td>Bolos</td>
            </tr>
            <tr>
                <td class="holiday-cell"></td>
                <td>Hari Libur / Akhir Pekan</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
