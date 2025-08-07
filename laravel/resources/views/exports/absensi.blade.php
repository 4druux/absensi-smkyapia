<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi</title>
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
            background-color: #D9E1F2;
            font-weight: bold;
        }

        .header-cell {
            background-color: #D9E1F2;
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

        .hadir-cell {
            background-color: #C6EFCE;
        }

        .telat-cell {
            background-color: #FFEB9C;
        }

        .izin-sakit-cell {
            background-color: #D9D9D9;
        }

        .alfa-bolos-cell {
            background-color: #FFC7CE;
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
            <h1>SMK YAPIA PARUNG</h1>
            <h2>LAPORAN ABSENSI SISWA/I</h2>
            <h2>Kelas {{ $kelas }} - {{ $jurusan }}</h2>
            <h2>Periode {{ $namaBulan }} {{ $tahun }}</h2>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 5%;">No</th>
                <th rowspan="2" style="width: 15%;">Nama Siswa/i</th>
                <th colspan="{{ $daysInMonth }}" class="header-cell">Tanggal</th>
                <th colspan="5" class="header-cell">Total</th>
            </tr>
            <tr>
                @foreach (range(1, $daysInMonth) as $day)
                    <th>{{ $day }}</th>
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
                            $status = $absensiData->get($student->id . '_' . $day, '-');
                            $date = \Carbon\Carbon::createFromDate($tahun, $monthNumber, $day);
                            $isHoliday = $allHolidays->contains($date->day);

                            $cellClass = '';
                            if ($isHoliday) {
                                $cellClass = 'holiday-cell';
                                $displayStatus = '';
                            } else {
                                switch ($status) {
                                    case 'hadir':
                                        $cellClass = 'hadir-cell';
                                        $displayStatus = '✓';
                                        $counts['H']++;
                                        break;
                                    case 'telat':
                                        $cellClass = 'telat-cell';
                                        $displayStatus = 'T';
                                        $counts['T']++;
                                        break;
                                    case 'sakit':
                                        $cellClass = 'izin-sakit-cell';
                                        $displayStatus = 'S';
                                        $counts['S']++;
                                        break;
                                    case 'izin':
                                        $cellClass = 'izin-sakit-cell';
                                        $displayStatus = 'I';
                                        $counts['I']++;
                                        break;
                                    case 'alfa':
                                        $cellClass = 'alfa-bolos-cell';
                                        $displayStatus = 'A';
                                        $counts['A']++;
                                        break;
                                    case 'bolos':
                                        $cellClass = 'alfa-bolos-cell';
                                        $displayStatus = 'B';
                                        $counts['B']++;
                                        break;
                                    default:
                                        $displayStatus = '-';
                                        break;
                                }
                            }
                        @endphp
                        <td class="{{ $cellClass }}">{{ $displayStatus }}</td>
                    @endforeach
                    <td>{{ $counts['T'] }}</td>
                    <td>{{ $counts['S'] }}</td>
                    <td>{{ $counts['I'] }}</td>
                    <td>{{ $counts['A'] }}</td>
                    <td>{{ $counts['B'] }}</td>
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
                <td colspan="{{ 2 + $daysInMonth }}" style="text-align: center;">Jumlah</td>
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
                <td class="hadir-cell">✓</td>
                <td>Hadir</td>
            </tr>
            <tr>
                <td class="telat-cell">T</td>
                <td>Telat</td>
            </tr>
            <tr>
                <td class="izin-sakit-cell">S</td>
                <td>Sakit</td>
            </tr>
            <tr>
                <td class="izin-sakit-cell">I</td>
                <td>Izin</td>
            </tr>
            <tr>
                <td class="alfa-bolos-cell">A</td>
                <td>Alfa</td>
            </tr>
            <tr>
                <td class="alfa-bolos-cell">B</td>
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
