<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Absensi;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicYear;
use Illuminate\Validation\ValidationException;


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

        $days = collect(range(1, $daysInMonth))->map(function ($day) use ($tahun, $monthNumber) {
            return [
                'nomor' => $day,
                'nama_hari' => Carbon::create($tahun, $monthNumber, $day)->translatedFormat('l'),
            ];
        });

        return Inertia::render('Absensi/SelectDay', [
            'selectedClass' => ['kelas' => $kelas, 'jurusan' => $jurusan],
            'tahun' => $tahun,
            'bulan' => $bulanSlug,
            'namaBulan' => $namaBulan,
            'days' => $days,
            'absensiDays' => $absensiDays,
        ]);
    }

    public function showDay($kelas, $jurusan, $tahun, $bulanSlug, $tanggal)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        if (!$monthNumber || !checkdate($monthNumber, $tanggal, $tahun)) {
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
                                ->translatedFormat('l, d F Y — H:i:s');
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
}