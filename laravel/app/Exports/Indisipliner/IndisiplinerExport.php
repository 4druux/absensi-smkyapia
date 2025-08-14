<?php

namespace App\Exports\Indisipliner;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Collection;

class IndisiplinerExport implements FromCollection, WithMapping, WithEvents
{
    protected $selectedKelas;
    protected $indisiplinerData;
    protected $tahun;
    protected $maxOtherViolations;
    protected $dataRowsCount;
    protected $firstDataRow;
    private $rowNumber = 0;

    public function __construct($selectedKelas, $indisiplinerData, $tahun)
    {
        $this->selectedKelas = $selectedKelas;
        $this->indisiplinerData = $indisiplinerData;
        $this->tahun = $tahun;

        $this->indisiplinerData = $indisiplinerData->sortBy('tanggal_surat')->values();

        $this->maxOtherViolations = 0;
        foreach ($this->indisiplinerData as $data) {
            $otherDetailsCount = $data->details->whereNotIn('jenis_pelanggaran', ['Terlambat', 'Alfa', 'Bolos'])->count();
            if ($otherDetailsCount > $this->maxOtherViolations) {
                $this->maxOtherViolations = $otherDetailsCount;
            }
        }
        $this->dataRowsCount = count($this->indisiplinerData);
        $this->firstDataRow = 8;
    }

    public function collection()
    {
        return $this->indisiplinerData;
    }

    public function map($data): array
    {
        $this->rowNumber++;
        $terlambat = $data->details->firstWhere('jenis_pelanggaran', 'Terlambat');
        $alfa = $data->details->firstWhere('jenis_pelanggaran', 'Alfa');
        $bolos = $data->details->firstWhere('jenis_pelanggaran', 'Bolos');
        $otherDetails = $data->details->whereNotIn('jenis_pelanggaran', ['Terlambat', 'Alfa', 'Bolos']);

        $rowData = [
            $this->rowNumber,
            $data->siswa->nama ?? 'Siswa Dihapus',
            $data->siswa->nis ?? '-',
            $this->selectedKelas->nama_kelas . ' ' . $this->selectedKelas->kelompok,
            $data->jenis_surat ?? '-',
            $data->nomor_surat ?? '-',
            $terlambat ? $terlambat->poin : '-',
            $alfa ? $alfa->poin : '-',
            $bolos ? $bolos->poin : '-',
        ];
        
        foreach ($otherDetails as $other) {
            $rowData[] = $other->jenis_pelanggaran ?? '-';
            $rowData[] = $other->poin ?? '-';
        }

        for ($i = $otherDetails->count(); $i < $this->maxOtherViolations; $i++) {
            $rowData[] = '-';
            $rowData[] = '-';
        }

        $rowData[] = $data->tanggal_surat ? \Carbon\Carbon::parse($data->tanggal_surat)->translatedFormat('d F Y') : '-';
        
        return $rowData;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $lastColIndex = 6 + 3 + ($this->maxOtherViolations * 2) + 1;
                $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);
                
                $sheet->insertNewRowBefore(1, 7);
                
                $sheet->setCellValue('A1', 'DATA INDISIPLINER SISWA');
                $sheet->setCellValue('A2', 'SMK YAPIA PARUNG');
                $sheet->setCellValue('A3', "Kelas {$this->selectedKelas->nama_kelas} {$this->selectedKelas->kelompok} - {$this->selectedKelas->jurusan->nama_jurusan}");
                $sheet->setCellValue('A4', "Tahun Ajaran {$this->tahun}");

                $headerStartRow = 6;
                $headerEndRow = $headerStartRow + 1;

                $sheet->setCellValue('A' . $headerStartRow, 'NO');
                $sheet->setCellValue('B' . $headerStartRow, 'NAMA SISWA');
                $sheet->setCellValue('C' . $headerStartRow, 'NOMOR INDUK SISWA');
                $sheet->setCellValue('D' . $headerStartRow, 'KELAS');
                $sheet->setCellValue('E' . $headerStartRow, 'JENIS SURAT');
                $sheet->setCellValue('F' . $headerStartRow, 'NOMOR SURAT');
                $sheet->setCellValue('G' . $headerStartRow, 'TINDAKAN INDISIPLINER');
                $sheet->setCellValue($lastCol . $headerStartRow, 'TANGGAL SURAT');

                $sheet->setCellValue('G' . $headerEndRow, 'TELAT');
                $sheet->setCellValue('H' . $headerEndRow, 'ALFA');
                $sheet->setCellValue('I' . $headerEndRow, 'BOLOS');

                $currentColIndex = 10;
                for ($i = 0; $i < $this->maxOtherViolations; $i++) {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($currentColIndex) . $headerEndRow, 'JENIS INDISIPLINER LAIN');
                    $currentColIndex++;
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($currentColIndex) . $headerEndRow, 'POINT');
                    $currentColIndex++;
                }

                $sheet->mergeCells('A1:' . $lastCol . '1');
                $sheet->mergeCells('A2:' . $lastCol . '2');
                $sheet->mergeCells('A3:' . $lastCol . '3');
                $sheet->mergeCells('A4:' . $lastCol . '4');
                
                $sheet->mergeCells('A' . $headerStartRow . ':A' . $headerEndRow);
                $sheet->mergeCells('B' . $headerStartRow . ':B' . $headerEndRow);
                $sheet->mergeCells('C' . $headerStartRow . ':C' . $headerEndRow);
                $sheet->mergeCells('D' . $headerStartRow . ':D' . $headerEndRow);
                $sheet->mergeCells('E' . $headerStartRow . ':E' . $headerEndRow);
                $sheet->mergeCells('F' . $headerStartRow . ':F' . $headerEndRow);
                $sheet->mergeCells(Coordinate::stringFromColumnIndex(7) . $headerStartRow . ':' . Coordinate::stringFromColumnIndex(9 + ($this->maxOtherViolations * 2)) . $headerStartRow);
                $sheet->mergeCells($lastCol . $headerStartRow . ':' . $lastCol . $headerEndRow);

                $headerStyle = [
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FABF8F'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ];
                
                $sheet->getStyle('A1:A4')->getFont()->setSize(14);
                $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $headerEndRow)->applyFromArray($headerStyle);
                
                $lastDataRow = $headerEndRow + $this->dataRowsCount;
                $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $lastDataRow)
                      ->getBorders()
                      ->getAllBorders()
                      ->setBorderStyle(Border::BORDER_THIN);
                
                $sheet->getStyle('A' . ($headerEndRow + 1) . ':' . $lastCol . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . ($headerEndRow + 1) . ':B' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $dataStyle = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EBF1DE'],
                    ],
                ];
                $sheet->getStyle('A' . ($headerEndRow + 1) . ':' . $lastCol . $lastDataRow)->applyFromArray($dataStyle);

                $sheet->getRowDimension($headerEndRow)->setRowHeight(40);
                
                $sheet->getStyle('G' . $headerEndRow)->getAlignment()->setTextRotation(90);
                $sheet->getStyle('H' . $headerEndRow)->getAlignment()->setTextRotation(90);
                $sheet->getStyle('I' . $headerEndRow)->getAlignment()->setTextRotation(90);

                $currentColIndex = 11;
                for ($i = 0; $i < $this->maxOtherViolations; $i++) {
                    $sheet->getStyle(Coordinate::stringFromColumnIndex($currentColIndex) . $headerEndRow)->getAlignment()->setTextRotation(90);
                    $currentColIndex += 2;
                }

                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);
                $sheet->getColumnDimension('F')->setAutoSize(true);
                $sheet->getColumnDimension($lastCol)->setAutoSize(true);
                
                $sheet->getColumnDimension('G')->setWidth(6);
                $sheet->getColumnDimension('H')->setWidth(6);
                $sheet->getColumnDimension('I')->setWidth(6);
                
                $currentColIndex = 10;
                for ($i = 0; $i < $this->maxOtherViolations; $i++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($currentColIndex))->setAutoSize(true);
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($currentColIndex + 1))->setWidth(6);
                    $currentColIndex += 2;
                }
            }
        ];
    }
}
