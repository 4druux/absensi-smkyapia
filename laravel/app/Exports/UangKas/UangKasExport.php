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
    protected $iuranData;
    protected $pengeluaranData;
    protected $dataRowsCount = 0;
    protected $headerRowStart = 6;
    protected $summaryRowStart;
    protected $totalPemasukanMingguan;
    protected $totalPemasukanLainnya;
    protected $totalPengeluaran;

    public function __construct($kelas, $kelompok, $jurusan, $academicYear, $year, $bulanSlug, $weeksInMonth, $iuranData, $pengeluaranData)
    {
        $this->kelas = $kelas;
        $this->kelompok = $kelompok;
        $this->jurusan = $jurusan;
        $this->academicYear = $academicYear;
        $this->year = $year;
        $this->bulanSlug = $bulanSlug;
        $this->weeksInMonth = $weeksInMonth;
        $this->iuranData = $iuranData;
        $this->pengeluaranData = $pengeluaranData;
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

        $totalWeeksInMonth = count($this->weeksInMonth);
        $iuranCount = $this->iuranData->count();
        $totalPemasukanCols = $totalWeeksInMonth + $iuranCount;

        $headerRow1 = ['No', 'Nama Siswa'];
        for ($i = 0; $i < $totalPemasukanCols; $i++) {
            $headerRow1[] = '';
        }
        $headerRow1[] = 'Total';
        $headerRow1[] = 'Status';
        $exportData->push($headerRow1);

        $headerRow2 = ['', ''];
        foreach ($this->weeksInMonth as $week => $isHoliday) {
            $headerRow2[] = "Minggu-{$week}";
        }
        foreach ($this->iuranData as $iuran) {
            $headerRow2[] = $iuran->deskripsi;
        }
        $headerRow2[] = '';
        $headerRow2[] = '';
        $exportData->push($headerRow2);

        $headerRow3 = ['', ''];
        $grandTotalPemasukan = 0;
        $this->totalPemasukanMingguan = 0;
        foreach ($this->weeksInMonth as $week => $isHoliday) {
            $nominal = 0;
            if (!$isHoliday) {
                foreach ($students as $student) {
                    if (isset($uangKasData[$student->id . '_' . $week])) {
                        $nominal = $uangKasData[$student->id . '_' . $week]->nominal;
                        break;
                    }
                }
                $this->totalPemasukanMingguan += $uangKasData->filter(fn($payment) => $payment->minggu == $week && $payment->status == 'paid')->sum('nominal');
            }
            $headerRow3[] = $nominal;
        }
        $this->totalPemasukanLainnya = 0;
        foreach ($this->iuranData as $iuran) {
            $nominalIuran = $iuran->payments->first()->nominal ?? 0;
            $totalIuranPaid = $iuran->payments->where('status', 'paid')->sum('nominal');
            $this->totalPemasukanLainnya += $totalIuranPaid;
            $headerRow3[] = $nominalIuran;
        }
        $grandTotalPemasukan = $this->totalPemasukanMingguan + $this->totalPemasukanLainnya;

        $headerRow3[] = '';
        $headerRow3[] = '';
        $exportData->push($headerRow3);

        $this->dataRowsCount = $students->count();

        foreach ($students as $index => $student) {
            $rowData = new Collection();
            $rowData->push($index + 1);
            $rowData->push($student->nama);

            $studentTotalPemasukan = 0;
            $paidCount = 0;
            $iuranLunas = true;

            foreach ($this->weeksInMonth as $week => $isHoliday) {
                $status = '';
                if (!$isHoliday) {
                    $isPaid = isset($uangKasData[$student->id . '_' . $week]) && $uangKasData[$student->id . '_' . $week]->status == 'paid';
                    $status = $isPaid ? '✓' : '✗';
                    if ($isPaid) {
                        $studentTotalPemasukan += $uangKasData[$student->id . '_' . $week]->nominal;
                        $paidCount++;
                    }
                }
                $rowData->push($status);
            }

            foreach ($this->iuranData as $iuran) {
                $isPaidOther = $iuran->payments->where('siswa_id', $student->id)->where('status', 'paid')->first();
                $statusOther = $isPaidOther ? '✓' : '✗';
                $rowData->push($statusOther);
                if ($isPaidOther) {
                    $studentTotalPemasukan += $isPaidOther->nominal;
                } else {
                    $iuranLunas = false;
                }
            }

            $rowData->push($studentTotalPemasukan);

            $nonHolidayWeeksCount = count(array_filter($this->weeksInMonth, fn($isHoliday) => !$isHoliday));
            if ($paidCount === $nonHolidayWeeksCount && $iuranLunas) {
                $status = 'Lunas';
            } elseif ($studentTotalPemasukan > 0) {
                $status = 'Belum Lunas';
            } else {
                $status = 'Belum Bayar';
            }
            $rowData->push($status);

            $exportData->push($rowData);
        }

        $totalRowData = new Collection();
        $totalRowData->push('Total Pemasukan');
        $totalRowData->push('');
        $totalPemasukanMingguan = 0;
        foreach ($this->weeksInMonth as $week => $isHoliday) {
            $totalMingguan = $uangKasData->where('minggu', $week)->sum('nominal');
            $totalRowData->push($totalMingguan);
            $totalPemasukanMingguan += $totalMingguan;
        }
        $totalPemasukanLainnya = 0;
        foreach ($this->iuranData as $iuran) {
            $totalIuranPaid = $iuran->payments->sum('nominal');
            $totalRowData->push($totalIuranPaid);
            $totalPemasukanLainnya += $totalIuranPaid;
        }
        $grandTotalPemasukan = $totalPemasukanMingguan + $totalPemasukanLainnya;
        $totalRowData->push($grandTotalPemasukan);
        $totalRowData->push('');
        $exportData->push($totalRowData);
        
        $this->totalPemasukanMingguan = $totalPemasukanMingguan;
        $this->totalPemasukanLainnya = $totalPemasukanLainnya;
        $this->totalPengeluaran = $this->pengeluaranData->sum('nominal');

        $this->summaryRowStart = $exportData->count() + 2;

        return $exportData;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Cambria');
        $totalWeeksInMonth = count($this->weeksInMonth);
        $iuranCount = $this->iuranData->count();
        $totalPemasukanCols = $totalWeeksInMonth + $iuranCount;
        $lastColIndex = 2 + $totalPemasukanCols + 2;
        $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);

        $headerStartRow = 6;
        $headerEndRow = $headerStartRow + 2;
        $firstDataRow = $headerEndRow + 1;
        $lastDataRow = $firstDataRow + $this->dataRowsCount - 1;
        $totalRow = $lastDataRow + 1;
        $sheet->getRowDimension($totalRow)->setRowHeight(20);

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
        
        $pemasukanStartCol = Coordinate::stringFromColumnIndex(3);
        $pemasukanEndCol = Coordinate::stringFromColumnIndex(2 + $totalPemasukanCols);
        $sheet->mergeCells($pemasukanStartCol . $headerStartRow . ':' . $pemasukanEndCol . $headerStartRow);
        $sheet->setCellValue($pemasukanStartCol . $headerStartRow, 'Pemasukan');

        $totalCol = Coordinate::stringFromColumnIndex(3 + $totalPemasukanCols);
        $sheet->mergeCells($totalCol . $headerStartRow . ':' . $totalCol . $headerEndRow);
        $sheet->setCellValue($totalCol . $headerStartRow, 'Total');

        $statusCol = Coordinate::stringFromColumnIndex(4 + $totalPemasukanCols);
        $sheet->mergeCells($statusCol . $headerStartRow . ':' . $statusCol . $headerEndRow);
        $sheet->setCellValue($statusCol . $headerStartRow, 'Status');

        $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $totalRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
            
        $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $headerEndRow)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        
        $sheet->getStyle('A' . $firstDataRow . ':A' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $dataStartCol = Coordinate::stringFromColumnIndex(3);
        $dataEndCol = Coordinate::stringFromColumnIndex(2 + $totalPemasukanCols);
        $sheet->getStyle($dataStartCol . $firstDataRow . ':' . $dataEndCol . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->getStyle($totalCol . $firstDataRow . ':' . $totalCol . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($statusCol . $firstDataRow . ':' . $statusCol . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $firstDataRow . ':B' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->mergeCells('A' . $totalRow . ':B' . $totalRow);
        $sheet->getStyle('A' . $totalRow . ':' . $lastCol . $totalRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $totalRow . ':' . $lastCol . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        
        $colIndex = 3;
        foreach ($this->weeksInMonth as $week => $isHoliday) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            if ($isHoliday) {
                $sheet->getStyle($col . ($headerStartRow + 2) . ':' . $col . $totalRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFD21A1A');
            }
            $colIndex++;
        }
        
        $pemasukanDataStartCol = Coordinate::stringFromColumnIndex(3 + $totalWeeksInMonth);
        $sheet->getStyle($pemasukanDataStartCol . ($headerStartRow + 2) . ':' . $pemasukanEndCol . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($totalCol . ($headerStartRow + 2) . ':' . $totalCol . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalWeeksInMonth = count($this->weeksInMonth);
                $iuranCount = $this->iuranData->count();
                $totalPemasukanCols = $totalWeeksInMonth + $iuranCount;
                $lastColIndex = 2 + $totalPemasukanCols + 2;
                
                $headerEndRow = $this->headerRowStart + 2;
                $lastDataRow = $headerEndRow + $this->dataRowsCount;
                $totalRow = $lastDataRow + 1;
                $statusCol = Coordinate::stringFromColumnIndex(4 + $totalPemasukanCols);

                $summaryTableStartRow = $totalRow + 2;
                $summaryTableEndRow = $summaryTableStartRow + 4 + $this->iuranData->count() + $this->pengeluaranData->count() + 2;
                $summaryTableStartCol = 'B';
                $summaryTableEndCol = 'D';

                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension($statusCol)->setWidth(15);
                
                $totalPemasukanKeseluruhan = $this->totalPemasukanMingguan + $this->totalPemasukanLainnya;
                $saldoAkhir = $totalPemasukanKeseluruhan - $this->totalPengeluaran;
                
                $currentRow = $summaryTableStartRow;
                
                $sheet->setCellValue($summaryTableStartCol . $currentRow, 'Ringkasan Keuangan');
                $sheet->mergeCells($summaryTableStartCol . $currentRow . ':' . $summaryTableEndCol . $currentRow);
                $sheet->getStyle($summaryTableStartCol . $currentRow)->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle($summaryTableStartCol . $currentRow . ':' . $summaryTableEndCol . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $currentRow++;
                
                $sheet->setCellValue($summaryTableStartCol . $currentRow, 'Deskripsi');
                $sheet->setCellValue('C' . $currentRow, 'Pemasukan');
                $sheet->setCellValue('D' . $currentRow, 'Pengeluaran');
                $sheet->getStyle($summaryTableStartCol . $currentRow . ':' . $summaryTableEndCol . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle($summaryTableStartCol . $currentRow . ':' . $summaryTableEndCol . $currentRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC4D79B');
                $currentRow++;

                $sheet->setCellValue($summaryTableStartCol . $currentRow, 'Pemasukan');
                $sheet->mergeCells($summaryTableStartCol . $currentRow . ':' . $summaryTableEndCol . $currentRow);
                $sheet->getStyle($summaryTableStartCol . $currentRow)->getFont()->setBold(true);
                $currentRow++;

                $sheet->setCellValue($summaryTableStartCol . $currentRow, 'Pemasukan Mingguan');
                $sheet->setCellValue('C' . $currentRow, $this->totalPemasukanMingguan);
                $currentRow++;

                foreach ($this->iuranData as $iuran) {
                    $totalIuran = $iuran->payments->where('status', 'paid')->sum('nominal');
                    $sheet->setCellValue($summaryTableStartCol . $currentRow, $iuran->deskripsi);
                    $sheet->setCellValue('C' . $currentRow, $totalIuran);
                    $currentRow++;
                }
                
                $sheet->setCellValue($summaryTableStartCol . $currentRow, 'Total Pemasukan');
                $sheet->setCellValue('C' . $currentRow, $totalPemasukanKeseluruhan);
                $sheet->getStyle($summaryTableStartCol . $currentRow . ':' . $summaryTableEndCol . $currentRow)->getFont()->setBold(true);
                $currentRow++;

                $sheet->setCellValue($summaryTableStartCol . $currentRow, 'Pengeluaran');
                $sheet->mergeCells($summaryTableStartCol . $currentRow . ':' . $summaryTableEndCol . $currentRow);
                $sheet->getStyle($summaryTableStartCol . $currentRow)->getFont()->setBold(true);
                $currentRow++;

                if ($this->pengeluaranData->count() > 0) {
                    foreach ($this->pengeluaranData as $pengeluaran) {
                        $sheet->setCellValue($summaryTableStartCol . $currentRow, $pengeluaran->deskripsi);
                        $sheet->setCellValue('D' . $currentRow, $pengeluaran->nominal);
                        $currentRow++;
                    }
                } else {
                    $sheet->setCellValue($summaryTableStartCol . $currentRow, 'Tidak ada pengeluaran bulan ini');
                    $sheet->setCellValue('D' . $currentRow, 0);
                    $currentRow++;
                }

                $sheet->setCellValue($summaryTableStartCol . $currentRow, 'Total Pengeluaran');
                $sheet->setCellValue('D' . $currentRow, $this->totalPengeluaran);
                $sheet->getStyle($summaryTableStartCol . $currentRow . ':' . $summaryTableEndCol . $currentRow)->getFont()->setBold(true);
                $currentRow++;

                $sheet->setCellValue($summaryTableStartCol . $currentRow, 'SALDO AKHIR');
                $sheet->mergeCells($summaryTableStartCol . $currentRow . ':C' . $currentRow);
                $sheet->setCellValue('D' . $currentRow, $saldoAkhir);
                $sheet->getStyle($summaryTableStartCol . $currentRow . ':' . $summaryTableEndCol . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle('D' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC4D79B');
                
                $summaryTableEndRow = $currentRow;
                $sheet->getStyle($summaryTableStartCol . ($summaryTableStartRow+1) . ':' . $summaryTableEndCol . $summaryTableEndRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle('C' . ($summaryTableStartRow + 2) . ':C' . $summaryTableEndRow)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('D' . ($summaryTableStartRow + 2) . ':D' . $summaryTableEndRow)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('C' . ($summaryTableStartRow + 2) . ':' . $summaryTableEndCol . $summaryTableEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('B' . ($summaryTableStartRow + 2) . ':' . 'B' . $summaryTableEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $legendStartRow = $summaryTableStartRow;
                $legendStartCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($summaryTableEndCol) + 2);
                $legendEndCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($summaryTableEndCol) + 3);
                $legendEndRow = $legendStartRow + 3;
                
                $sheet->getColumnDimension($legendStartCol)->setWidth(10);
                $sheet->getColumnDimension($legendEndCol)->setWidth(15);
                
                $sheet->mergeCells($legendStartCol . $legendStartRow . ':' . $legendEndCol . $legendStartRow);
                $sheet->setCellValue($legendStartCol . $legendStartRow, 'Keterangan');
                $sheet->getStyle($legendStartCol . $legendStartRow)->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle($legendStartCol . $legendStartRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle($legendStartCol . ($legendStartRow+1) . ':' . $legendEndCol . ($legendStartRow+1))->getFont()->setBold(true);
                $sheet->getStyle($legendStartCol . ($legendStartRow+1) . ':' . $legendEndCol . ($legendStartRow+1))->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFC4D79B');
                
                $sheet->getStyle($legendStartCol . ($legendStartRow+1) . ':' . $legendEndCol . $legendEndRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                
                $sheet->getStyle($legendStartCol . ($legendStartRow + 4))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD21A1A');
                $sheet->getStyle($legendStartCol . ($legendStartRow + 4))->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

                $sheet->setCellValue($legendStartCol . ($legendStartRow+1), 'Simbol');
                $sheet->setCellValue($legendEndCol . ($legendStartRow+1), 'Arti');
                $sheet->setCellValue($legendStartCol . ($legendStartRow+2), '✓');
                $sheet->setCellValue($legendEndCol . ($legendStartRow+2), 'Lunas');
                $sheet->setCellValue($legendStartCol . ($legendStartRow+3), '✗');
                $sheet->setCellValue($legendEndCol . ($legendStartRow+3), 'Belum Lunas');
                $sheet->setCellValue($legendStartCol . ($legendStartRow+4), '');
                $sheet->setCellValue($legendEndCol . ($legendStartRow+4), 'Hari Libur');
                $sheet->getStyle($legendStartCol . ($legendStartRow+4))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD21A1A');
            },
        ];
    }
}