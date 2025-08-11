<?php

namespace App\Exports\Kenaikan;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class KenaikanExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;
    protected $kelas;
    protected $jurusan;
    protected $tahun;

    public function __construct($data, $kelas, $jurusan, $tahun)
    {
        $this->data = $data;
        $this->kelas = $kelas;
        $this->jurusan = $jurusan;
        $this->tahun = $tahun;
    }

    public function array(): array
    {
        $rows = [];
        // Method ini sekarang HANYA berisi data siswa
        foreach ($this->data as $index => $student) {
             $rows[] = [
                $index + 1,
                $student['nama'],
                $student['kehadiran_non_alfa'],
                $student['jumlah_nilai_kurang'],
                ($student['akhlak'] === 'Baik') ? '✓' : '',
                ($student['akhlak'] === 'Kurang') ? '✓' : '',
                ($student['rekomendasi_walas'] === 'Tidak Naik') ? '✓' : '',
                ($student['rekomendasi_walas'] === 'Ragu-ragu') ? '✓' : '',
                $student['keputusan_akhir']
            ];
        }
        return $rows;
    }

    public function headings(): array
    {
        // Baris 1-5 sengaja dibuat kosong untuk diisi oleh judul di method styles()
        return [
            [],
            [],
            [],
            [],
            [],
            // Header tabel utama dimulai di sini (baris ke-6)
            ['NO', 'NAMA', 'SYARAT KENAIKAN KELAS', null, null, null, 'REKOMENDASI WALAS', null, 'KEPUTUSAN AKHIR'],
            [null, null, 'KEHADIRAN NON ALFA', 'JUMLAH NILAI KURANG DARI KKM', 'AKHLAQ', null, 'TIDAK NAIK', 'RAGU-RAGU', null],
            [null, null, null, null, 'Baik', 'Kurang', null, null, null]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Sisipkan dan tulis judul laporan utama
        $sheet->setCellValue('A1', "DATA SISWA NAIK KELAS BERSYARAT");
        $sheet->setCellValue('A2', "SMK YAPIA PARUNG");
        $sheet->setCellValue('A3', "KELAS: {$this->kelas} - {$this->jurusan}");
        $sheet->setCellValue('A4', "TAHUN AJARAN: {$this->tahun}");

        // Merge sel untuk judul utama
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');
        $sheet->mergeCells('A4:I4');
        $sheet->getStyle('A1:I4')->getFont()->setBold(true);
        $sheet->getStyle('A1:I4')->getAlignment()->setHorizontal('center');
        
        // Merge sel untuk header tabel
        $sheet->mergeCells('A6:A8');
        $sheet->mergeCells('B6:B8');
        $sheet->mergeCells('C6:F6');
        $sheet->mergeCells('G6:H6');
        $sheet->mergeCells('I6:I8');
        $sheet->mergeCells('C7:C8');
        $sheet->mergeCells('D7:D8');
        $sheet->mergeCells('E7:F7');
        $sheet->mergeCells('G7:G8');
        $sheet->mergeCells('H7:H8');

        $lastRow = $this->data->count() + 8;
        $sheet->getStyle("A6:I{$lastRow}")->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getStyle("B9:B{$lastRow}")->getAlignment()->setHorizontal('left');
        $sheet->getStyle("I9:I{$lastRow}")->getAlignment()->setHorizontal('left')->setWrapText(true);
        $sheet->getStyle("A6:I8")->getFont()->setBold(true);
        $sheet->getStyle("A6:I8")->getFill()->setFillType('solid')->getStartColor()->setARGB('FFC4D79B');
        $sheet->getStyle("A6:I{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle('thin');
    }
}