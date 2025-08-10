<?php

namespace App\Exports\Absensi;

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

class AbsensiExportYear implements FromCollection, WithStyles, WithEvents
{
    protected $rekapAbsensi;
    protected $kelas;
    protected $jurusan;
    protected $tahun;
    protected $dataRowsCount = 0;
    protected $grandTotals;
    protected $bulanHeaders = [];

    public function __construct($rekapAbsensi, $kelas, $jurusan, $tahun)
    {
        $this->rekapAbsensi = $rekapAbsensi;
        $this->kelas = $kelas;
        $this->jurusan = $jurusan;
        $this->tahun = $tahun;

        $this->grandTotals = ['telat' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0, 'bolos' => 0];
        foreach ($this->rekapAbsensi as $studentRekap) {
            $this->grandTotals['telat'] += $studentRekap['total_tahunan']['telat'];
            $this->grandTotals['sakit'] += $studentRekap['total_tahunan']['sakit'];
            $this->grandTotals['izin'] += $studentRekap['total_tahunan']['izin'];
            $this->grandTotals['alfa'] += $studentRekap['total_tahunan']['alfa'];
            $this->grandTotals['bolos'] += $studentRekap['total_tahunan']['bolos'];
        }

        $months = [
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        ];
        [$startYear, $endYear] = explode('-', $this->tahun);
        foreach ($months as $index => $month) {
            $year = ($index < 6) ? $startYear : $endYear;
            $this->bulanHeaders[] = $month . ' ' . $year;
        }
    }

    public function collection()
    {
        $exportData = new Collection();
        
        $exportData->push(['DATA KEHADIRAN SISWA']);
        $exportData->push(['SMK YAPIA PARUNG']);
        $exportData->push(["Kelas {$this->kelas} - {$this->jurusan}"]);
        $exportData->push(["Tahun Ajaran {$this->tahun}"]);
        $exportData->push(['']);

        $header1 = ['NO', 'NAMA SISWA'];
        $header2 = ['', ''];
        
        foreach ($this->bulanHeaders as $bulanHeader) {
            $header1[] = strtoupper($bulanHeader);
            $header1[] = null;
            $header1[] = null;
            $header1[] = null;
            $header1[] = null;
            $header2[] = 'Telat';
            $header2[] = 'Sakit';
            $header2[] = 'Izin';
            $header2[] = 'Alfa';
            $header2[] = 'Bolos';
        }

        $exportData->push($header1);
        $exportData->push($header2);
        
        foreach ($this->rekapAbsensi as $index => $studentRekap) {
            $rowData = new Collection();
            $rowData->push($index + 1);
            $rowData->push($studentRekap['nama']);
            foreach ($studentRekap['total_bulanan'] as $bulanData) {
                $rowData->push($bulanData['counts']['telat'] > 0 ? $bulanData['counts']['telat'] : '');
                $rowData->push($bulanData['counts']['sakit'] > 0 ? $bulanData['counts']['sakit'] : '');
                $rowData->push($bulanData['counts']['izin'] > 0 ? $bulanData['counts']['izin'] : '');
                $rowData->push($bulanData['counts']['alfa'] > 0 ? $bulanData['counts']['alfa'] : '');
                $rowData->push($bulanData['counts']['bolos'] > 0 ? $bulanData['counts']['bolos'] : '');
            }
            $exportData->push($rowData);
        }

        $this->dataRowsCount = count($this->rekapAbsensi);
        
        $jumlahRow = new Collection();
        $jumlahRow->push('Jumlah');
        $jumlahRow->push('');
        
        $totalBulanans = collect($this->rekapAbsensi)->pluck('total_bulanan');
        $monthlyTotals = [];
        for ($i = 0; $i < 12; $i++) {
            $monthlyTotals[$i]['telat'] = $totalBulanans->sum(fn($bulan) => $bulan[$i]['counts']['telat'] ?? 0);
            $monthlyTotals[$i]['sakit'] = $totalBulanans->sum(fn($bulan) => $bulan[$i]['counts']['sakit'] ?? 0);
            $monthlyTotals[$i]['izin'] = $totalBulanans->sum(fn($bulan) => $bulan[$i]['counts']['izin'] ?? 0);
            $monthlyTotals[$i]['alfa'] = $totalBulanans->sum(fn($bulan) => $bulan[$i]['counts']['alfa'] ?? 0);
            $monthlyTotals[$i]['bolos'] = $totalBulanans->sum(fn($bulan) => $bulan[$i]['counts']['bolos'] ?? 0);
        }
        
        foreach ($monthlyTotals as $total) {
            $jumlahRow->push($total['telat'] == 0 ? '0' : $total['telat']);
            $jumlahRow->push($total['sakit'] == 0 ? '0' : $total['sakit']);
            $jumlahRow->push($total['izin'] == 0 ? '0' : $total['izin']);
            $jumlahRow->push($total['alfa'] == 0 ? '0' : $total['alfa']);
            $jumlahRow->push($total['bolos'] == 0 ? '0' : $total['bolos']);
        }
        
        $exportData->push($jumlahRow);
        
        return $exportData;
    }
    
    public function styles(Worksheet $sheet)
    {
        $lastColIndex = (2 + 12 * 5);
        $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);
        
        $sheet->getStyle('A1:' . $lastCol . ($this->dataRowsCount + 6))->getFont()->setName('Cambria');
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->mergeCells('A3:' . $lastCol . '3');
        $sheet->mergeCells('A4:' . $lastCol . '4');
        $sheet->getStyle('A1:A4')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headerStartRow = 6;
        $headerEndRow = $headerStartRow + 1;
        
        $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $headerEndRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $headerEndRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFC4D79B');

        $sheet->mergeCells('A' . $headerStartRow . ':A' . $headerEndRow);
        $sheet->mergeCells('B' . $headerStartRow . ':B' . $headerEndRow);
        
        $colOffset = 2;
        foreach ($this->bulanHeaders as $index => $header) {
            $startColIndex = $colOffset + 1 + ($index * 5);
            $endColIndex = $startColIndex + 4;
            $startCol = Coordinate::stringFromColumnIndex($startColIndex);
            $endCol = Coordinate::stringFromColumnIndex($endColIndex);
            $sheet->mergeCells("{$startCol}{$headerStartRow}:{$endCol}{$headerStartRow}");
        }

        $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . ($headerEndRow + $this->dataRowsCount + 1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . ($headerEndRow + $this->dataRowsCount + 1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColIndex = (2 + 12 * 5);
                $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);
                
                $headerStartRow = 6;
                $headerEndRow = $headerStartRow + 1;
                $firstDataRow = $headerEndRow + 1;
                $lastDataRow = $firstDataRow + $this->dataRowsCount - 1;
                $jumlahRow = $lastDataRow + 1;
                
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $colOffset = 2;
                for ($colIndex = $colOffset + 1; $colIndex <= $lastColIndex; $colIndex++) {
                    $col = Coordinate::stringFromColumnIndex($colIndex);
                    $sheet->getColumnDimension($col)->setWidth(5);
                }
                
                $sheet->getStyle('A' . $headerStartRow . ':' . $lastCol . $jumlahRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $firstDataRow . ':B' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->mergeCells('A' . $jumlahRow . ':B' . $jumlahRow);
                
                $statusRow = $headerEndRow;
                $startColIndex = 3;
                $endColIndex = $lastColIndex;

                for ($colIndex = $startColIndex; $colIndex <= $endColIndex; $colIndex++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
                    $sheet->getStyle($columnLetter . $statusRow)->getAlignment()->setTextRotation(90);
                }

                for ($i = 0; $i < 12; $i++) {
                    $telatColIndex = 3 + ($i * 5);
                    $telatCol = Coordinate::stringFromColumnIndex($telatColIndex);
                    $sheet->getStyle($telatCol . $firstDataRow . ':' . $telatCol . $jumlahRow)
                          ->getFill()
                          ->setFillType(Fill::FILL_SOLID)
                          ->getStartColor()
                          ->setARGB('FFC4D79B');
                }
                $sheet->getRowDimension($headerStartRow)->setRowHeight(25);
                $sheet->getRowDimension($statusRow)->setRowHeight(40);
                $sheet->getRowDimension($jumlahRow)->setRowHeight(30);
            }
        ];
    }
}