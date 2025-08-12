<?php

namespace App\Exports\Grafik;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GrafikExportYear implements FromArray, WithTitle, WithCharts, WithStyles
{
    use Exportable;

    protected $data;
    protected $kelas;
    protected $kelompok;
    protected $jurusan;
    protected $tahun;

    public function __construct(array $data, $kelas, $kelompok, $jurusan, $tahun)
    {
        $this->data = $data;
        $this->kelas = $kelas;
        $this->kelompok = $kelompok;
        $this->jurusan = $jurusan;
        $this->tahun = $tahun;
    }

    public function title(): string
    {
        return 'Data';
    }

    public function array(): array
    {
        $months = [
            'JAN' => 'JANUARI', 'FEB' => 'FEBRUARI', 'MAR' => 'MARET', 'APR' => 'APRIL',
            'MEI' => 'MEI', 'JUN' => 'JUNI', 'JUL' => 'JULI', 'AGT' => 'AGUSTUS',
            'SEP' => 'SEPTEMBER', 'OKT' => 'OKTOBER', 'NOV' => 'NOVEMBER', 'DES' => 'DESEMBER'
        ];

        $rows = [
            ['GRAFIK ABSENSI TAHUNAN'],
            ['SMK YAPIA PARUNG'],
            ["Kelas {$this->kelas} {$this->kelompok} - {$this->jurusan}"],
            ["Tahun Ajaran {$this->tahun}"],
            [''],
        ];

        $rows[] = ['NO', 'BULAN', 'TELAT', 'ALFA', 'SAKIT', 'IZIN', 'BOLOS'];
        foreach ($this->data['labels'] as $index => $label) {
            $rows[] = [
                $index + 1,
                $months[strtoupper($label)] ?? $label,
                $this->data['telat'][$index],
                $this->data['alfa'][$index],
                $this->data['sakit'][$index],
                $this->data['izin'][$index],
                $this->data['bolos'][$index],
            ];
        }

        $rows[] = [
            'JUMLAH',
            null,
            $this->data['telat']->sum(),
            $this->data['alfa']->sum(),
            $this->data['sakit']->sum(),
            $this->data['izin']->sum(),
            $this->data['bolos']->sum(),
        ];
        
        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Cambria')->setSize(10);

        $sheet->getStyle('A1:T4')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:T4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A1:T1');
        $sheet->mergeCells('A2:T2');
        $sheet->mergeCells('A3:T3');
        $sheet->mergeCells('A4:T4');

        $headerRow = 6;
        $dataStartRow = 7;
        $dataPointCount = count($this->data['labels']);
        $lastDataRow = $dataStartRow + $dataPointCount - 1;
        $lastTotalRow = $lastDataRow + 1;

        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFC4D79B');

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(15);
        foreach (range('C', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setWidth(8);
        }

        $sheet->mergeCells("A{$lastTotalRow}:B{$lastTotalRow}");
        $sheet->getStyle("A{$headerRow}:G{$lastTotalRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle("A{$headerRow}:A{$lastTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B{$headerRow}:B" . ($lastTotalRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("C{$headerRow}:G{$lastTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A{$lastTotalRow}:G{$lastTotalRow}")->getFont()->setBold(true);

        for ($r = 1; $r <= 4; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(18);
        }

        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension($headerRow)->setRowHeight(22);

        for ($r = $dataStartRow; $r <= $lastDataRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(18);
        }

        $sheet->getRowDimension($lastTotalRow)->setRowHeight(22);
        $sheet->getStyle("A{$lastTotalRow}:G{$lastTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$lastTotalRow}:G{$lastTotalRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        for ($r = $lastDataRow + 2; $r <= $lastDataRow + 18; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(18);
        }

        $sheet->getStyle('H6:S20')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A22:R36')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    public function charts()
    {
        $dataPointCount = count($this->data['labels']);
        $sheetName = $this->title();
        $dataStartRow = 7;
        $dataEndRow = $dataStartRow + $dataPointCount - 1;

        $chartGenerator = function($title, $colLetter) use ($dataPointCount, $sheetName, $dataStartRow, $dataEndRow) {
            $categories = new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_STRING,
                "'{$sheetName}'!\$B\${$dataStartRow}:\$B\${$dataEndRow}",
                null, $dataPointCount
            );
            $values = new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_NUMBER,
                "'{$sheetName}'!\${$colLetter}\${$dataStartRow}:\${$colLetter}\${$dataEndRow}",
                null, $dataPointCount
            );

            $series = new DataSeries(
                DataSeries::TYPE_BARCHART,
                DataSeries::GROUPING_CLUSTERED,
                range(0, $dataPointCount - 1),
                [],
                [$categories],
                [$values]
            );
            $series->setPlotDirection(DataSeries::DIRECTION_COL);

            $layout = new Layout(['gapWidth' => 5]);
            $plotArea = new PlotArea($layout, [$series]);
            $chartTitle = new Title($title);

            return new Chart('chart_' . uniqid(), $chartTitle, null, $plotArea, false, 0);
        };

        $chartTelat = $chartGenerator('DATA TELAT', 'C');
        $chartTelat->setTopLeftPosition('I6');
        $chartTelat->setBottomRightPosition('O20');

        $chartAlfa = $chartGenerator('DATA ALFA', 'D');
        $chartAlfa->setTopLeftPosition('P6');
        $chartAlfa->setBottomRightPosition('V20');

        $chartSakit = $chartGenerator('DATA SAKIT', 'E');
        $chartSakit->setTopLeftPosition('A22');
        $chartSakit->setBottomRightPosition('G36');

        $chartIzin = $chartGenerator('DATA IZIN', 'F');
        $chartIzin->setTopLeftPosition('I22');
        $chartIzin->setBottomRightPosition('O36');

        $chartBolos = $chartGenerator('DATA BOLOS', 'G');
        $chartBolos->setTopLeftPosition('P22');
        $chartBolos->setBottomRightPosition('V36');

        return [$chartTelat, $chartAlfa, $chartSakit, $chartIzin, $chartBolos];
    }
}
