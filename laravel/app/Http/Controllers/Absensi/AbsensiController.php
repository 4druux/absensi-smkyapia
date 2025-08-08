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

        if ($years->isEmpty()) {
            $currentYear = now()->year;
            AcademicYear::create(['year' => $currentYear]);
            $years = AcademicYear::orderBy('year', 'asc')->get();
        }
        
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        return Inertia::render('Absensi/SelectYearPage', [
            'years' => $years->map(fn ($y) => ['nomor' => $y->year]),
            'selectedClass' => [
                'id' => $selectedKelas->id,
                'kelas' => $selectedKelas->nama_kelas,
                'jurusan' => $selectedKelas->jurusan->nama_jurusan,
            ],
        ]);
    }
    
    public function selectMonth($kelas, $jurusan, $tahun)
    {
        $months = collect(range(1, 12))->map(function ($month) use ($tahun) {
            $date = Carbon::create($tahun, $month, 1);
            return [
                'nama' => $date->translatedFormat('F'),
                'slug' => strtolower($date->translatedFormat('F')),
            ];
        });

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

        $date = Carbon::create($tahun, $monthNumber, 1);
        $daysInMonth = $date->daysInMonth;
        $namaBulan = $date->translatedFormat('F');
        $students = $selectedKelas->siswas;
        
        $absensiDays = Absensi::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $monthNumber)
            ->whereIn('siswa_id', $students->pluck('id'))
            ->distinct()
            ->pluck(DB::raw('DAY(tanggal)')); 
        
        $dbHolidays = Holiday::whereYear('date', $tahun)
            ->whereMonth('date', $monthNumber)
            ->get()
            ->pluck('date')
            ->map(fn($date) => Carbon::parse($date)->day);
        
        $days = collect();
        $firstDayOfMonth = Carbon::create($tahun, $monthNumber, 1);
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
        
        $realDays = collect(range(1, $daysInMonth))->map(function ($day) use ($tahun, $monthNumber) {
            $date = Carbon::create($tahun, $monthNumber, $day);
            return [
                'nomor' => $day,
                'nama_hari' => $date->translatedFormat('l'),
                'is_weekend' => $date->isWeekend(),
                'is_outside_month' => false,
            ];
        });

        $days = $days->merge($realDays);

        $lastDayOfMonth = Carbon::create($tahun, $monthNumber, $daysInMonth);
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
        if (!$monthNumber || !checkdate($monthNumber, (int) $tanggal, $tahun)) {
            abort(404);
        }
        
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $targetDate = Carbon::create($tahun, $monthNumber, $tanggal);
        $allStudents = $selectedKelas->siswas;
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
            'tahun' => $targetDate->year,
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
        $yearToAdd = $latestYear ? $latestYear->year + 1 : now()->year;
        AcademicYear::firstOrCreate(['year' => $yearToAdd]);
        
        $kelas = Kelas::with('jurusan')->findOrFail($request->kelas_id);

        return redirect()->route('absensi.class.show', [
            'kelas' => $kelas->nama_kelas,
            'jurusan' => $kelas->jurusan->nama_jurusan
        ])->with('success', 'Tahun ajaran berhasil ditambahkan!');
    }
    
    public function storeHoliday($kelas, $jurusan, $tahun, $bulanSlug, $tanggal)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $holidayDate = Carbon::create($tahun, $monthNumber, $tanggal)->toDateString();

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