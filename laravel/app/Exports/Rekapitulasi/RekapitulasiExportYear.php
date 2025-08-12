<?php

namespace App\Exports\Rekapitulasi;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithPreCalculateFormulas;

class RekapitulasiExportYear implements FromCollection, WithStyles, WithEvents, WithPreCalculateFormulas
{
    protected $rekapData;
    protected $kelas;
    protected $kelompok;
    protected $jurusan;
    protected $tahun;
    protected $dataRowsCount;

    public function __construct($rekapData, $kelas, $kelompok, $jurusan, $tahun)
    {
        $this->rekapData = $rekapData;
        $this->kelas = $kelas;
        $this->kelompok = $kelompok;
        $this->jurusan = $jurusan;
        $this->tahun = $tahun;
        $this->dataRowsCount = $rekapData->count();
    }

    public function collection()
    {
        $exportData = new Collection();
        
        $exportData->push(['REKAPITULASI DATA SISWA TAHUNAN']);
        $exportData->push(['SMK YAPIA PARUNG']);
        $exportData->push(["Kelas {$this->kelas} {$this->kelompok} - {$this->jurusan}"]);
        $exportData->push(["Tahun Ajaran {$this->tahun}"]);
        $exportData->push(['']);

        [$startYear, $endYear] = explode('-', $this->tahun);
        $periods = [
            "REKAPITULASI JULI-SEPT {$startYear}\nINI UNTUK DATA RAPORT PTS GANJIL",
            "REKAPITULASI JULI-DESEMBER\nINI UNTUK DATA RAPORT PAS GANJIL",
            "REKAPITULASI JAN-MARET {$endYear}\nINI UNTUK DATA RAPORT PTS GENAP",
            "REKAPITULASI JAN-JUNI {$endYear}\nINI UNTUK DATA RAPORT PAT GENAP",
            "REKAPITULASI JULI {$startYear} - JUNI {$endYear}\nINI UNTUK DATA KENAIKAN KELAS",
        ];

        $header1 = ['NO', 'NAMA'];
        foreach ($periods as $periodName) {
            $header1[] = $periodName;
            for ($i = 0; $i < 5; $i++) $header1[] = null;
        }
        $header1[] = "PERSENTASE KEHADIRAN SELAMA SETAHUN\nALFA, SAKIT DAN IJIN DIHITUNG";
        $header1[] = "PERSENTASE KEHADIRAN SELAMA SETAHUN\nHANYA ALFA";
        $exportData->push($header1);

        $header2 = ['', ''];
        for ($i = 0; $i < 5; $i++) {
            $header2[] = 'Telat';
            $header2[] = 'Alfa';
            $header2[] = 'Sakit';
            $header2[] = 'Izin';
            $header2[] = 'Bolos';
            $header2[] = "TOTAL POINT\nKUMULATIF";
        }
        $exportData->push($header2);

        foreach ($this->rekapData as $index => $student) {
            $rowData = [$index + 1, $student['nama']];
            foreach ($student['periods'] as $periodData) {
                $rowData[] = $periodData['telat'];
                $rowData[] = $periodData['alfa'];
                $rowData[] = $periodData['sakit'];
                $rowData[] = $periodData['izin'];
                $rowData[] = $periodData['bolos'];
                $rowData[] = $periodData['total_point_kumulatif'];
            }
            $rowData[] = $student['persentase_sia'] / 100;
            $rowData[] = $student['persentase_efektif'] / 100;
            $exportData->push($rowData);
        }
        
        return $exportData;
    }
    
    public function styles(Worksheet $sheet)
    {
        $lastCol = 'AH';
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Cambria')->setSize(11);
        
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->mergeCells("A4:{$lastCol}4");
        $sheet->getStyle('A1:A4')->getFont()->setBold(true)->setSize(14);

        $headerStartRow = 6;
        $headerEndRow = 7;
        
        $sheet->mergeCells("A{$headerStartRow}:A{$headerEndRow}");
        $sheet->mergeCells("B{$headerStartRow}:B{$headerEndRow}");
        
        for ($i = 0; $i < 5; $i++) {
            $startPeriodCol = Coordinate::stringFromColumnIndex(3 + ($i * 6));
            $endPeriodCol = Coordinate::stringFromColumnIndex(8 + ($i * 6));
            $sheet->mergeCells("{$startPeriodCol}{$headerStartRow}:{$endPeriodCol}{$headerStartRow}");
        }

        $sheet->mergeCells("AG{$headerStartRow}:AG{$headerEndRow}");
        $sheet->mergeCells("AH{$headerStartRow}:AH{$headerEndRow}");
        
        $sheet->getStyle("A{$headerStartRow}:{$lastCol}{$headerEndRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerStartRow}:{$lastCol}{$headerEndRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFABF8F');
        
        $lastDataRow = $headerEndRow + $this->dataRowsCount;
        $sheet->getStyle("A{$headerStartRow}:{$lastCol}{$lastDataRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $headerStartRow = 6;
                $headerEndRow = 7;
                $firstDataRow = $headerEndRow + 1;
                $lastDataRow = $firstDataRow + $this->dataRowsCount - 1;

                $sheet->getStyle("A1:AH4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$headerStartRow}:AH{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("B{$firstDataRow}:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                $sheet->getColumnDimension('A')->setWidth(4);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                
                $sheet->getRowDimension($headerStartRow)->setRowHeight(60);
                $sheet->getRowDimension($headerEndRow)->setRowHeight(70);

                for ($i = 0; $i < 5; $i++) {
                    $periodColStart = 3 + ($i * 6);
                    $sheet->getStyle(Coordinate::stringFromColumnIndex($periodColStart).$headerStartRow)->getAlignment()->setWrapText(true);
                    
                    for ($j = 0; $j < 6; $j++) {
                        $colIndex = $periodColStart + $j;
                        $col = Coordinate::stringFromColumnIndex($colIndex);
                        $sheet->getColumnDimension($col)->setWidth($j < 5 ? 5 : 8);
                        if ($j < 5) {
                            $sheet->getStyle("{$col}{$headerEndRow}")->getAlignment()->setTextRotation(90);
                        } else {
                            $sheet->getStyle("{$col}{$headerEndRow}")->getFont()->setSize(8);
                            $sheet->getStyle("{$col}{$headerEndRow}")->getAlignment()->setWrapText(true);
                        }
                    }
                }

                $sheet->getColumnDimension('AG')->setWidth(10);
                $sheet->getColumnDimension('AH')->setWidth(10);
                $sheet->getStyle("AG{$headerStartRow}:AH{$headerStartRow}")->getAlignment()->setWrapText(true)->setTextRotation(90);
                $sheet->getStyle("AG{$headerEndRow}:AH{$headerEndRow}")->getFont()->setSize(8);
                
                $dataRange = "C{$firstDataRow}:AF{$lastDataRow}";
                $sheet->getStyle($dataRange)->getNumberFormat()->setFormatCode('General');

                $percentRange = "AG{$firstDataRow}:AH{$lastDataRow}";
                $sheet->getStyle($percentRange)->getNumberFormat()->setFormatCode('0%');
            },
        ];
    }
}