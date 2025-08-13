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

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-bold {
            font-weight: bold;
        }

        .holiday-cell {
            background-color: #D21A1A;
        }

        .main-summary-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .summary-wrapper {
            width: 65%;
            page-break-inside: avoid;
        }

        .legend-wrapper {
            width: 30%;
            page-break-inside: avoid;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table th,
        .summary-table td {
            border: 1px solid #000;
            padding: 2px 5px;
            vertical-align: middle;
        }

        .summary-table th {
            text-align: center;
            background-color: #c4d79b;
        }

        .summary-table .sub-item {
            padding-left: 20px;
        }

        .summary-table .total-row td {
            font-weight: bold;
        }

        .legend-table {
            width: 100%;
            border-collapse: collapse;
        }

        .legend-table th,
        .legend-table td {
            border: 1px solid #000;
            padding: 2px 5px;
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
                <th rowspan="3" style="width: 25%; text-align: center;">Nama Siswa</th>
                <th
                    colspan="{{ count(array_filter($weeksInMonth, fn($isHoliday) => !$isHoliday)) + $iuranData->count() }}">
                    Pemasukan</th>
                <th rowspan="3">Total</th>
                <th rowspan="3">Status</th>
            </tr>
            <tr>
                @foreach (range(1, 5) as $week)
                    @if (!$weeksInMonth[$week])
                        <th class="{{ $weeksInMonth[$week] ? 'holiday-cell' : '' }}">
                            Minggu-{{ $week }}
                        </th>
                    @endif
                @endforeach
                @foreach ($iuranData as $iuran)
                    <th>
                        {{ $iuran->deskripsi }}
                    </th>
                @endforeach
            </tr>
            <tr>
                @php
                    $totalNominalMingguan = [];
                    $totalPemasukanMingguan = 0;
                @endphp
                @foreach (range(1, 5) as $week)
                    @if (!$weeksInMonth[$week])
                        @php
                            $nominal = 0;
                            foreach ($students as $student) {
                                if (isset($uangKasData[$student->id . '_' . $week])) {
                                    $nominal = $uangKasData[$student->id . '_' . $week]->nominal;
                                    break;
                                }
                            }
                            $totalNominalMingguan[$week] = $nominal;
                            $totalPemasukanMingguan += $uangKasData
                                ->filter(fn($payment) => $payment->minggu == $week && $payment->status == 'paid')
                                ->sum('nominal');
                        @endphp
                        <th class="{{ $weeksInMonth[$week] ? 'holiday-cell' : '' }}">
                            {{ number_format($nominal, 0, ',', '.') }}
                        </th>
                    @endif
                @endforeach
                @foreach ($iuranData as $iuran)
                    @php
                        $nominalIuran = $iuran->payments->first()->nominal ?? 0;
                    @endphp
                    <th>{{ number_format($nominalIuran, 0, ',', '.') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalPemasukan = 0;
                $totalLunas = 0;
            @endphp
            @foreach ($students as $index => $student)
                @php
                    $studentTotalPemasukan = 0;
                    $mingguanLunas = true;
                    $iuranLunas = true;
                    $totalPaidWeekly = 0;
                    $nonHolidayWeeksCount = count(array_filter($weeksInMonth, fn($isHoliday) => !$isHoliday));
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $student->nama }}</td>
                    @foreach (range(1, 5) as $week)
                        @if (!$weeksInMonth[$week])
                            @php
                                $isPaid =
                                    isset($uangKasData[$student->id . '_' . $week]) &&
                                    $uangKasData[$student->id . '_' . $week]->status == 'paid';
                                $displayStatus = $isPaid ? '✓' : '✗';
                                if ($isPaid) {
                                    $studentTotalPemasukan += $uangKasData[$student->id . '_' . $week]->nominal;
                                    $totalPaidWeekly++;
                                } else {
                                    $mingguanLunas = false;
                                }
                            @endphp
                            <td>{{ $displayStatus }}</td>
                        @endif
                    @endforeach

                    @foreach ($iuranData as $iuran)
                        @php
                            $isPaidOther = $iuran->payments
                                ->where('siswa_id', $student->id)
                                ->where('status', 'paid')
                                ->first();
                            $displayStatusOther = $isPaidOther ? '✓' : '✗';
                            if ($isPaidOther) {
                                $studentTotalPemasukan += $isPaidOther->nominal;
                            } else {
                                $iuranLunas = false;
                            }
                        @endphp
                        <td>{{ $displayStatusOther }}</td>
                    @endforeach

                    <td>{{ number_format($studentTotalPemasukan, 0, ',', '.') }}</td>
                    <td>
                        @php
                            $isPaidAll = $totalPaidWeekly === $nonHolidayWeeksCount && $iuranLunas;
                        @endphp
                        @if ($isPaidAll)
                            Lunas
                            @php $totalLunas++; @endphp
                        @elseif ($studentTotalPemasukan > 0)
                            Belum Lunas
                        @else
                            Belum Bayar
                        @endif
                    </td>
                </tr>
                @php
                    $grandTotalPemasukan += $studentTotalPemasukan;
                @endphp
            @endforeach
            <tr style="height: 30px;">
                <td colspan="2" class="text-left text-bold" style="text-align: center;">
                    Total Pemasukan
                </td>
                @php
                    $mingguanTotalPaid = [];
                    foreach (range(1, 5) as $week) {
                        if (!$weeksInMonth[$week]) {
                            $mingguanTotalPaid[$week] = $uangKasData->where('minggu', $week)->sum('nominal');
                        }
                    }
                @endphp
                @foreach (range(1, 5) as $week)
                    @if (!$weeksInMonth[$week])
                        <td class="text-bold">{{ number_format($mingguanTotalPaid[$week], 0, ',', '.') }}</td>
                    @endif
                @endforeach
                @php
                    $iuranTotalPaid = [];
                    foreach ($iuranData as $iuran) {
                        $iuranTotalPaid[] = $iuran->payments->sum('nominal');
                    }
                @endphp
                @foreach ($iuranTotalPaid as $total)
                    <td class="text-bold">{{ number_format($total, 0, ',', '.') }}</td>
                @endforeach
                <td class="text-bold">{{ number_format($grandTotalPemasukan, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="main-summary-container">
        <div class="summary-wrapper">
            <h3 style="margin-top: 30px; margin-bottom: 5px;">Ringkasan Keuangan</h3>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Deskripsi</th>
                        <th style="width: 25%;">Pemasukan</th>
                        <th style="width: 25%;">Pengeluaran</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-bold">
                        <td class="text-left">Pemasukan</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="text-left sub-item">Pemasukan Mingguan</td>
                        <td class="text-right">{{ number_format($totalPemasukanMingguan, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                    @php
                        $totalPemasukanLainnya = 0;
                    @endphp
                    @foreach ($iuranData as $iuran)
                        @php
                            $totalIuran = $iuran->payments->where('status', 'paid')->sum('nominal');
                            $totalPemasukanLainnya += $totalIuran;
                        @endphp
                        <tr>
                            <td class="text-left sub-item">{{ $iuran->deskripsi }}</td>
                            <td class="text-right">{{ number_format($totalIuran, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td class="text-left">Total Pemasukan</td>
                        @php
                            $totalPemasukanKeseluruhan = $totalPemasukanMingguan + $totalPemasukanLainnya;
                        @endphp
                        <td class="text-right">{{ number_format($totalPemasukanKeseluruhan, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>

                    <tr class="text-bold">
                        <td class="text-left">Pengeluaran</td>
                        <td></td>
                        <td></td>
                    </tr>
                    @php
                        $totalPengeluaran = $pengeluaranData->sum('nominal');
                    @endphp
                    @if ($pengeluaranData->count() > 0)
                        @foreach ($pengeluaranData as $pengeluaran)
                            <tr>
                                <td class="text-left sub-item">{{ $pengeluaran->deskripsi }}</td>
                                <td></td>
                                <td class="text-right">{{ number_format($pengeluaran->nominal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="text-left sub-item">Tidak ada pengeluaran bulan ini</td>
                            <td></td>
                            <td class="text-right">0</td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td class="text-left">Total Pengeluaran</td>
                        <td></td>
                        <td class="text-right">{{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                    </tr>

                    <tr class="total-row" style="background-color: #c4d79b;">
                        <td class="text-left">SALDO AKHIR</td>
                        @php
                            $saldoAkhir = $totalPemasukanKeseluruhan - $totalPengeluaran;
                        @endphp
                        <td></td>
                        <td class="text-right">{{ number_format($saldoAkhir, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="legend-wrapper">
            <h3 style="margin-top: 30px; margin-bottom: 5px;">Keterangan</h3>
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
                        <td>✗</td>
                        <td>Belum Bayar</td>
                    </tr>
                    <tr>
                        <td class="holiday-cell"></td>
                        <td>Hari Libur / Akhir Pekan</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
