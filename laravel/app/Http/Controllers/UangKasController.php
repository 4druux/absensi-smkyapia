<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Siswa;
use App\Models\AcademicYear;
use App\Models\UangKasPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\UangKasExport;
use App\Models\Holiday;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class UangKasController extends Controller
{
    private function getMonthNumberFromSlug($slug)
    {
        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];
        return $monthMap[strtolower($slug)] ?? null;
    }

    public function index()
    {
        $classes = Siswa::select('kelas', 'jurusan')
            ->distinct()
            ->orderBy('kelas')
            ->get();

        return Inertia::render('UangKas/SelectClass', [
            'classes' => $classes,
        ]);
    }

    public function showClass($kelas, $jurusan)
    {
        $academicYears = AcademicYear::all();

        return Inertia::render('UangKas/SelectYear', [
            'selectedClass' => (object) ['kelas' => $kelas, 'jurusan' => $jurusan],
            'academicYears' => $academicYears,
        ]);
    }

    public function showYear($kelas, $jurusan, $tahun)
    {
        $months = [
            (object)['label' => 'Januari', 'slug' => 'januari', 'id' => 1],
            (object)['label' => 'Februari', 'slug' => 'februari', 'id' => 2],
            (object)['label' => 'Maret', 'slug' => 'maret', 'id' => 3],
            (object)['label' => 'April', 'slug' => 'april', 'id' => 4],
            (object)['label' => 'Mei', 'slug' => 'mei', 'id' => 5],
            (object)['label' => 'Juni', 'slug' => 'juni', 'id' => 6],
            (object)['label' => 'Juli', 'slug' => 'juli', 'id' => 7],
            (object)['label' => 'Agustus', 'slug' => 'agustus', 'id' => 8],
            (object)['label' => 'September', 'slug' => 'september', 'id' => 9],
            (object)['label' => 'Oktober', 'slug' => 'oktober', 'id' => 10],
            (object)['label' => 'November', 'slug' => 'november', 'id' => 11],
            (object)['label' => 'Desember', 'slug' => 'desember', 'id' => 12],
        ];

        return Inertia::render('UangKas/SelectMonth', [
            'selectedClass' => (object) ['kelas' => $kelas, 'jurusan' => $jurusan],
            'tahun' => $tahun,
            'months' => $months,
        ]);
    }

    public function showMonth($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        if (!$monthNumber) {
            abort(404);
        }

        $firstDayOfMonth = Carbon::create($tahun, $monthNumber, 1);
        $daysInMonth = $firstDayOfMonth->daysInMonth;
        $namaBulan = $firstDayOfMonth->translatedFormat('F');

        $weeks = [];
        $weekNumber = 1;

        $startOfWeek = $firstDayOfMonth->copy();
        if ($startOfWeek->dayOfWeek !== Carbon::SUNDAY) {
            $startOfWeek->startOfWeek(Carbon::SUNDAY);
        }
        
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();

        while ($startOfWeek <= $lastDayOfMonth) {
            $endOfWeek = $startOfWeek->copy()->addDays(6);
            
            $weeks[] = [
                'id' => $weekNumber,
                'label' => "Minggu ke-$weekNumber",
                'start_date' => $startOfWeek->format('d-m-Y'),
                'end_date' => $endOfWeek->format('d-m-Y'),
            ];
            $weekNumber++;
            $startOfWeek->addWeeks();
        }
        
        $reindexedMinggu = array_values($weeks);
        
        foreach ($reindexedMinggu as $index => $week) {
            $weekStart = Carbon::createFromFormat('d-m-Y', $week['start_date']);
            $weekEnd = Carbon::createFromFormat('d-m-Y', $week['end_date']);
            
            $displayStart = $weekStart->copy();
            if ($displayStart->month !== $monthNumber) {
                $displayStart = $firstDayOfMonth->copy();
            }
            
            $displayEnd = $weekEnd->copy();
            if ($displayEnd->month !== $monthNumber) {
                $displayEnd = $lastDayOfMonth->copy();
            }

            if ($displayStart->day == $displayEnd->day) {
                $reindexedMinggu[$index]['display_date'] = $displayStart->format('d-m-Y');
            } else {
                $reindexedMinggu[$index]['display_date_range'] = $displayStart->format('d-m-Y') . ' s.d. ' . $displayEnd->format('d-m-Y');
            }
        }
        
        $siswaIds = Siswa::where('kelas', $kelas)->where('jurusan', $jurusan)->pluck('id');
        $totalSiswa = count($siswaIds);

        $paidStudentsCountByWeek = UangKasPayment::whereIn('siswa_id', $siswaIds)
            ->where('tahun', $tahun)
            ->where('bulan_slug', $bulanSlug)
            ->where('status', 'paid')
            ->select('minggu', DB::raw('count(*) as paid_count'))
            ->groupBy('minggu')
            ->pluck('paid_count', 'minggu')
            ->toArray();

        $fullyPaidWeeks = collect($paidStudentsCountByWeek)
            ->filter(fn($count) => $count === $totalSiswa)
            ->keys()
            ->toArray();
        
        $dbHolidays = Holiday::whereYear('date', $tahun)
            ->whereMonth('date', $monthNumber)
            ->get()
            ->pluck('date')->toArray();

        $finalMinggu = [];
        foreach($reindexedMinggu as $week) {
            $weekStart = Carbon::createFromFormat('d-m-Y', $week['start_date']);
            $weekEnd = Carbon::createFromFormat('d-m-Y', $week['end_date']);
            
            $isPaid = in_array($week['id'], $fullyPaidWeeks);

            $isHoliday = true;
            $currentDate = $weekStart->copy();
            while($currentDate <= $weekEnd) {
                if ($currentDate->month !== $monthNumber) {
                     $currentDate->addDay();
                     continue;
                }
                if (!in_array($currentDate->format('Y-m-d'), $dbHolidays) && !$currentDate->isWeekend()) {
                    $isHoliday = false;
                    break;
                }
                $currentDate->addDay();
            }

            $finalMinggu[] = [
                'id' => $week['id'],
                'label' => $week['label'],
                'start_date' => $week['start_date'],
                'end_date' => $week['end_date'],
                'display_date' => $week['display_date'] ?? null,
                'display_date_range' => $week['display_date_range'] ?? null,
                'is_paid' => $isPaid,
                'is_holiday' => $isHoliday,
            ];
        }

        return Inertia::render('UangKas/SelectWeek', [
            'selectedClass' => (object) ['kelas' => $kelas, 'jurusan' => $jurusan],
            'tahun' => $tahun,
            'bulanSlug' => $bulanSlug,
            'namaBulan' => $namaBulan,
            'minggu' => $finalMinggu,
        ]);
    }
    
    public function showWeek($kelas, $jurusan, $tahun, $bulanSlug, $minggu)
    {
        $siswa = Siswa::where('kelas', $kelas)
            ->where('jurusan', $jurusan)
            ->orderBy('nama')
            ->get();

        $existingPayments = UangKasPayment::where('tahun', $tahun)
            ->where('bulan_slug', $bulanSlug)
            ->where('minggu', $minggu)
            ->whereIn('siswa_id', $siswa->pluck('id'))
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->siswa_id => ['nominal' => $item->nominal, 'status' => $item->status]];
            })
            ->toArray();

        $monthNumber = array_search($bulanSlug, [
            'januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli',
            'agustus', 'september', 'oktober', 'november', 'desember',
        ]) + 1;
        $namaBulan = Carbon::createFromDate($tahun, $monthNumber, 1)->translatedFormat('F');

        return Inertia::render('UangKas/UangKasPage', [
            'studentData' => (object) [
                'students' => $siswa,
                'classCode' => $kelas,
                'major' => $jurusan,
            ],
            'selectedClass' => (object) ['kelas' => $kelas, 'jurusan' => $jurusan],
            'tahun' => $tahun,
            'bulanSlug' => $bulanSlug,
            'namaBulan' => $namaBulan,
            'minggu' => $minggu,
            'existingPayments' => $existingPayments,
        ]);
    }

    public function store(Request $request, $kelas, $jurusan, $tahun, $bulanSlug, $minggu)
    {
        $request->validate([
            'fixed_nominal' => 'required|numeric|min:0',
            'payments' => 'required|array',
            'payments.*.siswa_id' => 'required|exists:siswas,id',
            'payments.*.status' => 'required|in:paid,unpaid',
        ]);

        $fixedNominal = $request->input('fixed_nominal');
        
        $paidSiswaIds = collect($request->payments)->where('status', 'paid')->pluck('siswa_id');

        UangKasPayment::where('tahun', $tahun)
            ->where('bulan_slug', $bulanSlug)
            ->where('minggu', $minggu)
            ->whereNotIn('siswa_id', $paidSiswaIds)
            ->delete();

        foreach ($request->payments as $paymentData) {
            if ($paymentData['status'] === 'paid') {
                UangKasPayment::updateOrCreate(
                    [
                        'siswa_id' => $paymentData['siswa_id'],
                        'tahun' => $tahun,
                        'bulan_slug' => $bulanSlug,
                        'minggu' => $minggu,
                    ],
                    [
                        'nominal' => $fixedNominal,
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]
                );
            }
        }

        return redirect()->route('uang-kas.week.show', [
            'kelas' => $kelas,
            'jurusan' => $jurusan,
            'tahun' => $tahun,
            'bulanSlug' => $bulanSlug,
            'minggu' => $minggu
        ])->with('success', 'Data pembayaran uang kas berhasil diperbarui!');
    }

    public function storeYear(Request $request)
    {
        $request->validate([
            'kelas' => 'required|string',
            'jurusan' => 'required|string',
        ]);

        $latestYear = AcademicYear::orderBy('year', 'desc')->first();
        $yearToAdd = $latestYear ? $latestYear->year + 1 : now()->year;
        AcademicYear::firstOrCreate(['year' => $yearToAdd]);

        return redirect()->route('uang-kas.class.show', [
            'kelas' => $request->kelas,
            'jurusan' => $request->jurusan
        ])->with('success', 'Tahun ajaran berhasil ditambahkan!');
    }

    public function storeHoliday($kelas, $jurusan, $tahun, $bulanSlug, $minggu)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $startOfMonth = Carbon::createFromDate($tahun, $monthNumber, 1);
        $targetWeek = $startOfMonth->addWeeks($minggu - 1)->startOfWeek(Carbon::MONDAY);
        
        $holidayDate = $targetWeek->format('d-m-Y');

        $existingHoliday = Holiday::where('date', $holidayDate)->first();

        if ($existingHoliday) {
            return redirect()->back()->with('error', 'Minggu ini sudah terdaftar sebagai hari libur.');
        }

        Holiday::create([
            'date' => $holidayDate,
            'description' => "Hari Libur Uang Kas Minggu ke-{$minggu}",
        ]);

        return redirect()->back()->with('success', 'Minggu berhasil ditetapkan sebagai hari libur.');
    }
    
    public function exportMonthExcel($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        
        $students = Siswa::where('kelas', $kelas)->where('jurusan', $jurusan)->get();
        if ($students->isEmpty()) {
            $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
            return response()->json(['error' => "Tidak ada data siswa di kelas ini."], 404);
        }

        $uangKasData = UangKasPayment::whereIn('siswa_id', $students->pluck('id'))
            ->where('tahun', $tahun)
            ->where('bulan_slug', $bulanSlug)
            ->get();
        
        $dbHolidays = Holiday::whereYear('date', $tahun)
            ->whereMonth('date', $monthNumber)
            ->get()
            ->pluck('date');

        $weeksInMonth = [];
        $firstDayOfMonth = Carbon::create($tahun, $monthNumber, 1);
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
        $startOfWeek = $firstDayOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        
        $weekNumber = 1;
        while ($startOfWeek <= $lastDayOfMonth) {
            $endOfWeek = $startOfWeek->copy()->addDays(6);
            $isWeekHoliday = true;
            $currentDay = $startOfWeek->copy();
            
            while ($currentDay <= $endOfWeek) {
                if ($currentDay->month === $monthNumber && !in_array($currentDay->format('Y-m-d'), $dbHolidays->toArray()) && !$currentDay->isWeekend()) {
                    $isWeekHoliday = false;
                    break;
                }
                $currentDay->addDay();
            }
            
            $weeksInMonth[$weekNumber] = $isWeekHoliday;
            
            $weekNumber++;
            $startOfWeek->addWeek();
        }

        if ($uangKasData->isEmpty() && count(array_filter($weeksInMonth, fn($isHoliday) => !$isHoliday)) > 0) {
            $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
            return response()->json(['error' => "Tidak ada data uang kas untuk bulan {$namaBulan}."], 404);
        }

        $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
        $fileName = "UangKas-{$kelas}-{$jurusan}-{$namaBulan}-{$tahun}.xlsx";

        return Excel::download(new UangKasExport($kelas, $jurusan, $tahun, $bulanSlug, $weeksInMonth), $fileName);
    }
    
    public function exportMonthPdf($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        
        $students = Siswa::where('kelas', $kelas)->where('jurusan', $jurusan)->get();
        if ($students->isEmpty()) {
            return response()->json(['error' => 'Tidak ada siswa di kelas ini.'], 404);
        }

        $uangKasData = UangKasPayment::whereIn('siswa_id', $students->pluck('id'))
            ->where('tahun', $tahun)
            ->where('bulan_slug', $bulanSlug)
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->siswa_id . '_' . $item->minggu => $item,
                ];
            });

        $dbHolidays = Holiday::whereYear('date', $tahun)
            ->whereMonth('date', $monthNumber)
            ->get()
            ->pluck('date');
        
        $weeksInMonth = [];
        $firstDayOfMonth = Carbon::create($tahun, $monthNumber, 1);
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
        $startOfWeek = $firstDayOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        
        $weekNumber = 1;
        while ($startOfWeek <= $lastDayOfMonth) {
            $endOfWeek = $startOfWeek->copy()->addDays(6);
            $isWeekHoliday = true;
            $currentDay = $startOfWeek->copy();
            
            while ($currentDay <= $endOfWeek) {
                if ($currentDay->month === $monthNumber && !in_array($currentDay->format('Y-m-d'), $dbHolidays->toArray()) && !$currentDay->isWeekend()) {
                    $isWeekHoliday = false;
                    break;
                }
                $currentDay->addDay();
            }
            
            $weeksInMonth[$weekNumber] = $isWeekHoliday;
            
            $weekNumber++;
            $startOfWeek->addWeek();
        }

        $paidWeeksCount = 0;
        foreach ($students as $student) {
            $paidCount = $uangKasData->filter(fn($payment) => $payment->siswa_id === $student->id && $payment->status === 'paid')->count();
            $nonHolidayWeeks = count(array_filter($weeksInMonth, fn($isHoliday) => !$isHoliday));
            if ($paidCount === $nonHolidayWeeks) {
                 $paidWeeksCount++;
            }
        }
        
        if ($uangKasData->isEmpty() && $paidWeeksCount === 0) {
            $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
            return response()->json(['error' => "Tidak ada data uang kas untuk bulan {$namaBulan}."], 404);
        }
        
        $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
        $fileName = "UangKas-{$kelas}-{$jurusan}-{$namaBulan}-{$tahun}.pdf";
        $logoPath = 'images/logo-smk.png';

        $pdf = Pdf::loadView('exports.uangkas', compact('students', 'kelas', 'jurusan', 'namaBulan', 'tahun', 'uangKasData', 'weeksInMonth', 'logoPath'));
        
        return $pdf->download($fileName);
    }
}