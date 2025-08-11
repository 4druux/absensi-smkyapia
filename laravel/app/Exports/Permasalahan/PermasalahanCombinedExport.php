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
    protected $jurusan;
    protected $tahun;

    public function __construct($data, $kelas, $jurusan, $tahun)
    {
        $this->permasalahanKelas = $data['kelas'];
        $this->permasalahanSiswa = $data['siswa'];
        $this->kelas = $kelas;
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
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $currentRow = 1;

                // --- Tabel Permasalahan Kelas ---
                $sheet->setCellValue("A{$currentRow}", 'LAPORAN KEADAAN PERMASALAHAN KELAS');
                $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
                $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true);
                $currentRow++;
                
                $headerKelas = ['NO', 'TGL/BLN/TAHUN', 'KELAS', 'MASALAH KELAS', 'PEMECAHAN MASALAH', 'KET'];
                $sheet->fromArray($headerKelas, null, "A{$currentRow}");
                $startHeaderKelas = $currentRow;
                $currentRow++;

                foreach($this->permasalahanKelas as $index => $problem) {
                    $sheet->fromArray([
                        $index + 1,
                        \Carbon\Carbon::parse($problem->tanggal)->format('d/m/Y'),
                        "{$this->kelas} {$this->jurusan}",
                        $problem->masalah,
                        $problem->pemecahan,
                        '', // Kolom KET kosong
                    ], null, "A{$currentRow}");
                    $currentRow++;
                }
                
                $endTableKelas = $currentRow - 1;
                $sheet->getStyle("A{$startHeaderKelas}:F{$endTableKelas}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A{$startHeaderKelas}:F{$startHeaderKelas}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC4D79B');
                $sheet->getStyle("D".($startHeaderKelas+1).":E{$endTableKelas}")->getAlignment()->setWrapText(true);


                // --- Jarak Antar Tabel ---
                $currentRow += 2;

                // --- Tabel Permasalahan Siswa ---
                $sheet->setCellValue("A{$currentRow}", 'LAPORAN KEADAAN PERMASALAHAN SISWA');
                $sheet->mergeCells("A{$currentRow}:G{$currentRow}");
                $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true);
                $currentRow++;

                $headerSiswa = ['NO', 'TGL/BLN/TAHUN', 'NAMA SISWA', 'KELAS', 'MASALAH', 'TINDAKAN WALAS', 'KET'];
                $sheet->fromArray($headerSiswa, null, "A{$currentRow}");
                $startHeaderSiswa = $currentRow;
                $currentRow++;

                foreach($this->permasalahanSiswa as $index => $problem) {
                    $sheet->fromArray([
                        $index + 1,
                        \Carbon\Carbon::parse($problem->tanggal)->format('d/m/Y'),
                        $problem->siswa->nama,
                        "{$this->kelas} {$this->jurusan}",
                        $problem->masalah,
                        $problem->tindakan_walas,
                        '', // Kolom KET kosong
                    ], null, "A{$currentRow}");
                    $currentRow++;
                }

                $endTableSiswa = $currentRow - 1;
                $sheet->getStyle("A{$startHeaderSiswa}:G{$endTableSiswa}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A{$startHeaderSiswa}:G{$startHeaderSiswa}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC4D79B');
                $sheet->getStyle("E".($startHeaderSiswa+1).":F{$endTableSiswa}")->getAlignment()->setWrapText(true);

                // Atur Lebar Kolom
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(40);
                $sheet->getColumnDimension('E')->setWidth(40);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(20);

                // Atur Alignment
                $sheet->getStyle("A1:G{$endTableSiswa}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            },
        ];
    }
}