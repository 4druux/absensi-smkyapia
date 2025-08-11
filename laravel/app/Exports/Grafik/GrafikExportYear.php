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
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class GrafikExportYear implements FromArray, WithTitle, WithCharts, WithStyles
{
    use Exportable;

    protected $data;
    protected $kelas;
    protected $jurusan;
    protected $tahun;

    public function __construct(array $data, $kelas, $jurusan, $tahun)
    {
        $this->data = $data;
        $this->kelas = $kelas;
        $this->jurusan = $jurusan;
        $this->tahun = $tahun;
    }

    public function title(): string
    {
        return 'Data';
    }

    public function array(): array
    {
        $rows = [['No', 'Bulan', 'Telat', 'Alfa', 'Sakit', 'Izin', 'Bolos']];
        foreach ($this->data['labels'] as $index => $label) {
            $rows[] = [
                $index + 1,
                $label,
                $this->data['telat'][$index],
                $this->data['alfa'][$index],
                $this->data['sakit'][$index],
                $this->data['izin'][$index],
                $this->data['bolos'][$index],
            ];
        }

        $rows[] = [
            null,
            'Jumlah',
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
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        foreach (range('C', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setWidth(8);
        }

        $lastRow = count($this->data['labels']) + 2; 
        $sheet->getStyle("A1:G{$lastRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C2:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A{$lastRow}:G{$lastRow}")->getFont()->setBold(true);
    }

    public function charts()
    {
        $dataPointCount = count($this->data['labels']);
        $sheetName = $this->title();
        $dataStartRow = 2;
        $dataEndRow = $dataStartRow + $dataPointCount - 1;

        $chartGenerator = function($title, $colLetter) use ($dataPointCount, $sheetName, $dataStartRow, $dataEndRow) {
            $categories = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'{$sheetName}'!\$B\${$dataStartRow}:\$B\${$dataEndRow}", null, $dataPointCount);
            $values = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'{$sheetName}'!\${$colLetter}\${$dataStartRow}:\${$colLetter}\${$dataEndRow}", null, $dataPointCount);

            $series = new DataSeries(
                DataSeries::TYPE_BARCHART,
                DataSeries::GROUPING_CLUSTERED,
                range(0, $dataPointCount - 1),
                [], 
                [$categories],
                [$values]
            );
            $series->setPlotDirection(DataSeries::DIRECTION_COL);

            $layout = new Layout(['gapWidth' => 70]);
            
            $plotArea = new PlotArea($layout, [$series]);
            
            $chart = new Chart(
                'chart_'.uniqid(), new Title($title), null, $plotArea
            );
            return $chart;
        };

        $chartTelat = $chartGenerator('DATA TELAT', 'C');
        $chartTelat->setTopLeftPosition('I2'); $chartTelat->setBottomRightPosition('P17');

        $chartAlfa = $chartGenerator('DATA ALFA', 'D');
        $chartAlfa->setTopLeftPosition('Q2'); $chartAlfa->setBottomRightPosition('X17');
        
        $chartSakit = $chartGenerator('DATA SAKIT', 'E');
        $chartSakit->setTopLeftPosition('I19'); $chartSakit->setBottomRightPosition('P34');
        
        $chartIzin = $chartGenerator('DATA IZIN', 'F');
        $chartIzin->setTopLeftPosition('Q19'); $chartIzin->setBottomRightPosition('X34');

        $chartBolos = $chartGenerator('DATA BOLOS', 'G');
        $chartBolos->setTopLeftPosition('I36'); $chartBolos->setBottomRightPosition('P51');

        return [$chartTelat, $chartAlfa, $chartSakit, $chartIzin, $chartBolos];
    }
}