<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafik Absensi Tahunan</title>
    <style>
        @page {
            margin: 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
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

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table th,
        .summary-table td {
            border: 1px solid #333;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 9px;
        }

        .summary-table th {
            font-weight: bold;
            background-color: #C4D79B;
        }

        .summary-table tfoot td {
            font-weight: bold;
        }

        .text-left {
            text-align: left;
        }

        .chart-container {
            border: 1px solid #ccc;
            padding: 10px;
        }

        .chart-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .chart-area {
            width: 100%;
            height: 150px;
            border-left: 1px solid #333;
            border-bottom: 1px solid #333;
            display: table;
            table-layout: fixed;
        }

        .bar-wrapper {
            display: table-cell;
            vertical-align: bottom;
            text-align: center;
            position: relative;
        }

        .bar {
            width: 60%;
            margin: 0 auto;
            display: block;
        }

        .bar-label {
            font-size: 8px;
        }

        .bar-value {
            font-size: 8px;
            margin-bottom: 2px;
        }
    </style>
</head>

<body>
    <div class="header">
        @if (isset($logoPath) && file_exists(public_path($logoPath)))
            <div class="logo-container">
                <img src="{{ public_path($logoPath) }}" alt="Logo">
            </div>
        @endif
        <div class="header-text">
            <h1>GRAFIK ABSENSI TAHUNAN</h1>
            <h2>SMK YAPIA PARUNG</h2>
            <h2>Kelas {{ $kelas }} {{ $kelompok }} - {{ $jurusan }}</h2>
            <h2>Tahun Ajaran {{ $tahun }}</h2>
        </div>
    </div>

    @php
        $charts = [
            ['title' => 'DATA TELAT', 'data' => $chartData['telat'], 'color' => '#3b82f6'],
            ['title' => 'DATA ALFA', 'data' => $chartData['alfa'], 'color' => '#ef4444'],
            ['title' => 'DATA SAKIT', 'data' => $chartData['sakit'], 'color' => '#f59e0b'],
            ['title' => 'DATA IZIN', 'data' => $chartData['izin'], 'color' => '#6b7280'],
            ['title' => 'DATA BOLOS', 'data' => $chartData['bolos'], 'color' => '#dc2626'],
        ];
        $maxValue = 0;
        foreach ($charts as $chart) {
            $maxInData = $chart['data']->max();
            if ($maxInData > $maxValue) {
                $maxValue = $maxInData;
            }
        }
        $maxValue = $maxValue > 0 ? $maxValue : 1;
    @endphp

    <table style="width: 100%; border-spacing: 15px; border-collapse: separate;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="chart-container">
                    <div class="chart-title">RINGKASAN DATA TAHUNAN</div>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">BULAN</th>
                                <th>T</th>
                                <th>A</th>
                                <th>S</th>
                                <th>I</th>
                                <th>B</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chartData['labels'] as $index => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td>{{ $chartData['telat'][$index] ?: '' }}</td>
                                    <td>{{ $chartData['alfa'][$index] ?: '' }}</td>
                                    <td>{{ $chartData['sakit'][$index] ?: '' }}</td>
                                    <td>{{ $chartData['izin'][$index] ?: '' }}</td>
                                    <td>{{ $chartData['bolos'][$index] ?: '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Jumlah</td>
                                <td>{{ $chartData['telat']->sum() }}</td>
                                <td>{{ $chartData['alfa']->sum() }}</td>
                                <td>{{ $chartData['sakit']->sum() }}</td>
                                <td>{{ $chartData['izin']->sum() }}</td>
                                <td>{{ $chartData['bolos']->sum() }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="chart-container">
                    @php $chart = $charts[0]; @endphp
                    <div class="chart-title">{{ $chart['title'] }}</div>
                    <div class="chart-area">
                        @foreach ($chart['data'] as $index => $value)
                            <div class="bar-wrapper">
                                <div class="bar-value">{{ $value }}</div>
                                <div class="bar"
                                    style="height: {{ ($value / $maxValue) * 120 }}px; background-color: {{ $chart['color'] }};">
                                </div>
                                <div class="bar-label">{{ $chartData['labels'][$index] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="chart-container">
                    @php $chart = $charts[1]; @endphp
                    <div class="chart-title">{{ $chart['title'] }}</div>
                    <div class="chart-area">
                        @foreach ($chart['data'] as $index => $value)
                            <div class="bar-wrapper">
                                <div class="bar-value">{{ $value }}</div>
                                <div class="bar"
                                    style="height: {{ ($value / $maxValue) * 120 }}px; background-color: {{ $chart['color'] }};">
                                </div>
                                <div class="bar-label">{{ $chartData['labels'][$index] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="chart-container">
                    @php $chart = $charts[2]; @endphp
                    <div class="chart-title">{{ $chart['title'] }}</div>
                    <div class="chart-area">
                        @foreach ($chart['data'] as $index => $value)
                            <div class="bar-wrapper">
                                <div class="bar-value">{{ $value }}</div>
                                <div class="bar"
                                    style="height: {{ ($value / $maxValue) * 120 }}px; background-color: {{ $chart['color'] }};">
                                </div>
                                <div class="bar-label">{{ $chartData['labels'][$index] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="chart-container">
                    @php $chart = $charts[3]; @endphp
                    <div class="chart-title">{{ $chart['title'] }}</div>
                    <div class="chart-area">
                        @foreach ($chart['data'] as $index => $value)
                            <div class="bar-wrapper">
                                <div class="bar-value">{{ $value }}</div>
                                <div class="bar"
                                    style="height: {{ ($value / $maxValue) * 120 }}px; background-color: {{ $chart['color'] }};">
                                </div>
                                <div class="bar-label">{{ $chartData['labels'][$index] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="chart-container">
                    @php $chart = $charts[4]; @endphp
                    <div class="chart-title">{{ $chart['title'] }}</div>
                    <div class="chart-area">
                        @foreach ($chart['data'] as $index => $value)
                            <div class="bar-wrapper">
                                <div class="bar-value">{{ $value }}</div>
                                <div class="bar"
                                    style="height: {{ ($value / $maxValue) * 120 }}px; background-color: {{ $chart['color'] }};">
                                </div>
                                <div class="bar-label">{{ $chartData['labels'][$index] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>
