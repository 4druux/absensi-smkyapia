<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Uang Kas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        h1,
        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .paid {
            color: green;
            font-weight: bold;
        }

        .unpaid {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h2>Laporan Uang Kas</h2>
    <h1>Kelas {{ $kelas }} - {{ $jurusan }}</h1>
    <h2>Bulan {{ $namaBulan }} Tahun {{ $tahun }}</h2>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%; text-align: left;">Nama Siswa</th>
                <th>Nominal</th>
                @foreach (range(1, 5) as $week)
                    <th>Minggu ke-{{ $week }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: left;">{{ $student->nama }}</td>
                    <td>
                        @php
                            $nominal = 0;
                            for ($week = 1; $week <= 5; $week++) {
                                if (isset($uangKasData[$student->id . '_' . $week])) {
                                    $nominal = $uangKasData[$student->id . '_' . $week]['nominal'];
                                    break;
                                }
                            }
                        @endphp
                        {{ number_format($nominal, 0, ',', '.') }}
                    </td>
                    @foreach (range(1, 5) as $week)
                        <td>
                            @php
                                $status = $uangKasData->get($student->id . '_' . $week)['status'] ?? '-';
                            @endphp
                            @if ($status === 'paid')
                                <span class="paid">✓</span>
                            @elseif($status === 'unpaid')
                                <span class="unpaid">✗</span>
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
