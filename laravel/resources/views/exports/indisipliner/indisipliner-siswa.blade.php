<!DOCTYPE html>
<html>

<head>
    <title>Surat Pernyataan Indisipliner</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .header-table .logo-cell {
            width: 10%;
            text-align: left;
        }

        .header-table .logo-cell img {
            width: 60px;
            height: auto;
        }

        .header-table .title-cell {
            width: 80%;
            text-align: center;
        }

        .header-table .title-cell h1,
        .header-table .title-cell p {
            margin: 0;
            font-weight: bold;
        }

        .header-table .title-cell h1 {
            font-size: 16px;
        }

        .header-table .title-cell p {
            font-size: 14px;
        }

        .header-table .no-urut-cell {
            width: 10%;
            text-align: right;
        }

        .header-table .no-urut-box {
            width: 80px;
            margin-left: auto;
        }

        .header-table .no-urut-label {
            padding: 5px;
            background-color: #C4D79B;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            margin-bottom: -13px;
            border-top: 1px solid #000;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
        }

        .header-table .no-urut-value {
            padding: 5px;
            font-size: 12px;
            text-align: center;
            border: 1px solid #000;
        }

        .section-siswa {
            margin-top: 50px;
            margin-bottom: 20px;
        }

        .section-siswa table {
            width: 100%;
            border-collapse: collapse;
        }

        .section-siswa table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .section-siswa table td:first-child {
            width: 25%;
        }

        .section-siswa table td:nth-child(2) {
            width: 5%;
        }

        .section-siswa table td:last-child {
            font-weight: bold;
        }

        .statement {
            text-align: justify;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        table.indisipliner-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .indisipliner-table th,
        .indisipliner-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .indisipliner-table th {
            background-color: #C4D79B;
            text-align: center;
        }

        .signatures {
            margin-top: 50px;
            width: 100%;
        }

        .signatures table {
            width: 100%;
            border: none;
        }

        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0;
            border: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="{{ public_path('images/logo-smk.png') }}" alt="Logo SMK">
                </td>
                <td class="title-cell">
                    <h1>SURAT PERNYATAAN SIKAP INDISIPLINER</h1>
                    <p style="margin-top: 5px;">SMK YAPIA PARUNG</p>
                    <p style="margin-top: 5px;">Tahun Ajaran {{ $tahun ?? '2025-2026' }}</p>
                </td>
                <td class="no-urut-cell">
                    <div class="no-urut-box">
                        <p class="no-urut-label">NO. URUT</p>
                        <p class="no-urut-value">{{ $noUrut ?? '' }}</p>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section-siswa">
            <table>
                <tr>
                    <td>Yang bertanda tangan di bawah ini :</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td>{{ $siswa->nama }}</td>
                </tr>
                <tr>
                    <td>Nomor Induk Siswa</td>
                    <td>:</td>
                    <td>{{ $siswa->nis }}</td>
                </tr>
                <tr>
                    <td>Kelas</td>
                    <td>:</td>
                    <td>{{ $siswa->kelas->nama_kelas }} {{ $siswa->kelas->kelompok }} -
                        {{ $siswa->kelas->jurusan->nama_jurusan }}</td>
                </tr>
            </table>
        </div>

        <div class="statement">
            Dengan ini saya menyatakan bahwa saya tidak akan mengulangi perbuatan saya dengan melanggar tata tertib SMK
            YAPIA Parung. Adapun Sikap Indisipliner yang telah dilakukan adalah sebagai berikut:
        </div>

        <table class="indisipliner-table">
            <thead>
                <tr>
                    <th style="text-align: center;">NO</th>
                    <th style="text-align: center;">URAIAN INDISIPLINER</th>
                    <th style="text-align: center;">POIN</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPoin = 0;
                    $rowNumber = 1;
                    $details = $indisiplinerData->flatMap->details;
                @endphp

                @if ($details->isNotEmpty())
                    @foreach ($details as $detail)
                        <tr>
                            <td style="text-align: center;">{{ $rowNumber++ }}</td>
                            <td>{{ $detail->jenis_pelanggaran ?? '-' }}</td>
                            <td style="text-align: center;">
                                @if ($detail->poin)
                                    {{ $detail->poin }}
                                    @php $totalPoin += $detail->poin; @endphp
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif

                @if ($rowNumber <= 6)
                    @for ($i = $rowNumber; $i <= 6; $i++)
                        <tr>
                            <td style="text-align: center;">{{ $i }}</td>
                            <td>-</td>
                            <td style="text-align: center;">-</td>
                        </tr>
                    @endfor
                @endif

                <tr>
                    <td colspan="2" style="font-weight: bold; text-align: center;">JUMLAH POINT</td>
                    <td style="text-align: center;">{{ $totalPoin }}</td>
                </tr>
            </tbody>
        </table>

        <div class="statement">
            Saya sanggup mematuhi segala peraturan dan tata tertib yang ada di SMK Yapia Parung. Apabila dikemudian hari
            saya melakukan pelanggaran tata tertib sekolah, maka saya siap mendapatkan sanksi sesuai peraturan dan tata
            tertib yang berlaku di SMK Yapia Parung sampai dengan dikembalikan kepada orang tua/wali.
        </div>

        <div class="statement">
            Demikian Surat ini saya buat dalam keadaan sehat jasmani dan rohani serta dipertanggung jawabkan sebagaimana
            mestinya.
        </div>
        <div class="signatures">
            <table>
                <tr>
                    <td style="text-align: center; vertical-align: top;">
                        <p style="text-align: start; margin-right: 150px;">Mengetahui,</p>
                        <p style="text-align: start; margin-right: 150px;">Orang Tua/Wali</p>
                        <br><br><br>
                        <p style="text-align: start; margin-right: 150px;">(.........................................)
                        </p>
                    </td>
                    <td style="text-align: center; vertical-align: top;">
                        <p style="text-align: end; margin-left: 150px;">Pembuat Pernyataan</p>
                        <br><br><br><br><br>
                        <p style="text-align: end; margin-left: 150px;">(.........................................)
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
