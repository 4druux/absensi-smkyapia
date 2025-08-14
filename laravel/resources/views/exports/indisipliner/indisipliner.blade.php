<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Indisipliner</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
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

        .header-text h1 {
            font-size: 14px;
            margin: 0;
        }

        .header-text h2 {
            font-size: 12px;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 10px;
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
            background-color: #fabf8f;
            font-weight: bold;
            text-transform: uppercase;
        }

        .data-row {
            background-color: #EBF1DE;
        }

        .vertical-text-container {
            height: 40px;
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
            margin-top: 14px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="logo-container">
            <img src="{{ public_path('images/logo-smk.png') }}" alt="Logo SMK">
        </div>
        <div class="header-text">
            <h1 style="margin-top: 5px;">DATA INDISIPLINER SISWA</h1>
            <h2 style="margin-top: 5px;">SMK YAPIA PARUNG</h2>
            <h2 style="margin-top: 5px;">Kelas {{ $selectedClass->nama_kelas }} {{ $selectedClass->kelompok }} -
                {{ $selectedClass->jurusan->nama_jurusan }}</h2>
            <h2 style="margin-top: 5px;">Tahun Ajaran {{ $tahun }}</h2>
        </div>
    </div>

    @if ($indisiplinerData->isEmpty())
        <p style="text-align: center;">Tidak ada data indisipliner untuk tahun ajaran ini.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 2%;">No</th>
                    <th rowspan="2" style="width: 15%;">Nama Siswa</th>
                    <th rowspan="2" style="width: 8%;">Nomor Induk Siswa</th>
                    <th rowspan="2" style="width: 5%;">Kelas</th>
                    <th rowspan="2" style="width: 10%;">Jenis Surat</th>
                    <th rowspan="2" style="width: 10%;">Nomor Surat</th>
                    @php
                        $maxOtherViolations = 0;
                        foreach ($indisiplinerData as $data) {
                            $otherDetailsCount = $data->details
                                ->whereNotIn('jenis_pelanggaran', ['Terlambat', 'Alfa', 'Bolos'])
                                ->count();
                            if ($otherDetailsCount > $maxOtherViolations) {
                                $maxOtherViolations = $otherDetailsCount;
                            }
                        }
                    @endphp
                    <th colspan="{{ 3 + $maxOtherViolations * 2 }}" style="width: 45%; height: 20px;">
                        Tindakan
                        Indisipliner</th>
                    <th rowspan="2" style="width: 5%;">Tanggal Surat</th>
                </tr>
                <tr>
                    <th style="width: 2%;">
                        <div class="vertical-text-container"><span class="vertical-text">Telat</span></div>
                    </th>
                    <th style="width: 2%;">
                        <div class="vertical-text-container"><span class="vertical-text">Alfa</span></div>
                    </th>
                    <th style="width: 2%;">
                        <div class="vertical-text-container"><span class="vertical-text">Bolos</span></div>
                    </th>

                    @for ($i = 0; $i < $maxOtherViolations; $i++)
                        <th style="width: 10%;">Jenis Indisipliner Lain</th>
                        <th style="width: 2%;">
                            <div class="vertical-text-container"><span class="vertical-text">Point</span></div>
                        </th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach ($indisiplinerData as $index => $data)
                    @php
                        $terlambat = $data->details->firstWhere('jenis_pelanggaran', 'Terlambat');
                        $alfa = $data->details->firstWhere('jenis_pelanggaran', 'Alfa');
                        $bolos = $data->details->firstWhere('jenis_pelanggaran', 'Bolos');
                        $otherDetails = $data->details->whereNotIn('jenis_pelanggaran', ['Terlambat', 'Alfa', 'Bolos']);
                    @endphp
                    <tr class="data-row">
                        <td>{{ $index + 1 }}</td>
                        <td style="text-align: left;">{{ $data->siswa->nama ?? 'Siswa Dihapus' }}</td>
                        <td>{{ $data->siswa->nis ?? '-' }}</td>
                        <td>{{ $selectedClass->nama_kelas }} {{ $selectedClass->kelompok }}</td>
                        <td>{{ $data->jenis_surat ?? '-' }}</td>
                        <td>{{ $data->nomor_surat ?? '-' }}</td>
                        <td>{{ $terlambat ? $terlambat->poin : '-' }}</td>
                        <td>{{ $alfa ? $alfa->poin : '-' }}</td>
                        <td>{{ $bolos ? $bolos->poin : '-' }}</td>

                        @foreach ($otherDetails as $other)
                            <td style="text-align: center;">{{ $other->jenis_pelanggaran ?? '-' }}</td>
                            <td>{{ $other->poin ?? '-' }}</td>
                        @endforeach

                        @for ($i = $otherDetails->count(); $i < $maxOtherViolations; $i++)
                            <td style="text-align: center;">-</td>
                            <td>-</td>
                        @endfor

                        <td>{{ $data->tanggal_surat ? \Carbon\Carbon::parse($data->tanggal_surat)->translatedFormat('d F Y') : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
