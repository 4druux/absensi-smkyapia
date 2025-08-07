<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\UangKasPayment;
use Carbon\Carbon;
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

class UangKasExport implements FromCollection, WithStyles, WithEvents
{
    protected $kelas;
    protected $jurusan;
    protected $tahun;
    protected $bulanSlug;
    protected $weeksInMonth;
    protected $dataRowsCount = 0;

    public function __construct($kelas, $jurusan, $tahun, $bulanSlug, $weeksInMonth)
    {
        $this->kelas = $kelas;
        $this->jurusan = $jurusan;
        $this->tahun = $tahun;
        $this->bulanSlug = $bulanSlug;
        $this->weeksInMonth = $weeksInMonth;
    }

    private function getMonthNumberFromSlug($slug)
    {
        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];
        return $monthMap[strtolower($slug)] ?? null;
    }

    public function collection()
    {
        $students = Siswa::where('kelas', $this->kelas)
            ->where('jurusan', $this->jurusan)
            ->orderBy('nama')
            ->get();

        $uangKasData = UangKasPayment::whereIn('siswa_id', $students->pluck('id'))
            ->where('tahun', $this->tahun)
            ->where('bulan_slug', $this->bulanSlug)
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->siswa_id . '_' . $item->minggu => $item,
                ];
            });

        $exportData = new Collection();

        $namaBulan = Carbon::createFromDate($this->tahun, $this->getMonthNumberFromSlug($this->bulanSlug), 1)->translatedFormat('F');
        $exportData->push(['SMK YAPIA PARUNG']);
        $exportData->push(['LAPORAN UANG KAS SISWA/I']);
        $exportData->push(["Kelas {$this->kelas} {$this->jurusan}"]);
        $exportData->push(["Periode {$namaBulan} {$this->tahun}"]);
        $exportData->push(['']);

        $headerRow1 = ['No', 'Nama Siswa/i'];
        for ($week = 1; $week <= 5; $week++) {
            $headerRow1[] = '';
        }
        $headerRow1[] = 'Total';
        $headerRow1[] = 'Status';
        $exportData->push($headerRow1);

        $headerRow2 = ['', ''];
        for ($week = 1; $week <= 5; $week++) {
            $headerRow2[] = "Minggu-{$week}";
        }
        $headerRow2[] = '';
        $headerRow2[] = '';
        $exportData->push($headerRow2);
        
        $headerRow3 = ['', ''];
        for ($week = 1; $week <= 5; $week++) {
            if ($this->weeksInMonth[$week]) {
                $headerRow3[] = '-';
            } else {
                $nominal = 0;
                foreach ($uangKasData as $key => $data) {
                    if (str_ends_with($key, '_' . $week)) {
                        $nominal = $data->nominal;
                        break;
                    }
                }
                $headerRow3[] = $nominal;
            }
        }
        $headerRow3[] = '';
        $headerRow3[] = '';
        $exportData->push($headerRow3);

        $grandTotalNominal = 0;
        $mingguanTotalPaid = array_fill(1, 5, 0);
        $this->dataRowsCount = $students->count();

        foreach ($students as $index => $student) {
            $rowData = new Collection();
            $rowData->push($index + 1);
            $rowData->push($student->nama);

            $studentTotal = 0;
            $paidCount = 0;
            for ($week = 1; $week <= 5; $week++) {
                if ($this->weeksInMonth[$week]) {
                    $rowData->push('');
                } else {
                    $status = isset($uangKasData[$student->id . '_' . $week]) ? '✓' : 'X';
                    $rowData->push($status);
                    if (isset($uangKasData[$student->id . '_' . $week])) {
                        $payment = $uangKasData[$student->id . '_' . $week];
                        $studentTotal += $payment->nominal;
                        $paidCount++;
                        $mingguanTotalPaid[$week] += $payment->nominal;
                    }
                }
            }

            $rowData->push($studentTotal);

            $nonHolidayWeeks = count(array_filter($this->weeksInMonth, fn($isHoliday) => !$isHoliday));
            if ($nonHolidayWeeks > 0 && $paidCount === $nonHolidayWeeks) {
                $status = 'Lunas';
            } elseif ($paidCount > 0) {
                $status = 'Belum Lunas';
            } else {
                $status = 'Belum Bayar';
            }
            $rowData->push($status);

            $exportData->push($rowData);
            $grandTotalNominal += $studentTotal;
        }

        $jumlahRow = new Collection();
        $jumlahRow->push('Jumlah');
        $jumlahRow->push('');
        for ($week = 1; $week <= 5; $week++) {
            $jumlahRow->push($this->weeksInMonth[$week] ? '' : $mingguanTotalPaid[$week]);
        }

        $jumlahRow->push($grandTotalNominal);
        $jumlahRow->push('');
        $exportData->push($jumlahRow);

        return $exportData;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColIndex = 2 + 5 + 2;
        $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);

        $headerStartRow = 6;
        $headerEndRow = $headerStartRow + 2;
        $firstDataRow = $headerEndRow + 1;
        $lastDataRow = $firstDataRow + $this->dataRowsCount - 1;
        $jumlahRow = $lastDataRow + 1;
        $sheet->getRowDimension($jumlahRow)->setRowHeight(30);

        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->mergeCells('A3:' . $lastCol . '3');
        $sheet->mergeCells('A4:' . $lastCol . '4');
        $sheet->getStyle('A1:A4')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        

        $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $headerEndRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $headerEndRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');

        $sheet->mergeCells('A' . $headerStartRow . ':A' . $headerEndRow);
        $sheet->setCellValue('A' . $headerStartRow, 'No');
        $sheet->mergeCells('B' . $headerStartRow . ':B' . $headerEndRow);
        $sheet->setCellValue('B' . $headerStartRow, 'Nama Siswa/i');

        $firstMingguCol = Coordinate::stringFromColumnIndex(3);
        $lastMingguCol = Coordinate::stringFromColumnIndex(7);
        $sheet->mergeCells($firstMingguCol . $headerStartRow . ':' . $lastMingguCol . $headerStartRow);
        $sheet->setCellValue($firstMingguCol . $headerStartRow, 'Mingguan');

        $totalCol = Coordinate::stringFromColumnIndex(8);
        $sheet->mergeCells($totalCol . $headerStartRow . ':' . $totalCol . $headerEndRow);
        $sheet->setCellValue($totalCol . $headerStartRow, 'Total');

        $statusCol = Coordinate::stringFromColumnIndex(9);
        $sheet->mergeCells($statusCol . $headerStartRow . ':' . $statusCol . $headerEndRow);
        $sheet->setCellValue($statusCol . $headerStartRow, 'Status');

        $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $jumlahRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->mergeCells('A' . $jumlahRow . ':B' . $jumlahRow);
        $sheet->getStyle('A' . $jumlahRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);        
        for ($week = 1; $week <= 5; $week++) {
            $col = Coordinate::stringFromColumnIndex($week + 2);
            if ($this->weeksInMonth[$week]) {
                $sheet->getStyle($col . ($headerStartRow + 1) . ':' . $col . $jumlahRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFD21A1A');
            }
        }

        for ($row = $firstDataRow; $row <= $lastDataRow; $row++) {
            for ($week = 1; $week <= 5; $week++) {
                $col = Coordinate::stringFromColumnIndex($week + 2);
                $cellValue = $sheet->getCell($col . $row)->getValue();
                $style = $sheet->getStyle($col . $row);

                if ($cellValue === '✓') {
                    $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC6EFCE');
                } elseif ($cellValue === 'X') {
                    $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC7CE');
                }
            }
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColIndex = 2 + 5 + 2;
                $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);

                $headerStartRow = 6;
                $headerEndRow = $headerStartRow + 2;
                $firstDataRow = $headerEndRow + 1;
                $lastDataRow = $firstDataRow + $this->dataRowsCount - 1;
                $jumlahRow = $lastDataRow + 1;
                $totalCol = Coordinate::stringFromColumnIndex(8);
                $statusCol = Coordinate::stringFromColumnIndex(9);
                $firstMingguCol = Coordinate::stringFromColumnIndex(3);
                $lastMingguCol = Coordinate::stringFromColumnIndex(7);

                $sheet->getStyle($firstMingguCol . ($headerEndRow + 1) . ':' . $lastMingguCol . $jumlahRow)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle($totalCol . ($headerEndRow + 1) . ':' . $totalCol . $jumlahRow)->getNumberFormat()->setFormatCode('#,##0');

                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $jumlahRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle('B' . $firstDataRow . ':B' . $lastDataRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                $sheet->getStyle('A' . $jumlahRow . ':B' . $jumlahRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                for ($week = 1; $week <= 5; $week++) {
                    $col = Coordinate::stringFromColumnIndex($week + 2);
                    $sheet->getColumnDimension($col)->setWidth(12);
                }

                $sheet->getColumnDimension($totalCol)->setAutoSize(true);
                $sheet->getColumnDimension($statusCol)->setAutoSize(true);

                $legendStartRow = $jumlahRow + 3;
                $legendData = [
                    ['Status', 'Keterangan'],
                    ['✓', 'Sudah Bayar'],
                    ['X', 'Belum Bayar'],
                    ['', 'Hari Libur / Akhir Pekan'],
                ];

                $statusColWidth = 10;
                $legendColWidth = 25;
                $sheet->getColumnDimension('A')->setWidth($statusColWidth);
                $sheet->getColumnDimension('B')->setWidth($legendColWidth);

                $currentLegendRow = $legendStartRow;
                foreach ($legendData as $row) {
                    $sheet->setCellValue('A' . $currentLegendRow, $row[0]);
                    $sheet->setCellValue('B' . $currentLegendRow, $row[1]);
                    $currentLegendRow++;
            }


                $legendEndRow = $legendStartRow + count($legendData) - 1;
                $sheet->getStyle('A' . $legendStartRow . ':B' . $legendEndRow)->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle('A' . $legendStartRow . ':B' . $legendStartRow)->getFont()->setBold(true);

                $sheet->getStyle('A' . ($legendStartRow + 1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC6EFCE');
                $sheet->getStyle('A' . ($legendStartRow + 2))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC7CE');
                $sheet->getStyle('A' . ($legendStartRow + 3))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD21A1A');
            },
        ];
    }
}