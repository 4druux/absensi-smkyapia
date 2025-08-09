<?php

namespace App\Http\Controllers\Absensi;

use App\Models\AcademicYear;
use App\Models\Kelas;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Holiday;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
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

    public function selectClass()
    {
        $classes = Kelas::with('jurusan')->get();
        return Inertia::render('Absensi/SelectClassPage', [
            'classes' => $classes,
        ]);
    }
    
    public function selectYear($kelas, $jurusan)
    {
        $years = AcademicYear::orderBy('year', 'asc')->get();
        
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        return Inertia::render('Absensi/SelectYearPage', [
            'years' => $years->map(fn ($y) => ['nomor' => $y->year . '-' . ($y->year + 1)]),
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
            ],
        ]);
    }
    
    public function selectMonth($kelas, $jurusan, $tahun)
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

        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        return Inertia::render('Absensi/SelectMonthPage', [
            'months' => $months,
            'tahun' => $tahun,
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
            ],
        ]);
    }
    
    public function showMonth($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        if (!$monthNumber) {
            abort(404);
        }

        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $year = $this->getCorrectYear($tahun, $monthNumber);
        $date = Carbon::create($year, $monthNumber, 1);
        $daysInMonth = $date->daysInMonth;
        $namaBulan = $date->translatedFormat('F');
        $students = $selectedKelas->siswas;
        
        $absensiDays = Absensi::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $monthNumber)
            ->whereIn('siswa_id', $students->pluck('id'))
            ->distinct()
            ->pluck(DB::raw('DAY(tanggal)')); 
        
        $dbHolidays = Holiday::whereYear('date', $year)
            ->whereMonth('date', $monthNumber)
            ->get()
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->day);
        
        $days = collect();
        $firstDayOfMonth = Carbon::create($year, $monthNumber, 1);
        $startDayOfWeek = $firstDayOfMonth->dayOfWeek; 
        
        for ($i = $startDayOfWeek; $i > 0; $i--) {
            $prevDate = $firstDayOfMonth->copy()->subDays($i);
            $days->push([
                'nomor' => $prevDate->day,
                'nama_hari' => $prevDate->translatedFormat('l'),
                'is_weekend' => $prevDate->isWeekend(),
                'is_outside_month' => true,
            ]);
        }
        
        $realDays = collect(range(1, $daysInMonth))->map(function ($day) use ($year, $monthNumber) {
            $date = Carbon::create($year, $monthNumber, $day);
            return [
                'nomor' => $day,
                'nama_hari' => $date->translatedFormat('l'),
                'is_weekend' => $date->isWeekend(),
                'is_outside_month' => false,
            ];
        });

        $days = $days->merge($realDays);

        $lastDayOfMonth = Carbon::create($year, $monthNumber, $daysInMonth);
        $endDayOfWeek = $lastDayOfMonth->dayOfWeek;
        $paddingEndDaysCount = 6 - $endDayOfWeek;
        if ($paddingEndDaysCount > 0) {
            $firstDayOfNextMonth = $lastDayOfMonth->copy()->addDay();
            for ($i = 0; $i < $paddingEndDaysCount; $i++) {
                $nextDate = $firstDayOfNextMonth->copy()->addDays($i);
                $days->push([
                    'nomor' => $nextDate->day,
                    'nama_hari' => $nextDate->translatedFormat('l'),
                    'is_weekend' => $nextDate->isWeekend(),
                    'is_outside_month' => true,
                ]);
            }
        }

        $allHolidays = $days->filter(fn($day) => $day['is_weekend'] && !$day['is_outside_month'])
                            ->pluck('nomor')
                            ->filter()
                            ->merge($dbHolidays)
                            ->unique();

        return Inertia::render('Absensi/SelectDayPage', [
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
            ],
            'tahun' => $tahun,
            'bulan' => $bulanSlug,
            'namaBulan' => $namaBulan,
            'days' => $days,
            'absensiDays' => $absensiDays,
            'holidays' => $allHolidays, 
        ]);
    }
    
    public function showDay($kelas, $jurusan, $tahun, $bulanSlug, $tanggal)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $year = $this->getCorrectYear($tahun, $monthNumber);
        
        if (!$monthNumber || !checkdate($monthNumber, (int) $tanggal, (int) $year)) {
            abort(404);
        }
        
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();
            
        $selectedKelas->load('siswas');
        $allStudents = $selectedKelas->siswas;

        if ($allStudents->isEmpty()) {
             return Inertia::render('Absensi/AbsensiPage', [
                'studentData' => [
                    'students' => [],
                    'classCode' => $selectedKelas->nama_kelas,
                    'major' => $selectedKelas->jurusan->nama_jurusan,
                ],
                'tanggal' => $tanggal,
                'bulan' => $bulanSlug,
                'namaBulan' => Carbon::create($year, $monthNumber, 1)->translatedFormat('F'),
                'tahun' => $tahun,
                'tanggalAbsen' => null,
                'existingAttendance' => [],
                'selectedClass' => [
                    'id' => $selectedKelas->id,
                    'kelas' => $selectedKelas->nama_kelas,
                    'jurusan' => $selectedKelas->jurusan->nama_jurusan,
                ],
            ]);
        }
            
        $targetDate = Carbon::create($year, $monthNumber, $tanggal);
        $studentData = null;
        $tanggalAbsen = null;

        if ($allStudents->isNotEmpty()) {
            $studentData = [
                'classCode' => $selectedKelas->nama_kelas,
                'major' => $selectedKelas->jurusan->nama_jurusan,
                'students' => $allStudents->values()->all(),
            ];
        }

        $existingAttendance = Absensi::whereDate('tanggal', $targetDate->toDateString())
            ->whereIn('siswa_id', $allStudents->pluck('id'))
            ->get();

        if ($existingAttendance->isNotEmpty()) {
            $tanggalAbsen = Carbon::parse($existingAttendance->first()->updated_at)
                ->setTimezone('Asia/Jakarta')
                ->translatedFormat('d-m-Y H:i:s');
        }

        return Inertia::render('Absensi/AbsensiPage', [
            'studentData' => $studentData,
            'tanggal' => $targetDate->day,
            'bulan' => $bulanSlug,
            'namaBulan' => $targetDate->translatedFormat('F'),
            'tahun' => $tahun,
            'tanggalAbsen' => $tanggalAbsen,
            'existingAttendance' => $existingAttendance->pluck('status', 'siswa_id'),
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
            ],
        ]);
    }

    public function storeYear(Request $request)
    {
        $request->validate(['kelas_id' => 'required|exists:kelas,id']);

        $latestYear = AcademicYear::orderBy('year', 'desc')->first();
        $yearToCreate = now()->month >= 7 ? now()->year : now()->year - 1;
        
        if ($latestYear) {
                $yearToCreate = $latestYear->year + 1;
        }

        $academicYear = AcademicYear::firstOrCreate(['year' => $yearToCreate]);
        
        $kelas = Kelas::with('jurusan')->findOrFail($request->kelas_id);

        return redirect()->route('absensi.class.show', [
                'kelas' => $kelas->nama_kelas,
                'jurusan' => $kelas->jurusan->nama_jurusan
        ])->with('success', 'Tahun ajaran berhasil ditambahkan!');
    }
    
    public function storeHoliday($kelas, $jurusan, $tahun, $bulanSlug, $tanggal)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $year = $this->getCorrectYear($tahun, $monthNumber);
        $holidayDate = Carbon::create($year, $monthNumber, $tanggal)->toDateString();

        $existingHoliday = Holiday::where('date', $holidayDate)->exists();
        if ($existingHoliday) {
            return redirect()->back()->with('error', 'Tanggal ini sudah terdaftar sebagai hari libur.');
        }

        Holiday::create([
            'date' => $holidayDate,
            'description' => 'Hari Libur Tambahan',
        ]);
        
        return redirect()->back()->with('success', 'Tanggal berhasil ditetapkan sebagai hari libur.');
    }
}