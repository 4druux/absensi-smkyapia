<?php

namespace App\Http\Controllers\Absensi;

use App\Models\Absensi;
use App\Models\AcademicYear;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class AbsensiApiController extends Controller
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
            $currentYear = now()->year;
            AcademicYear::create(['year' => $currentYear]);
            $years = AcademicYear::orderBy('year', 'asc')->get();
        }
        
        return response()->json($years->map(fn ($y) => ['nomor' => $y->year]));
    }

    public function storeYearApi(Request $request)
    {
        $latestYear = AcademicYear::orderBy('year', 'desc')->first();
        $yearToAdd = $latestYear ? $latestYear->year + 1 : now()->year;
        
        $academicYear = AcademicYear::firstOrCreate(['year' => $yearToAdd]);
        
        return response()->json(['message' => 'Tahun ajaran berhasil ditambahkan!', 'year' => $academicYear], 201);
    }

    public function getMonths($kelas, $jurusan, $tahun)
    {
        $months = collect(range(1, 12))->map(function ($month) use ($tahun) {
            $date = Carbon::create($tahun, $month, 1);
            return [
                'nama' => $date->translatedFormat('F'),
                'slug' => strtolower($date->translatedFormat('F')),
            ];
        });
        return response()->json($months);
    }
    
    public function getDays($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        if (!$monthNumber) {
            return response()->json(['error' => 'Bulan tidak valid.'], 404);
        }

        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $date = Carbon::create($tahun, $monthNumber, 1);
        $daysInMonth = $date->daysInMonth;

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
                            
                            
        return response()->json([
            'days' => $days,
            'absensiDays' => $absensiDays,
            'holidays' => $allHolidays,
        ]);
    }
    
    public function getAttendance($kelas, $jurusan, $tahun, $bulanSlug, $tanggal)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        if (!$monthNumber || !checkdate($monthNumber, (int) $tanggal, $tahun)) {
            return response()->json(['error' => 'Tanggal tidak valid.'], 404);
        }

        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $targetDate = Carbon::create($tahun, $monthNumber, $tanggal);
        $allStudents = $selectedKelas->siswas;

        if ($allStudents->isEmpty()) {
            return response()->json(['error' => "Tidak ada siswa di kelas ini."], 404);
        }

        $existingAttendance = Absensi::whereDate('tanggal', $targetDate->toDateString())
            ->whereIn('siswa_id', $allStudents->pluck('id'))
            ->get();
        
        $tanggalAbsen = null;
        if ($existingAttendance->isNotEmpty()) {
            $tanggalAbsen = Carbon::parse($existingAttendance->first()->updated_at)
                ->setTimezone('Asia/Jakarta')
                ->translatedFormat('d-m-Y H:i:s');
        }

        return response()->json([
            'students' => $allStudents,
            'tanggalAbsen' => $tanggalAbsen,
            'existingAttendance' => $existingAttendance->pluck('status', 'siswa_id'),
        ]);
    }

    public function storeAttendance(Request $request, $kelas, $jurusan, $tahun, $bulanSlug, $tanggal)
    {
        $request->validate([
            'attendance' => 'nullable|array',
            'attendance.*.siswa_id' => 'required|exists:siswas,id',
            'attendance.*.status' => 'required|string',
            'all_student_ids' => 'required|array',
            'all_student_ids.*' => 'exists:siswas,id',
        ]);
        
        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $targetDate = Carbon::create($tahun, $monthNumber, $tanggal)->toDateString();

        $allStudents = $selectedKelas->siswas;
        $existingAttendance = Absensi::where('tanggal', $targetDate)->whereIn('siswa_id', $allStudents->pluck('id'))->exists();
        if ($existingAttendance) {
            return response()->json([
                'message' => 'Absensi untuk hari ini sudah dicatat dan tidak bisa diubah.'
            ], 409);
        }

        $allStudentIdsOnPage = collect($request->all_student_ids);
        $notPresentData = collect($request->attendance ?? []);
        $notPresentIds = $notPresentData->pluck('siswa_id');
        $presentIds = $allStudentIdsOnPage->diff($notPresentIds);

        DB::transaction(function () use ($notPresentData, $presentIds, $targetDate) {
            foreach ($notPresentData as $data) {
                Absensi::create([
                    'siswa_id' => $data['siswa_id'],
                    'tanggal' => $targetDate,
                    'status' => $data['status'],
                ]);
            }
            foreach ($presentIds as $studentId) {
                Absensi::create([
                    'siswa_id' => $studentId,
                    'tanggal' => $targetDate,
                    'status' => 'hadir',
                ]);
            }
        });

        return response()->json(['message' => 'Absensi berhasil disimpan!'], 201);
    }
    
    public function storeHolidayApi($kelas, $jurusan, $tahun, $bulanSlug, $tanggal)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $holidayDate = Carbon::create($tahun, $monthNumber, $tanggal)->toDateString();

        $existingHoliday = Holiday::where('date', $holidayDate)->exists();
        if ($existingHoliday) {
            return response()->json(['message' => 'Tanggal ini sudah terdaftar sebagai hari libur.'], 409);
        }

        Holiday::create([
            'date' => $holidayDate,
            'description' => 'Hari Libur Tambahan',
        ]);
        
        return response()->json(['message' => 'Tanggal berhasil ditetapkan sebagai hari libur.'], 201);
    }
}