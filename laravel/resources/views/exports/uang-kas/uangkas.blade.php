<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Uang Kas Bulanan</title>
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


        .holiday-cell {
            background-color: #D21A1A;
        }

        .legend-table {
            width: 30%;
            margin-top: 20px;
            margin-left: 0;
            font-size: 10px;
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
            <h1>DATA UANG KAS SISWA BULANAN</h1>
            <h2>SMK YAPIA PARUNG</h2>
            <h2>Kelas {{ $kelas }} {{ $kelompok }} - {{ $jurusan }}</h2>
            <h2>Periode {{ $namaBulan }} {{ $year }}</h2>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="3" style="width: 5%;">No</th>
                <th rowspan="3" style="width: 30%; text-align: center;">Nama Siswa</th>
                <th colspan="5">Mingguan</th>
                <th rowspan="3">Total</th>
                <th rowspan="3">Status</th>
            </tr>
            <tr>
                @foreach (range(1, 5) as $week)
                    <th class="{{ $weeksInMonth[$week] ? 'holiday-cell' : '' }}">
                        Minggu-{{ $week }}
                    </th>
                @endforeach
            </tr>
            <tr>
                @php
                    $totalNominalMingguan = [];
                @endphp
                @foreach (range(1, 5) as $week)
                    @php
                        $nominal = 0;
                        if (!$weeksInMonth[$week]) {
                            foreach ($students as $student) {
                                if (isset($uangKasData[$student->id . '_' . $week])) {
                                    $nominal = $uangKasData[$student->id . '_' . $week]->nominal;
                                    break;
                                }
                            }
                        }
                        $totalNominalMingguan[$week] = $nominal;
                    @endphp
                    <th class="{{ $weeksInMonth[$week] ? 'holiday-cell' : '' }}">
                        @if (!$weeksInMonth[$week])
                            {{ number_format($nominal, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalNominal = 0;
                $mingguanTotalPaid = array_fill(1, 5, 0);
            @endphp
            @foreach ($students as $index => $student)
                @php
                    $studentTotal = 0;
                    $paidCount = 0;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: left;">{{ $student->nama }}</td>
                    @foreach (range(1, 5) as $week)
                        @if ($weeksInMonth[$week])
                            <td class="holiday-cell"></td>
                        @else
                            @php
                                $cellClass = isset($uangKasData[$student->id . '_' . $week]);

                                $displayStatus = isset($uangKasData[$student->id . '_' . $week]) ? '✓' : '✗';
                                if (isset($uangKasData[$student->id . '_' . $week])) {
                                    $studentTotal += $uangKasData[$student->id . '_' . $week]->nominal;
                                    $paidCount++;
                                    $mingguanTotalPaid[$week] += $uangKasData[$student->id . '_' . $week]->nominal;
                                }
                            @endphp
                            <td class="{{ $cellClass }}">
                                {{ $displayStatus }}
                            </td>
                        @endif
                    @endforeach
                    <td>{{ number_format($studentTotal, 0, ',', '.') }}</td>
                    <td>
                        @php
                            $nonHolidayWeeks = count(array_filter($weeksInMonth, fn($isHoliday) => !$isHoliday));
                        @endphp
                        @if ($paidCount === $nonHolidayWeeks)
                            Lunas
                        @elseif ($paidCount > 0)
                            Belum Lunas
                        @else
                            Belum Bayar
                        @endif
                    </td>
                </tr>
                @php
                    $grandTotalNominal += $studentTotal;
                @endphp
            @endforeach
            <tr style="height: 30px;">
                <td colspan="2" style="text-align: center;">Jumlah</td>
                @foreach (range(1, 5) as $week)
                    <td class="{{ $weeksInMonth[$week] ? 'holiday-cell' : '' }}">
                        @if (!$weeksInMonth[$week])
                            {{ number_format($mingguanTotalPaid[$week], 0, ',', '.') }}
                        @endif
                    </td>
                @endforeach
                <td>{{ number_format($grandTotalNominal, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <table class="legend-table">
        <thead>
            <tr>
                <th>Simbol</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>✓</td>
                <td>Sudah Bayar</td>
            </tr>
            <tr>
                <td>X</td>
                <td>Belum Bayar</td>
            </tr>
            <tr>
                <td class="holiday-cell"></td>
                <td>Hari Libur / Akhir Pekan</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
