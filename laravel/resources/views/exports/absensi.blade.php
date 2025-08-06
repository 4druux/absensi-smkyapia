<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi</title>
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
    </style>
</head>

<body>
    <h2>Laporan Absensi</h2>
    <h1>Kelas {{ $kelas }} - {{ $jurusan }}</h1>
    <h2>Bulan {{ $namaBulan }} Tahun {{ $tahun }}</h2>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Nama Siswa</th>
                @foreach (range(1, $daysInMonth) as $day)
                    <th style="width: 2%;">{{ $day }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: left;">{{ $student->nama }}</td>
                    @foreach (range(1, $daysInMonth) as $day)
                        <td>
                            @php
                                $status = $absensiData->get($student->id . '_' . $day, '-');
                            @endphp
                            {{ $status }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
