<?php

namespace App\Http\Controllers\UangKas;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Holiday;
use App\Models\UangKasPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UangKasApiController extends Controller
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
    
    private function getCorrectYear($academicYear, $month)
    {
        [$startYear, $endYear] = explode('-', $academicYear);
        return $month >= 7 ? $startYear : $endYear;
    }
    
    public function getClasses()
    {
        $classes = Kelas::with('jurusan')->get();
        return response()->json($classes->map(fn($c) => [
            'id' => $c->id,
            'kelas' => $c->nama_kelas,
            'jurusan' => $c->jurusan->nama_jurusan,
            'kelompok' => $c->kelompok,
        ]));
    }
    
    public function getYears()
    {
        $years = AcademicYear::orderBy('year', 'asc')->get();

        if ($years->isEmpty()) {
            $currentYear = now()->month >= 7 ? now()->year : now()->year - 1;
            AcademicYear::create(['year' => $currentYear]);
            $years = AcademicYear::orderBy('year', 'asc')->get();
        }
        
        return response()->json($years->map(fn ($y) => ['nomor' => $y->year . '-' . ($y->year + 1)]));
    }

    public function storeYearApi()
    {
        $latestYear = AcademicYear::orderBy('year', 'desc')->first();
        $yearToCreate = now()->month >= 7 ? now()->year : now()->year - 1;
        
        if ($latestYear) {
                $yearToCreate = $latestYear->year + 1;
        }
        
        $academicYear = AcademicYear::firstOrCreate(['year' => $yearToCreate]);
        
        return response()->json(['message' => 'Tahun ajaran berhasil ditambahkan!', 'year' => $academicYear], 201);
    }
    
    public function getMonths($kelas, $jurusan, $tahun)
    {
        $startYear = intval(explode('-', $tahun)[0]);
        $endYear = intval(explode('-', $tahun)[1]);

        $months = collect(range(7, 12))->map(function ($month) use ($startYear) {
            $date = Carbon::create($startYear, $month, 1);
            return [
                'nama' => $date->translatedFormat('F'),
                'slug' => strtolower($date->translatedFormat('F')),
            ];
        })->merge(collect(range(1, 6))->map(function ($month) use ($endYear) {
            $date = Carbon::create($endYear, $month, 1);
            return [
                'nama' => $date->translatedFormat('F'),
                'slug' => strtolower($date->translatedFormat('F')),
            ];
        }));

        return response()->json($months);
    }

    public function getWeeks($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        if (!$monthNumber) {
            return response()->json(['error' => 'Bulan tidak valid.'], 404);
        }
        
        $year = $this->getCorrectYear($tahun, $monthNumber);
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $firstDayOfMonth = Carbon::create($year, $monthNumber, 1);
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
        $students = $selectedKelas->siswas;

        $weeks = [];
        $weekNumber = 1;

        $startOfWeek = $firstDayOfMonth->copy();
        if ($startOfWeek->dayOfWeek !== Carbon::SUNDAY) {
            $startOfWeek->startOfWeek(Carbon::SUNDAY);
        }

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
        
        $siswaIds = $students->pluck('id');
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

        $dbHolidays = Holiday::whereYear('date', $year)
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
        
        return response()->json([
            'minggu' => $finalMinggu,
        ]);
    }

    public function getPayments($kelas, $jurusan, $tahun, $bulanSlug, $minggu)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        if (!$monthNumber) {
            return response()->json(['error' => 'Bulan tidak valid.'], 404);
        }

        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
        
        $siswa = $selectedKelas->siswas;

        if ($siswa->isEmpty()) {
            return response()->json(['error' => "Tidak ada siswa di kelas ini."], 404);
        }

        $existingPayments = UangKasPayment::where('tahun', $tahun)
            ->where('bulan_slug', $bulanSlug)
            ->where('minggu', $minggu)
            ->whereIn('siswa_id', $siswa->pluck('id'))
            ->get();
        
        return response()->json([
            'students' => $siswa,
            'existingPayments' => $existingPayments->mapWithKeys(function ($item) {
                return [$item->siswa_id => ['nominal' => $item->nominal, 'status' => $item->status]];
            })->toArray(),
        ]);
    }

    public function storePayments(Request $request, $kelas, $jurusan, $tahun, $bulanSlug, $minggu)
    {
        $request->validate([
            'fixed_nominal' => 'required|numeric|min:0',
            'payments' => 'required|array',
            'payments.*.siswa_id' => 'required|exists:siswas,id',
            'payments.*.status' => 'required|in:paid,unpaid',
        ]);
        
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $year = $this->getCorrectYear($tahun, $monthNumber);

        $fixedNominal = $request->input('fixed_nominal');
        $paidSiswaIds = collect($request->payments)->where('status', 'paid')->pluck('siswa_id');

        DB::transaction(function () use ($paidSiswaIds, $year, $bulanSlug, $minggu, $fixedNominal) {
            UangKasPayment::where('tahun', $year)
                ->where('bulan_slug', $bulanSlug)
                ->where('minggu', $minggu)
                ->whereNotIn('siswa_id', $paidSiswaIds)
                ->delete();

            foreach ($paidSiswaIds as $siswaId) {
                UangKasPayment::updateOrCreate(
                    [
                        'siswa_id' => $siswaId,
                        'tahun' => $year,
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
        });
        
        return response()->json(['message' => 'Data pembayaran uang kas berhasil diperbarui!'], 200);
    }
    
    public function storeHolidayApi($kelas, $jurusan, $tahun, $bulanSlug, $minggu)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $year = $this->getCorrectYear($tahun, $monthNumber);
        $startOfMonth = Carbon::createFromDate($year, $monthNumber, 1);
        $targetWeekStart = $startOfMonth->copy()->addWeeks($minggu - 1)->startOfWeek(Carbon::SUNDAY);
        
        $weekDateToMark = $targetWeekStart->isSameMonth($startOfMonth) ? $targetWeekStart : $startOfMonth;

        $existingHoliday = Holiday::where('date', $weekDateToMark->toDateString())->first();

        if ($existingHoliday) {
            return response()->json(['message' => 'Minggu ini sudah terdaftar sebagai hari libur.'], 409);
        }

        Holiday::create([
            'date' => $weekDateToMark->toDateString(),
            'description' => "Hari Libur Uang Kas Minggu ke-{$minggu}",
        ]);

        return response()->json(['message' => 'Minggu berhasil ditetapkan sebagai hari libur.'], 201);
    }
}