<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Siswa;
use App\Models\AcademicYear;
use App\Models\UangKasPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UangKasController extends Controller
{
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
        $monthNumber = array_search($bulanSlug, [
            'januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli',
            'agustus', 'september', 'oktober', 'november', 'desember',
        ]) + 1;

        $startDate = new \DateTime("$tahun-$monthNumber-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');

        $minggu = [];
        $currentDate = clone $startDate;
        $weekNumber = 1;

        while ($currentDate <= $endDate) {
            $startOfWeek = clone $currentDate;
            while ($startOfWeek->format('N') != 1) { // Cari hari Senin
                $startOfWeek->modify('-1 day');
            }
            $endOfWeek = clone $startOfWeek;
            $endOfWeek->modify('+6 days');

            $displayEndOfWeek = clone $endOfWeek;
            if ($displayEndOfWeek > $endDate) {
                $displayEndOfWeek = clone $endDate;
            }

            $displayStartOfWeek = clone $startOfWeek;
            if ($displayStartOfWeek < $startDate) {
                $displayStartOfWeek = clone $startDate;
            }

            if ($displayStartOfWeek <= $endDate && $displayEndOfWeek >= $startDate) {
                $minggu[] = [
                    'id' => $weekNumber,
                    'label' => "Minggu ke-$weekNumber",
                    'start_date' => $displayStartOfWeek->format('Y-m-d'),
                    'end_date' => $displayEndOfWeek->format('Y-m-d'),
                ];
            }
            
            $currentDate->modify('+7 days');
            $weekNumber++;
        }
        
        $siswaIds = Siswa::where('kelas', $kelas)->where('jurusan', $jurusan)->pluck('id');
        $totalSiswa = count($siswaIds);

        // Perbaikan: Ambil jumlah siswa yang sudah bayar per minggu
        $paidStudentsCountByWeek = UangKasPayment::whereIn('siswa_id', $siswaIds)
            ->where('tahun', $tahun)
            ->where('bulan_slug', $bulanSlug)
            ->where('status', 'paid')
            ->select('minggu', DB::raw('count(*) as paid_count'))
            ->groupBy('minggu')
            ->pluck('paid_count', 'minggu')
            ->toArray();

        // Perbaikan: Tentukan minggu yang sudah lunas
        $fullyPaidWeeks = collect($paidStudentsCountByWeek)
            ->filter(fn($count) => $count === $totalSiswa)
            ->keys()
            ->toArray();


        return Inertia::render('UangKas/SelectWeek', [
            'selectedClass' => (object) ['kelas' => $kelas, 'jurusan' => $jurusan],
            'tahun' => $tahun,
            'bulanSlug' => $bulanSlug,
            'namaBulan' => Carbon::createFromDate($tahun, $monthNumber, 1)->translatedFormat('F'),
            'minggu' => $minggu,
            'paidWeeks' => $fullyPaidWeeks, // Mengirimkan minggu yang sudah LUNAS
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

        // Hapus data pembayaran yang diubah menjadi 'unpaid'
        UangKasPayment::where('tahun', $tahun)
            ->where('bulan_slug', $bulanSlug)
            ->where('minggu', $minggu)
            ->whereNotIn('siswa_id', $paidSiswaIds)
            ->delete();

        // Simpan atau perbarui pembayaran yang 'paid'
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
            'year' => 'required|unique:academic_years,year',
        ]);

        AcademicYear::create([
            'year' => $request->input('year'),
        ]);

        return back()->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }
}