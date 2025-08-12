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
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use Carbon\Carbon;

class RekapitulasiExportMonth implements FromCollection, WithStyles, WithEvents, WithPreCalculateFormulas
{
    protected $rekapData;
    protected $kelas;
    protected $kelompok;
    protected $jurusan;
    protected $tahun;
    protected $namaBulan;
    protected $activeDaysInMonth;
    protected $dataRowsCount;

    public function __construct($rekapData, $kelas, $kelompok, $jurusan, $tahun, $namaBulan, $activeDaysInMonth)
    {
        $this->rekapData = $rekapData;
        $this->kelas = $kelas;
        $this->kelompok = $kelompok;
        $this->jurusan = $jurusan;
        $this->tahun = $tahun;
        $this->namaBulan = $namaBulan;
        $this->activeDaysInMonth = $activeDaysInMonth;
        $this->dataRowsCount = $rekapData->count();
    }

    public function collection()
    {
        $exportData = new Collection();
        
        $exportData->push(['REKAPITULASI DATA SISWA BULANAN']);
        $exportData->push(['SMK YAPIA PARUNG']);
        $exportData->push(["Kelas {$this->kelas} {$this->kelompok} - {$this->jurusan}"]);
        $exportData->push(["Periode {$this->namaBulan}"]);
        $exportData->push(['']);

        $exportData->push(['NO', 'NAMA', 'REKAPITULASI', null, null, null, null, null, null, null, null, "Point Lainnya", "KETERANGAN (Tulis Jenis Pelanggaran Disertai dengan Tanggal Kejadian)", "Total Point Bulan Lalu", "Total Point Sampai Bulan Ini", "Deskripsi"]);
        $exportData->push([
            '', '',
            'Telat', 'Alfa', 'Sakit', 'Izin', 'Bolos',
            'Point Telat', 'Point Alfa', 'Point Bolos', 'Persentase',
            null, null, null, null, null
        ]);

        foreach ($this->rekapData as $index => $student) {
            $absensi = $student['absensi'];
            $exportData->push([
                $index + 1,
                $student['nama'],
                $absensi['telat'],
                $absensi['alfa'],
                $absensi['sakit'],
                $absensi['izin'],
                $absensi['bolos'],
                '', '', '', '', 
                '', '', 
                $student['total_point_bulan_lalu'] == 0 ? '0' : $student['total_point_bulan_lalu'],
                '', ''
            ]);
        }

        $exportData->push(['RATA-RATA KEHADIRAN PERBULAN']);
        
        return $exportData;
    }
    
    public function styles(Worksheet $sheet)
    {
        $lastCol = 'P';
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Cambria');
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
        $sheet->mergeCells("C{$headerStartRow}:K{$headerStartRow}");
        $sheet->mergeCells("L{$headerStartRow}:L{$headerEndRow}");
        $sheet->mergeCells("M{$headerStartRow}:M{$headerEndRow}");
        $sheet->mergeCells("N{$headerStartRow}:N{$headerEndRow}");
        $sheet->mergeCells("O{$headerStartRow}:O{$headerEndRow}");
        $sheet->mergeCells("P{$headerStartRow}:P{$headerEndRow}");
        
        $sheet->getStyle("A{$headerStartRow}:{$lastCol}{$headerEndRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerStartRow}:{$lastCol}{$headerEndRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFABF8F');

        $sheet->getStyle("L{$headerStartRow}:M{$headerEndRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFC4D79B');
        
        $lastDataRow = $headerEndRow + $this->dataRowsCount + 1;
        $sheet->getStyle("A{$headerStartRow}:{$lastCol}{$lastDataRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'P';
                $headerStartRow = 6;
                $headerEndRow = 7;
                $firstDataRow = $headerEndRow + 1;
                $lastDataRow = $firstDataRow + $this->dataRowsCount - 1;

                $sheet->getStyle("A1:{$lastCol}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$headerStartRow}:{$lastCol}{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("B{$firstDataRow}:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("M{$firstDataRow}:M{$lastDataRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("P{$firstDataRow}:P{$lastDataRow}")->getAlignment()->setWrapText(true);
                $sheet->getStyle("L{$headerStartRow}:P{$headerStartRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                      ->setVertical(Alignment::VERTICAL_CENTER)
                      ->setWrapText(true);

                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('L')->setWidth(9);
                $sheet->getColumnDimension('M')->setWidth(40);
                $sheet->getColumnDimension('N')->setWidth(8);
                $sheet->getColumnDimension('O')->setWidth(8);
                $sheet->getColumnDimension('P')->setWidth(35);
                $sheet->getRowDimension($headerStartRow)->setRowHeight(30);

                for ($colIndex = 3; $colIndex <= 11; $colIndex++) {
                    $col = Coordinate::stringFromColumnIndex($colIndex);
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $sheet->getStyle("{$col}{$headerEndRow}")->getAlignment()->setTextRotation(90);
                }

                for ($row = $firstDataRow; $row <= $lastDataRow; $row++) {
                    $sheet->setCellValue("H{$row}", "=C{$row}*0.5");
                    $sheet->setCellValue("I{$row}", "=D{$row}*1.5");
                    $sheet->setCellValue("J{$row}", "=G{$row}*2");
                    $sheet->setCellValue("O{$row}", "=SUM(H{$row}:I{$row},J{$row},L{$row},N{$row})");

                    if ($this->activeDaysInMonth > 0) {
                        $sheet->setCellValue("K{$row}", "=(" . $this->activeDaysInMonth . "-(D{$row}+E{$row}+F{$row}))/" . $this->activeDaysInMonth);
                    } else {
                        $sheet->setCellValue("K{$row}", 0);
                    }
                    
                    $deskripsiFormula = '=IF(O'.$row.'>=50, "Ananda diberikan Surat Peringatan 3", '.
                                        'IF(O'.$row.'>=43, "Ananda diberikan Surat Peringatan 2", '.
                                        'IF(O'.$row.'>=35, "Ananda diberikan Surat Peringatan 2", '.
                                        'IF(O'.$row.'>=28, "Ananda diberikan Surat Peringatan 1", '.
                                        'IF(O'.$row.'>=20, "Ananda diberikan Surat Peringatan 1", '.
                                        'IF(O'.$row.'>=15, "Ananda diberikan Surat Komitmen Ke-3", '.
                                        'IF(O'.$row.'>=10, "Ananda diberikan Surat Komitmen Ke-2", '.
                                        'IF(O'.$row.'>=5, "Ananda diberikan Surat Komitmen Ke-1", '.
                                        'IF(O'.$row.'>=1, "Motivasi dan Teguran dari Wali Kelas", "Aman")'.
                                        ')))))))))';
                    $sheet->setCellValue("P{$row}", $deskripsiFormula);
                    $sheet->getRowDimension($row)->setRowHeight(25);

                    $footerRow = $lastDataRow + 1;
                    $sheet->mergeCells("A{$footerRow}:J{$footerRow}");
                    $sheet->setCellValue("K{$footerRow}", "=AVERAGE(K{$firstDataRow}:K{$lastDataRow})");
                    $sheet->getStyle("A{$footerRow}:P{$footerRow}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFABF8F');
                    $sheet->getStyle("A{$footerRow}")->getFont()->setBold(true);
                    $sheet->getStyle("K{$footerRow}")->getFont()->setBold(true)->setSize(12);
                    $sheet->getRowDimension($footerRow)->setRowHeight(30);
                    $sheet->getStyle("A{$footerRow}:P{$footerRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                $conditionalRules = [
                    ['operator' => Conditional::OPERATOR_GREATERTHANOREQUAL, 'value' => 50, 'color' => 'FFFF0000'],
                    ['operator' => Conditional::OPERATOR_GREATERTHANOREQUAL, 'value' => 43, 'color' => 'FF538DD5'],
                    ['operator' => Conditional::OPERATOR_GREATERTHANOREQUAL, 'value' => 35, 'color' => 'FFFFFF00'],
                    ['operator' => Conditional::OPERATOR_GREATERTHANOREQUAL, 'value' => 28, 'color' => 'FF7030A0'],
                    ['operator' => Conditional::OPERATOR_GREATERTHANOREQUAL, 'value' => 20, 'color' => 'FF92D050'],
                    ['operator' => Conditional::OPERATOR_GREATERTHANOREQUAL, 'value' => 15, 'color' => 'FFFF7C80'],
                    ['operator' => Conditional::OPERATOR_GREATERTHANOREQUAL, 'value' => 10, 'color' => 'FFFFC000'],
                    ['operator' => Conditional::OPERATOR_GREATERTHANOREQUAL, 'value' => 5,  'color' => 'FFEBF1DE'],
                    ['operator' => Conditional::OPERATOR_GREATERTHANOREQUAL, 'value' => 1,  'color' => 'FFC5D9F1'],
                ];

                $conditionalStyles = [];
                foreach ($conditionalRules as $rule) {
                    $condition = new Conditional();
                    $condition->setConditionType(Conditional::CONDITION_EXPRESSION);
                    $condition->addCondition('=$O' . $firstDataRow . '>=' . $rule['value']);
                    
                    $condition->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)
                              ->getStartColor()->setARGB($rule['color']);
                    
                    $conditionalStyles[] = $condition;
                }

                $sheet->getStyle("P{$firstDataRow}:P{$lastDataRow}")->setConditionalStyles($conditionalStyles);
                
                $footerRow = $lastDataRow + 1;
                $sheet->getStyle("K{$firstDataRow}:K{$footerRow}")->getNumberFormat()->setFormatCode('0%');
                $sheet->getRowDimension($headerEndRow)->setRowHeight(75);

                $legendStartCol = 'S';
                $legendDataCol = 'T';
                $legendStartRow = 6;

                $legendData = [
                    ['header' => ['KATEGORI DESKRIPSI', null], 'color' => 'FFC4D79B'],
                    ['data' => ['>=50', 'Ananda diberikan Surat Peringatan 3'], 'color' => 'FFFF0000'],
                    ['data' => ['>=43', 'Ananda diberikan Surat Peringatan 2'], 'color' => 'FF538DD5'],
                    ['data' => ['>=35', 'Ananda diberikan Surat Peringatan 2'], 'color' => 'FFFFFF00'],
                    ['data' => ['>=28', 'Ananda diberikan Surat Peringatan 1'], 'color' => 'FF7030A0'],
                    ['data' => ['>=20', 'Ananda diberikan Surat Peringatan 1'], 'color' => 'FF92D050'],
                    ['data' => ['>=15', 'Ananda diberikan Surat Komitmen Ke-3'], 'color' => 'FFFF7C80'],
                    ['data' => ['>=10', 'Ananda diberikan Surat Komitmen Ke-2'], 'color' => 'FFFFC000'],
                    ['data' => ['>=5', 'Ananda diberikan Surat Komitmen Ke-1'], 'color' => 'FFEBF1DE'],
                    ['data' => ['>=1', 'Motivasi dan Teguran dari Wali Kelas'], 'color' => 'FFC5D9F1'],
                    ['data' => ['0', 'Aman'], 'color' => null],
                ];
                
                $currentLegendRow = $legendStartRow;

                foreach ($legendData as $index => $item) {
                    if (isset($item['header'])) {
                        $sheet->mergeCells("{$legendStartCol}{$currentLegendRow}:{$legendDataCol}".($currentLegendRow + 1));
                        $sheet->setCellValue("{$legendStartCol}{$currentLegendRow}", $item['header'][0]);
                        $sheet->getStyle("{$legendStartCol}{$currentLegendRow}")->getFont()->setBold(true);
                        
                        if ($item['color']) {
                            $sheet->getStyle("{$legendStartCol}{$currentLegendRow}:{$legendDataCol}".($currentLegendRow + 1))
                                  ->getFill()->setFillType(Fill::FILL_SOLID)
                                  ->getStartColor()->setARGB($item['color']);
                        }
                        $currentLegendRow++;
                    } else {
                        $sheet->setCellValue("{$legendStartCol}{$currentLegendRow}", $item['data'][0]);
                        $sheet->setCellValue("{$legendDataCol}{$currentLegendRow}", $item['data'][1]);
                        
                        if ($item['color']) {
                            $sheet->getStyle("{$legendStartCol}{$currentLegendRow}:{$legendDataCol}{$currentLegendRow}")
                                  ->getFill()->setFillType(Fill::FILL_SOLID)
                                  ->getStartColor()->setARGB($item['color']);
                        }
                    }
                    $currentLegendRow++;
                }

                $legendEndRow = $currentLegendRow - 1;
                $sheet->getColumnDimension($legendStartCol)->setWidth(18);
                $sheet->getColumnDimension($legendDataCol)->setWidth(60);
                $sheet->getStyle("S{$legendStartRow}:T{$legendEndRow}")
                      ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("S{$legendStartRow}:T{$legendEndRow}")
                      ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("S{$legendStartRow}:T{$legendStartRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("S" . ($legendStartRow + 2) . ":S{$legendEndRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("T" . ($legendStartRow + 2) . ":T{$legendEndRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);

                
                $efektifStartRow = $legendEndRow + 2;
                $efektifEndRow = $efektifStartRow + 1;

                $sheet->mergeCells("S{$efektifStartRow}:T{$efektifStartRow}");
                $sheet->setCellValue("S{$efektifStartRow}", "HARI EFEKTIF");
                $sheet->getStyle("S{$efektifStartRow}:T{$efektifStartRow}")->getFont()->setBold(true);
                $sheet->getStyle("S{$efektifStartRow}:T{$efektifStartRow}")
                      ->getFill()->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFC4D79B');

                $sheet->setCellValue("S{$efektifEndRow}", "Jumlah");
                $sheet->setCellValue("T{$efektifEndRow}", $this->activeDaysInMonth);
                $sheet->getStyle("T{$efektifEndRow}")->getFont()->setBold(bold: true)->setSize(16);

                $sheet->getStyle("S{$efektifStartRow}:T{$efektifEndRow}")
                      ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("S{$efektifStartRow}:T{$efektifEndRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                      ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("S{$efektifEndRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}