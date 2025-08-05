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


     public function selectYear()
    {
        $years = AcademicYear::orderBy('year', 'asc')->get();

        if ($years->isEmpty()) {
            $currentYear = now()->year;
            AcademicYear::create(['year' => $currentYear]);
            $years = AcademicYear::orderBy('year', 'asc')->get();
        }

        return Inertia::render('Absensi/SelectYear', [
            'years' => $years->map(fn ($y) => ['nomor' => $y->year]),
        ]);
    }

     public function selectMonth($tahun)
    {
        $months = collect(range(1, 12))->map(function ($month) use ($tahun) {
            $date = Carbon::create($tahun, $month, 1);
            return [
                'nama' => $date->translatedFormat('F'),
                'slug' => strtolower($date->translatedFormat('F')),
                'tahun' => $tahun,
            ];
        });

        return Inertia::render('Absensi/SelectMonth', [
            'months' => $months,
            'tahun' => $tahun,
        ]);
    }

    public function showMonth($tahun, $bulanSlug)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        if (!$monthNumber) {
            abort(404);
        }

        $date = Carbon::create($tahun, $monthNumber, 1);
        $daysInMonth = $date->daysInMonth;
        $namaBulan = $date->translatedFormat('F');

        $days = collect(range(1, $daysInMonth))->map(function ($day) use ($tahun, $monthNumber) {
            return [
                'nomor' => $day,
                'nama_hari' => Carbon::create($tahun, $monthNumber, $day)->translatedFormat('l'),
            ];
        });
        
        $absensiDays = Absensi::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $monthNumber)
            ->distinct()
            ->pluck(DB::raw('DAY(tanggal)'));

        return Inertia::render('Absensi/SelectDay', [
            'tahun' => $tahun,
            'bulan' => $bulanSlug,
            'namaBulan' => $namaBulan,
            'days' => $days,
            'absensiDays' => $absensiDays,
        ]);
    }

    public function showDay($tahun, $bulanSlug, $tanggal)
    {
        $monthNumber = $this->getMonthNumberFromSlug($bulanSlug);
        if (!$monthNumber || !checkdate($monthNumber, $tanggal, $tahun)) {
            abort(404);
        }

        $targetDate = Carbon::create($tahun, $monthNumber, $tanggal);
        $allStudents = Siswa::all();
        $studentData = null;
        $tanggalAbsen = null;

        if ($allStudents->isNotEmpty()) {
            $firstStudent = $allStudents->first();
            $studentData = [
                'classCode' => $firstStudent->kelas,
                'major' => $firstStudent->jurusan,
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
        ]);
    }

 public function store(Request $request, $tahun, $bulanSlug, $tanggal)
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

    $existingAttendance = Absensi::where('tanggal', $targetDate)->exists();
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

    return redirect()->route('absensi.day.show', [$tahun, $bulanSlug, $tanggal])->with('success', 'Absensi berhasil disimpan!');
}

public function storeYear()
{
    $latestYear = AcademicYear::orderBy('year', 'desc')->first();
    $yearToAdd = $latestYear ? $latestYear->year + 1 : now()->year;
    AcademicYear::firstOrCreate(['year' => $yearToAdd]);

    return redirect()->route('absensi.index')->with('success', 'Tahun ajaran berhasil ditambahkan!');
}
}