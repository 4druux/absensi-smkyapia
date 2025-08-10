<?php

namespace App\Exports\Absensi;

use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\Holiday;
use App\Models\Kelas;
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

class AbsensiExportMonth implements FromCollection, WithStyles, WithEvents
{
    protected $kelasNama;
    protected $jurusanNama;
    protected $tahun;
    protected $bulanSlug;
    protected $bulanNumber;
    protected $daysInMonth;
    protected $allHolidays;
    protected $dataRowsCount = 0;
    protected $headingRowsCount = 6;

    public function __construct($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $this->kelasNama = $kelas;
        $this->jurusanNama = $jurusan;
        $this->tahun = $tahun;
        $this->bulanSlug = $bulanSlug;
        $this->bulanNumber = $this->getMonthNumberFromSlug($bulanSlug);
        
        $year = intval(explode('-', $this->tahun)[0]);
        $this->daysInMonth = Carbon::createFromDate($year, $this->bulanNumber, 1)->daysInMonth;
        
        $dbHolidays = Holiday::whereYear('date', $year)
            ->whereMonth('date', $this->bulanNumber)
            ->pluck('date');

        $weekends = collect();
        $date = Carbon::create($year, $this->bulanNumber, 1);
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
        $selectedKelas = Kelas::whereHas('jurusan', function($query) {
            $query->where('nama_jurusan', $this->jurusanNama);
        })->where('nama_kelas', $this->kelasNama)->firstOrFail();
        
        $students = $selectedKelas->siswas()->orderBy('nama')->get();
        
        $yearForQuery = intval(explode('-', $this->tahun)[0]);
        $absensiData = Absensi::whereIn('siswa_id', $students->pluck('id'))
            ->whereYear('tanggal', $yearForQuery)
            ->whereMonth('tanggal', $this->bulanNumber)
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->siswa_id . '_' . Carbon::parse($item->tanggal)->day => $item->status,
                ];
            });

        $exportData = new Collection();
        
        $namaBulan = Carbon::createFromDate($yearForQuery, $this->bulanNumber, 1)->translatedFormat('F');
        $exportData->push(['DATA KEHADIRAN SISWA']);
        $exportData->push(['SMK YAPIA PARUNG']);
        $exportData->push(["Kelas {$this->kelasNama} {$this->jurusanNama}"]);
        $exportData->push(["Periode {$namaBulan} {$this->tahun}"]);
        $exportData->push(['']);

        $headerRow1 = ['NO', 'NAMA SISWA'];
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $headerRow1[] = '';
        }
        $headerRow1[] = 'TOTAL';
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

            $counts = ['H' => 0, 'T' => 0, 'S' => 0, 'I' => 0, 'A' => 0, 'B' => 0];
            
            for ($day = 1; $day <= $this->daysInMonth; $day++) {
                $date = Carbon::create($yearForQuery, $this->bulanNumber, $day);
                $dateString = $date->format('Y-m-d');
                $status = $absensiData->get($student->id . '_' . $day, '');
                
                if ($this->allHolidays->contains($dateString)) {
                    $statusAbbr = '';
                } else {
                    if ($status === '') {
                        $statusAbbr = '-';
                    } else {
                        $statusUpper = strtoupper($status);
                        switch ($statusUpper) {
                            case 'HADIR':
                                $statusAbbr = '✓';
                                $counts['H']++;
                                break;
                            case 'TELAT':
                                $statusAbbr = 'T';
                                $counts['T']++;
                                break;
                            case 'SAKIT':
                                $statusAbbr = 'S';
                                $counts['S']++;
                                break;
                            case 'IZIN':
                                $statusAbbr = 'I';
                                $counts['I']++;
                                break;
                            case 'ALFA':
                                $statusAbbr = 'A';
                                $counts['A']++;
                                break;
                            case 'BOLOS':
                                $statusAbbr = 'B';
                                $counts['B']++;
                                break;
                            default:
                                $statusAbbr = '-';
                        }
                    }
                }
                $rowData->push($statusAbbr);
            }
            
            $rowData->push($counts['T'] > 0 ? $counts['T'] : '');
            $rowData->push($counts['S'] > 0 ? $counts['S'] : '');
            $rowData->push($counts['I'] > 0 ? $counts['I'] : '');
            $rowData->push($counts['A'] > 0 ? $counts['A'] : '');
            $rowData->push($counts['B'] > 0 ? $counts['B'] : '');
            
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
        
        $jumlahRow->push($totalT == 0 ? '0' : $totalT);
        $jumlahRow->push($totalS == 0 ? '0' : $totalS);
        $jumlahRow->push($totalI == 0 ? '0' : $totalI);
        $jumlahRow->push($totalA == 0 ? '0' : $totalA);
        $jumlahRow->push($totalB == 0 ? '0' : $totalB);
        $exportData->push($jumlahRow);
        
        return $exportData;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Cambria');

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
            ->getStartColor()->setARGB('FFC4D79B');

        $sheet->mergeCells('A' . $headerStartRow . ':A' . $headerEndRow);
        $sheet->setCellValue('A' . $headerStartRow, 'NO');
        $sheet->mergeCells('B' . $headerStartRow . ':B' . $headerEndRow);
        $sheet->setCellValue('B' . $headerStartRow, 'NAMA SISWA');

        $firstTglCol = Coordinate::stringFromColumnIndex(3);
        $lastTglCol = Coordinate::stringFromColumnIndex(2 + $this->daysInMonth);
        $sheet->mergeCells($firstTglCol . $headerStartRow . ':' . $lastTglCol . $headerStartRow);
        $sheet->setCellValue($firstTglCol . $headerStartRow, 'TANGGAL');

        $firstStatusColIndex = 3 + $this->daysInMonth;
        $firstStatusCol = Coordinate::stringFromColumnIndex($firstStatusColIndex);
        $sheet->mergeCells($firstStatusCol . $headerStartRow . ':' . $lastCol . $headerStartRow);
        $sheet->setCellValue($firstStatusCol . $headerStartRow, 'TOTAL');

        $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $jumlahRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $lastDataColIndex = 2 + $this->daysInMonth;
        $lastDataCol = Coordinate::stringFromColumnIndex($lastDataColIndex);
        $sheet->mergeCells('A' . $jumlahRow . ':' . $lastDataCol . $jumlahRow);
        $sheet->getStyle('A' . $jumlahRow)->getFont()->setBold(false);
        $sheet->getStyle('A' . $jumlahRow . ':' . $lastCol . $jumlahRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $yearForHoliday = intval(explode('-', $this->tahun)[0]);
        $dateOffset = 2;
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $date = Carbon::create($yearForHoliday, $this->bulanNumber, $day);
            if ($this->allHolidays->contains($date->format('Y-m-d'))) {
                $col = Coordinate::stringFromColumnIndex($day + $dateOffset);
                $sheet->getStyle($col . $headerEndRow . ':' . $col . $jumlahRow)
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

                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                
                $dateStartColIndex = 3;
                $dateEndColIndex = 2 + $this->daysInMonth;
                for ($colIndex = $dateStartColIndex; $colIndex <= $dateEndColIndex; $colIndex++) {
                    $col = Coordinate::stringFromColumnIndex($colIndex);
                    $sheet->getColumnDimension($col)->setWidth(4);
                }
                
                $firstTotalColIndex = $dateEndColIndex + 1;
                $lastTotalColIndex = $dateEndColIndex + 5;
                for ($colIndex = $firstTotalColIndex; $colIndex <= $lastTotalColIndex; $colIndex++) {
                     $col = Coordinate::stringFromColumnIndex($colIndex);
                    $sheet->getColumnDimension($col)->setWidth(4);
                }

                $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $jumlahRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('B' . $headerStartRow . ':B' . $headerEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $firstDataRow . ':B' . $jumlahRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                $legendData = [
                    ['STATUS', 'KETERANGAN'],
                    ['✓', 'Hadir'],
                    ['T', 'Telat'],
                    ['S', 'Sakit'],
                    ['I', 'Izin'],
                    ['A', 'Alfa'],
                    ['B', 'Bolos'],
                    ['', 'Hari Libur / Akhir Pekan'],
                ];

                $sheet->getColumnDimension('B')->setWidth(25);

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
                $sheet->getStyle('A' . $legendStartRow . ':B' . $legendStartRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFC4D79B');
                
                $sheet->getStyle('A' . ($legendStartRow + 7))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD21A1A');
            },
        ];
    }
}
