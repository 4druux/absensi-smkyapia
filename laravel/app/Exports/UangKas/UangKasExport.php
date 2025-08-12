<?php

namespace App\Exports\UangKas;

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
    protected $kelompok;
    protected $jurusan;
    protected $academicYear;
    protected $year;
    protected $bulanSlug;
    protected $weeksInMonth;
    protected $dataRowsCount = 0;

    public function __construct($kelas, $kelompok, $jurusan, $academicYear, $year, $bulanSlug, $weeksInMonth)
    {
        $this->kelas = $kelas;
        $this->kelompok = $kelompok;
        $this->jurusan = $jurusan;
        $this->academicYear = $academicYear;
        $this->year = $year;
        $this->bulanSlug = $bulanSlug;
        $this->weeksInMonth = $weeksInMonth;
    }

    public function collection()
    {
        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];
        $monthNumber = $monthMap[strtolower($this->bulanSlug)] ?? null;

        $selectedKelas = \App\Models\Kelas::whereHas('jurusan', function ($query) {
            $query->where('nama_jurusan', $this->jurusan);
        })->where('nama_kelas', $this->kelas)->firstOrFail();

        $students = $selectedKelas->siswas()->orderBy('nama')->get();

        $uangKasData = UangKasPayment::whereIn('siswa_id', $students->pluck('id'))
            ->where('tahun', $this->academicYear)
            ->where('bulan_slug', $this->bulanSlug)
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->siswa_id . '_' . $item->minggu => $item,
                ];
            });

        $exportData = new Collection();
        $namaBulan = Carbon::createFromDate($this->year, $monthNumber, 1)->translatedFormat('F');

        $exportData->push(['DATA UANG KAS SISWA BULANAN']);
        $exportData->push(['SMK YAPIA PARUNG']);
        $exportData->push(["Kelas {$this->kelas} {$this->kelompok} - {$this->jurusan}"]);
        $exportData->push(["Periode {$namaBulan} {$this->year}"]);
        $exportData->push(['']);

        $headerRow1 = ['No', 'Nama Siswa'];
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
            if (isset($this->weeksInMonth[$week]) && $this->weeksInMonth[$week]) {
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
                if (isset($this->weeksInMonth[$week]) && $this->weeksInMonth[$week]) {
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

            $nonHolidayWeeks = count(array_filter($this->weeksInMonth, fn ($isHoliday) => !$isHoliday));
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
            if (isset($this->weeksInMonth[$week]) && $this->weeksInMonth[$week]) {
                $jumlahRow->push('');
            } else {
                $totalMingguan = $mingguanTotalPaid[$week];
                $jumlahRow->push($totalMingguan == 0 ? '0' : $totalMingguan);
            }
        }

        $jumlahRow->push($grandTotalNominal == 0 ? '0' : $grandTotalNominal);
        $jumlahRow->push('');
        $exportData->push($jumlahRow);

        return $exportData;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Cambria');

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
            ->getStartColor()->setARGB('FFC4D79B');

        $sheet->mergeCells('A' . $headerStartRow . ':A' . $headerEndRow);
        $sheet->setCellValue('A' . $headerStartRow, 'No');
        $sheet->mergeCells('B' . $headerStartRow . ':B' . $headerEndRow);
        $sheet->setCellValue('B' . $headerStartRow, 'Nama Siswa');

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
            if (isset($this->weeksInMonth[$week]) && $this->weeksInMonth[$week]) {
                $sheet->getStyle($col . ($headerStartRow + 1) . ':' . $col . $jumlahRow)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFD21A1A');
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

                $sheet->getStyle($firstMingguCol . ($headerEndRow + 1) . ':' . $lastMingguCol . $jumlahRow)->getNumberFormat()->setFormatCode('#,##0;-#,##0;0');
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
                    ['Simbol', 'Keterangan'],
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

                $sheet->getStyle('A' . ($legendStartRow + 3))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD21A1A');
            },
        ];
    }
}