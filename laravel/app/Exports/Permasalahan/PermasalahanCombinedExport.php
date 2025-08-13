<?php

namespace App\Exports\Permasalahan;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PermasalahanCombinedExport implements WithEvents, WithTitle
{
    protected $permasalahanKelas;
    protected $permasalahanSiswa;
    protected $kelas;
    protected $kelompok;
    protected $jurusan;
    protected $tahun;

    public function __construct($data, $kelas, $kelompok, $jurusan, $tahun)
    {
        $this->permasalahanKelas = $data['kelas'];
        $this->permasalahanSiswa = $data['siswa'];
        $this->kelas = $kelas;
        $this->kelompok = $kelompok;
        $this->jurusan = $jurusan;
        $this->tahun = $tahun;
    }

    public function title(): string
    {
        return 'Laporan Permasalahan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $currentRow = 1;

                $sheet->setCellValue('A1', 'LAPORAN KEADAAN PERMASALAHAN KELAS');
                $sheet->setCellValue('A2', 'SMK YAPIA PARUNG');
                $sheet->setCellValue('A3', "Kelas {$this->kelas} {$this->kelompok} - {$this->jurusan}");
                $sheet->setCellValue('A4', "TAHUN AJARAN {$this->tahun}");

                $sheet->setCellValue('H1', 'LAPORAN KEADAAN PERMASALAHAN SISWA');
                $sheet->setCellValue('H2', 'SMK YAPIA PARUNG');
                $sheet->setCellValue('H3', "Kelas {$this->kelas} {$this->kelompok} - {$this->jurusan}");
                $sheet->setCellValue('H4', "TAHUN AJARAN {$this->tahun}");

                $sheet->getStyle('A1:N4')->getFont()->setBold(true);
                $sheet->getStyle('A1:N4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('A4:F4');
                $sheet->mergeCells('H1:M1');
                $sheet->mergeCells('H2:M2');
                $sheet->mergeCells('H3:M3');
                $sheet->mergeCells('H4:M4');

                $currentRow = 6;
                $headerKelas = ['NO', 'TGL/BLN/TAHUN', 'KELAS', 'MASALAH KELAS', 'PEMECAHAN MASALAH', 'KET'];
                $sheet->fromArray($headerKelas, null, "A{$currentRow}");
                $startHeaderKelas = $currentRow;
                $currentRow++;

                foreach ($this->permasalahanKelas as $index => $problem) {
                    $sheet->fromArray([
                        $index + 1,
                        \Carbon\Carbon::parse($problem->tanggal)->format('d/m/Y'),
                        "{$this->kelas} {$this->kelompok} - {$this->jurusan}",
                        $problem->masalah,
                        $problem->pemecahan,
                        $problem->keterangan,

                    ], null, "A{$currentRow}");
                    $currentRow++;
                }

                $endTableKelas = $currentRow - 1;
                $sheet->getStyle("A{$startHeaderKelas}:F{$endTableKelas}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A{$startHeaderKelas}:F{$startHeaderKelas}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC4D79B');
                $sheet->getStyle("A" . ($startHeaderKelas + 1) . ":F{$endTableKelas}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
                $sheet->getStyle("A" . ($startHeaderKelas) . ":F{$startHeaderKelas}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                

                $currentRow = 6;
                $headerSiswa = ['NO', 'TGL/BLN/TAHUN', 'NAMA SISWA', 'KELAS', 'MASALAH', 'TINDAKAN WALAS', 'KET'];
                $sheet->fromArray($headerSiswa, null, "H{$currentRow}");
                $startHeaderSiswa = $currentRow;
                $currentRow++;

                foreach ($this->permasalahanSiswa as $index => $problem) {
                    $sheet->fromArray([
                        $index + 1,
                        \Carbon\Carbon::parse($problem->tanggal)->format('d/m/Y'),
                        $problem->siswa->nama,
                        "{$this->kelas} {$this->kelompok} - {$this->jurusan}",
                        $problem->masalah,
                        $problem->tindakan_walas,
                        $problem->keterangan,
                    ], null, "H{$currentRow}");
                    $currentRow++;
                }
                $endTableSiswa = $currentRow - 1;
                $sheet->getStyle("H{$startHeaderSiswa}:N{$endTableSiswa}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("H{$startHeaderSiswa}:N{$startHeaderSiswa}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC4D79B');
                $sheet->getStyle("H" . ($startHeaderSiswa + 1) . ":N{$endTableSiswa}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
                $sheet->getStyle("H" . ($startHeaderSiswa) . ":N{$startHeaderSiswa}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(40);
                $sheet->getColumnDimension('E')->setWidth(40);
                $sheet->getColumnDimension('F')->setWidth(40);

                $sheet->getColumnDimension('G')->setWidth(5);
                
                $sheet->getColumnDimension('H')->setWidth(5);
                $sheet->getColumnDimension('I')->setWidth(18);
                $sheet->getColumnDimension('J')->setWidth(18);
                $sheet->getColumnDimension('K')->setWidth(18);
                $sheet->getColumnDimension('L')->setWidth(40);
                $sheet->getColumnDimension('M')->setWidth(40);
                $sheet->getColumnDimension('N')->setWidth(40);
            },
        ];
    }
}