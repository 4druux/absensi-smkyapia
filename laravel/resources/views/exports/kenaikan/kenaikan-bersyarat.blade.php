<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kenaikan Bersyarat</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; }
        .header { margin-bottom: 20px; }
        .logo-container { display: inline-block; vertical-align: top; width: 80px; }
        .logo-container img { width: 100%; }
        .header-text { display: inline-block; vertical-align: top; margin-left: 15px;}
        h1 { font-size: 14px; margin: 0;}
        h2 { font-size: 12px; margin: 0;}
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; vertical-align: middle; }
        th { font-weight: bold; background-color: #C4D79B; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        @if (isset($logoPath) && file_exists(public_path($logoPath)))
            <div class="logo-container"><img src="{{ public_path($logoPath) }}" alt="Logo"></div>
        @endif
        <div class="header-text">
            <h1>DATA SISWA NAIK KELAS BERSYARAT</h1>
            <h2>SMK YAPIA PARUNG</h2>
            <h2>KELAS: {{ $kelas }} - {{ $jurusan }} | TAHUN AJARAN: {{ $tahun }}</h2>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="3" style="width: 4%;">NO</th>
                <th rowspan="3" style="width: 20%;">NAMA</th>
                <th colspan="4">SYARAT KENAIKAN KELAS</th>
                <th colspan="2">REKOMENDASI WALAS</th>
                <th rowspan="3">KEPUTUSAN AKHIR</th>
            </tr>
            <tr>
                <th rowspan="2">KEHADIRAN NON ALFA</th>
                <th rowspan="2">JUMLAH NILAI KURANG DARI KKM</th>
                <th colspan="2">AKHLAQ</th>
                <th rowspan="2">TIDAK NAIK</th>
                <th rowspan="2">RAGU-RAGU</th>
            </tr>
            <tr>
                <th>Baik</th>
                <th>Kurang</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $student['nama'] }}</td>
                    <td>{{ $student['kehadiran_non_alfa'] }}</td>
                    <td>{{ $student['jumlah_nilai_kurang'] }}</td>
                    <td>{{ $student['akhlak'] === 'Baik' ? '✓' : '' }}</td>
                    <td>{{ $student['akhlak'] === 'Kurang' ? '✓' : '' }}</td>
                    <td>{{ $student['rekomendasi_walas'] === 'Tidak Naik' ? '✓' : '' }}</td>
                    <td>{{ $student['rekomendasi_walas'] === 'Ragu-ragu' ? '✓' : '' }}</td>
                    <td class="text-left">{{ $student['keputusan_akhir'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>