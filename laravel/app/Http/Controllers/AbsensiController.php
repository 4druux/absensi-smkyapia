<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Absensi;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Holiday;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicYear;
use Illuminate\Validation\ValidationException;
use App\Exports\AbsensiExport;
use Maatwebsite\Excel\Facades\Excel;


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
        $classes = Siswa::select('kelas', 'jurusan')->distinct()->get();

        return Inertia::render('Absensi/SelectClass', [
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

        return Inertia::render('Absensi/SelectYear', [
            'years' => $years->map(fn ($y) => ['nomor' => $y->year]),
            'selectedClass' => ['kelas' => $kelas, 'jurusan' => $jurusan],
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

        return Inertia::render('Absensi/SelectMonth', [
            'months' => $months,
            'tahun' => $tahun,
            'selectedClass' => ['kelas' => $kelas, 'jurusan' => $jurusan],
        ]);
    }


    public function showMonth($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        if (!$monthNumber) {
            abort(404);
        }

        $date = Carbon::create($tahun, $monthNumber, 1);
        $daysInMonth = $date->daysInMonth;
        $namaBulan = $date->translatedFormat('F');

        $students = Siswa::where('kelas', $kelas)->where('jurusan', $jurusan)->get();

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

        $paddingDaysCount = $startDayOfWeek;
        if ($paddingDaysCount > 0) {
            $lastDayOfPrevMonth = $firstDayOfMonth->copy()->subDay();
            for ($i = $paddingDaysCount - 1; $i >= 0; $i--) {
                $prevDate = $lastDayOfPrevMonth->copy()->subDays($i);
                $days->push([
                    'nomor' => $prevDate->day,
                    'nama_hari' => $prevDate->translatedFormat('l'),
                    'is_weekend' => $prevDate->isWeekend(),
                    'is_outside_month' => true,
                ]);
            }
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

        return Inertia::render('Absensi/SelectDay', [
            'selectedClass' => ['kelas' => $kelas, 'jurusan' => $jurusan],
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

        $targetDate = Carbon::create($tahun, $monthNumber, $tanggal);
        $allStudents = Siswa::where('kelas', $kelas)->where('jurusan', $jurusan)->get();
        $studentData = null;
        $tanggalAbsen = null;

        if ($allStudents->isNotEmpty()) {
            $studentData = [
                'classCode' => $kelas,
                'major' => $jurusan,
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
            'selectedClass' => ['kelas' => $kelas, 'jurusan' => $jurusan],
        ]);
    }

    public function store(Request $request, $kelas, $jurusan, $tahun, $bulanSlug, $tanggal)
    {
        $request->validate([
            'attendance' => 'nullable|array',
            'attendance.*.siswa_id' => 'required|exists:siswas,id',
            'attendance.*.status' => 'required|string',
            'all_student_ids' => 'required|array',
            'all_student_ids.*' => 'exists:siswas,id',
        ]);
        
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $targetDate = Carbon::create($tahun, $monthNumber, $tanggal)->toDateString();

        $allStudents = Siswa::where('kelas', $kelas)->where('jurusan', $jurusan)->get();
        $existingAttendance = Absensi::where('tanggal', $targetDate)->whereIn('siswa_id', $allStudents->pluck('id'))->exists();
        if ($existingAttendance) {
            throw ValidationException::withMessages([
                'absensi' => 'Absensi untuk hari ini sudah dicatat dan tidak bisa diubah.',
            ]);
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

        return redirect()->route('absensi.day.show', ['kelas' => $kelas, 'jurusan' => $jurusan, 'tahun' => $tahun, 'bulanSlug' => $bulanSlug, 'tanggal' => $tanggal])->with('success', 'Absensi berhasil disimpan!');
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

        return redirect()->route('absensi.class.show', [
            'kelas' => $request->kelas,
            'jurusan' => $request->jurusan
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

    public function exportMonthExcel($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        $students = Siswa::where('kelas', $kelas)->where('jurusan', $jurusan)->pluck('id');
        $absensiCount = Absensi::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $monthNumber)
            ->whereIn('siswa_id', $students)
            ->count();
        
        if ($absensiCount === 0) {
            $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
            return response()->json(['error' => "Tidak ada data absensi untuk bulan {$namaBulan} {$tahun}."], 404);
        }

        $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
        $fileName = "Absensi-{$kelas}-{$jurusan}-{$namaBulan}-{$tahun}.xlsx";

        return Excel::download(new AbsensiExport($kelas, $jurusan, $tahun, $bulanSlug), $fileName);
    }

  public function exportMonthPdf($kelas, $jurusan, $tahun, $bulanSlug)
{
    $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
    if (!$monthNumber) {
        return response()->json(['error' => 'Bulan tidak valid.'], 404);
    }
    
    $students = Siswa::where('kelas', $kelas)->where('jurusan', $jurusan)->get();
    
    if ($students->isEmpty()) {
        return response()->json(['error' => "Tidak ada siswa di kelas {$kelas} {$jurusan}."], 404);
    }

    $absensiCount = Absensi::whereYear('tanggal', $tahun)
        ->whereMonth('tanggal', $monthNumber)
        ->whereIn('siswa_id', $students->pluck('id'))
        ->count();

    if ($absensiCount === 0) {
        $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
        return response()->json(['error' => "Tidak ada data absensi untuk bulan {$namaBulan} {$tahun}."], 404);
    }

    $daysInMonth = Carbon::createFromDate($tahun, $monthNumber)->daysInMonth;
    $absensiData = Absensi::whereIn('siswa_id', $students->pluck('id'))
        ->whereYear('tanggal', $tahun)
        ->whereMonth('tanggal', $monthNumber)
        ->get()
        ->mapWithKeys(function ($item) {
            return [
                $item->siswa_id . '_' . Carbon::parse($item->tanggal)->day => $item->status,
            ];
        });

    $namaBulan = Carbon::createFromDate($tahun, $monthNumber)->translatedFormat('F');
    $fileName = "Absensi-{$kelas}-{$jurusan}-{$namaBulan}-{$tahun}.pdf";

    $dbHolidays = Holiday::whereYear('date', $tahun)
        ->whereMonth('date', $monthNumber)
        ->pluck('date');
    $weekends = collect();
    $date = Carbon::create($tahun, $monthNumber, 1);
    for ($i = 0; $i < $daysInMonth; $i++) {
        if ($date->isWeekend()) {
            $weekends->push($date->format('Y-m-d'));
        }
        $date->addDay();
    }
    $allHolidays = $dbHolidays->merge($weekends)->unique()->map(fn($date) => Carbon::parse($date)->day);

    $logoPath = 'images/logo-smk.png'; 
    
    $pdf = Pdf::loadView('exports.absensi', compact('students', 'kelas', 'jurusan', 'namaBulan', 'tahun', 'daysInMonth', 'absensiData', 'allHolidays', 'monthNumber', 'logoPath'));
    
    return $pdf->download($fileName);
}
}