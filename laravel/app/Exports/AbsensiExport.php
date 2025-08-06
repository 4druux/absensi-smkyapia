<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AbsensiExport implements FromCollection, WithStyles, WithEvents
{
    protected $kelas;
    protected $jurusan;
    protected $tahun;
    protected $bulanSlug;
    protected $bulanNumber;
    protected $daysInMonth;
    protected $allHolidays;
    protected $dataRowsCount = 0;
    protected $headingRowsCount = 6;

    public function __construct($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $this->kelas = $kelas;
        $this->jurusan = $jurusan;
        $this->tahun = $tahun;
        $this->bulanSlug = $bulanSlug;
        $this->bulanNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $this->daysInMonth = Carbon::createFromDate($tahun, $this->bulanNumber, 1)->daysInMonth;
        
        $dbHolidays = Holiday::whereYear('date', $tahun)
            ->whereMonth('date', $this->bulanNumber)
            ->pluck('date');

        $weekends = collect();
        $date = Carbon::create($tahun, $this->bulanNumber, 1);
        for ($i = 0; $i < $this->daysInMonth; $i++) {
            if ($date->isWeekend()) {
                $weekends->push($date->format('Y-m-d'));
            }
            $date->addDay();
        }
        $this->allHolidays = $dbHolidays->merge($weekends)->unique()->map(fn($date) => Carbon::parse($date)->format('Y-m-d'));
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
        
        $absensiData = Absensi::whereIn('siswa_id', $students->pluck('id'))
            ->whereYear('tanggal', $this->tahun)
            ->whereMonth('tanggal', $this->bulanNumber)
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->siswa_id . '_' . Carbon::parse($item->tanggal)->day => $item->status,
                ];
            });

        $exportData = new Collection();
        
        $namaBulan = Carbon::createFromDate($this->tahun, $this->bulanNumber, 1)->translatedFormat('F');
        $exportData->push(['SMK YAPIA PARUNG']);
        $exportData->push(['LAPORAN ABSENSI SISWA/I']);
        $exportData->push(["Kelas {$this->kelas} {$this->jurusan}"]);
        $exportData->push(["Periode {$namaBulan} {$this->tahun}"]);
        $exportData->push(['']);

        $headerRow1 = ['No', 'Nama Siswa/i'];
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $headerRow1[] = '';
        }
        $headerRow1[] = 'Total';
        $exportData->push($headerRow1);

        $headerRow2 = ['', ''];
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $headerRow2[] = $day;
        }
        $headerRow2[] = 'T';
        $headerRow2[] = 'S';
        $headerRow2[] = 'I';
        $headerRow2[] = 'A';
        $headerRow2[] = 'B';
        $exportData->push($headerRow2);

        $totalT = 0;
        $totalS = 0;
        $totalI = 0;
        $totalA = 0;
        $totalB = 0;

        foreach ($students as $index => $student) {
            $rowData = new Collection();
            $rowData->push($index + 1);
            $rowData->push($student->nama);

            $counts = [
                'H' => 0, 'T' => 0, 'S' => 0, 'I' => 0, 'A' => 0, 'B' => 0, 'L' => 0,
            ];
            
            for ($day = 1; $day <= $this->daysInMonth; $day++) {
                $date = Carbon::create($this->tahun, $this->bulanNumber, $day);
                $dateString = $date->format('Y-m-d');
                $status = $absensiData->get($student->id . '_' . $day, '');
                
                if ($this->allHolidays->contains($dateString)) {
                    $statusAbbr = '';
                    $counts['L']++;
                } else {
                    if ($status === '') {
                        $statusAbbr = '-';
                    } else {
                        $statusAbbr = strtoupper(substr($status, 0, 1));
                        if ($statusAbbr === 'H') {
                            $statusAbbr = '✓';
                            $counts['H']++;
                        }
                        
                        if (array_key_exists($statusAbbr, $counts)) {
                             if ($statusAbbr !== 'H') {
                                $counts[$statusAbbr]++;
                            }
                        }
                    }
                }
                $rowData->push($statusAbbr);
            }
            
            $rowData->push($counts['T']);
            $rowData->push($counts['S']);
            $rowData->push($counts['I']);
            $rowData->push($counts['A']);
            $rowData->push($counts['B']);
            
            $exportData->push($rowData);
            $this->dataRowsCount++;

            $totalT += $counts['T'];
            $totalS += $counts['S'];
            $totalI += $counts['I'];
            $totalA += $counts['A'];
            $totalB += $counts['B'];
        }

        $jumlahRow = new Collection();
        $jumlahRow->push('Jumlah');
        $jumlahRow->push('');
        
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $jumlahRow->push('');
        }
        
        $jumlahRow->push($totalT);
        $jumlahRow->push($totalS);
        $jumlahRow->push($totalI);
        $jumlahRow->push($totalA);
        $jumlahRow->push($totalB);
        $exportData->push($jumlahRow);
        
        return $exportData;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColIndex = (2 + $this->daysInMonth + 5);
        $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);
        
        $headerStartRow = 6;
        $headerEndRow = $headerStartRow + 1;
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

        $firstTglCol = Coordinate::stringFromColumnIndex(3);
        $lastTglCol = Coordinate::stringFromColumnIndex(2 + $this->daysInMonth);
        $sheet->mergeCells($firstTglCol . $headerStartRow . ':' . $lastTglCol . $headerStartRow);
        $sheet->setCellValue($firstTglCol . $headerStartRow, 'Tanggal');

        $firstStatusColIndex = 3 + $this->daysInMonth;
        $firstStatusCol = Coordinate::stringFromColumnIndex($firstStatusColIndex);
        $lastStatusCol = Coordinate::stringFromColumnIndex($lastColIndex);
        $sheet->mergeCells($firstStatusCol . $headerStartRow . ':' . $lastStatusCol . $headerStartRow);
        $sheet->setCellValue($firstStatusCol . $headerStartRow, 'Total');

        $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $jumlahRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $lastDataColIndex = 2 + $this->daysInMonth;
        $lastDataCol = Coordinate::stringFromColumnIndex($lastDataColIndex);
        $sheet->mergeCells('A' . $jumlahRow . ':' . $lastDataCol . $jumlahRow);
        $sheet->getStyle('A' . $jumlahRow . ':' . $lastDataCol . $jumlahRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $dateOffset = 2;
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $date = Carbon::create($this->tahun, $this->bulanNumber, $day);
            if ($this->allHolidays->contains($date->format('Y-m-d'))) {
                $col = Coordinate::stringFromColumnIndex($day + $dateOffset);
                $sheet->getStyle($col . $firstDataRow . ':' . $col . $jumlahRow)
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
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColIndex = (2 + $this->daysInMonth + 5);
                $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);
                
                $headerStartRow = 6;
                $headerEndRow = $headerStartRow + 1;
                $firstDataRow = $headerEndRow + 1;
                $lastDataRow = $firstDataRow + $this->dataRowsCount - 1;
                $jumlahRow = $lastDataRow + 1;
                $legendStartRow = $jumlahRow + 3;

                $sheet->getColumnDimension(column: 'A')->setWidth(5);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                
                $dateStartColIndex = 3;
                $dateEndColIndex = 2 + $this->daysInMonth;
                for ($colIndex = $dateStartColIndex; $colIndex <= $dateEndColIndex; $colIndex++) {
                    $col = Coordinate::stringFromColumnIndex($colIndex);
                    $sheet->getColumnDimension($col)->setWidth(3);
                }
                
                $firstTotalColIndex = $dateEndColIndex + 1;
                $lastTotalColIndex = $dateEndColIndex + 5;
                for ($colIndex = $firstTotalColIndex; $colIndex <= $lastTotalColIndex; $colIndex++) {
                     $col = Coordinate::stringFromColumnIndex($colIndex);
                    $sheet->getColumnDimension($col)->setWidth(3);
                }

                $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $jumlahRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('B' . $headerStartRow . ':' . 'B' . $jumlahRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                for ($row = $firstDataRow; $row <= $lastDataRow; $row++) {
                    $dateOffset = 2;
                    for ($colIndex = 1; $colIndex <= $this->daysInMonth; $colIndex++) {
                        $realCol = $colIndex + $dateOffset;
                        $cellValue = $sheet->getCellByColumnAndRow($realCol, $row)->getValue();
                        $style = $sheet->getStyleByColumnAndRow($realCol, $row);

                        switch ($cellValue) {
                            case '✓':
                                $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC6EFCE');
                                break;
                            case 'T':
                                $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFEB9C');
                                break;
                            case 'S':
                            case 'I':
                                $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
                                break;
                            case 'A':
                            case 'B':
                                $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC7CE');
                                break;
                        }
                    }
                }
                
                $legendData = [
                    ['Status', 'Keterangan'],
                    ['✓', 'Hadir'],
                    ['T', 'Telat'],
                    ['S', 'Sakit'],
                    ['I', 'Izin'],
                    ['A', 'Alfa'],
                    ['B', 'Bolos'],
                    ['Libur', 'Hari Libur / Akhir Pekan'],
                ];

                $statusColWidth = 10;
                $legendColWidth = 20;
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
                $sheet->getStyle('A' . ($legendStartRow + 2))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFEB9C');
                $sheet->getStyle('A' . ($legendStartRow + 3))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
                $sheet->getStyle('A' . ($legendStartRow + 4))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
                $sheet->getStyle('A' . ($legendStartRow + 5))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC7CE');
                $sheet->getStyle('A' . ($legendStartRow + 6))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC7CE');
                $sheet->getStyle('A' . ($legendStartRow + 7))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD21A1A');
            },
        ];
    }
}