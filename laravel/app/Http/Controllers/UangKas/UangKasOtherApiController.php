<?php

namespace App\Http\Controllers\UangKas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Iuran;
use App\Models\UangKasOther;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


class UangKasOtherApiController extends Controller
{
    public function getOtherCash($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];
        $monthNumber = $monthMap[strtolower($bulanSlug)] ?? null;

        if (!$monthNumber) {
            return response()->json([], 404);
        }

        [$startYear, $endYear] = explode('-', $tahun);
        $year = $monthNumber >= 7 ? $startYear : $endYear;

        $selectedKelas = Kelas::whereHas('jurusan', fn($query) => $query->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $iurans = Iuran::where('kelas_id', $selectedKelas->id)
                      ->whereHas('payments', function ($query) use ($year, $monthNumber) {
                          $query->whereYear('tanggal', $year)->whereMonth('tanggal', $monthNumber);
                      })
                      ->with('payments')
                      ->get();
                      
        $totalSiswa = $selectedKelas->siswas()->count();

        $otherCashCards = $iurans->map(function ($iuran) use ($totalSiswa) {
            if ($totalSiswa == 0) {
                $isPaid = false;
            } else {
                $paidCount = $iuran->payments->where('status', 'paid')->count();
                $isPaid = $paidCount >= $totalSiswa;
            }
            
            $firstPayment = $iuran->payments->first();

            return [
                'id' => 'other-' . $iuran->id,
                'type' => 'other',
                'other_id' => $iuran->id,
                'label' => $iuran->deskripsi,
                'display_date' => $firstPayment->tanggal->format('d-m-Y'),
                'display_date_range' => null,
                'is_paid' => $isPaid,
                'is_holiday' => false,
            ];
        });

        return response()->json($otherCashCards);
    }

    public function store(Request $request, $kelas, $jurusan, $displayYear, $bulanSlug)
{
    $validated = $request->validate([
        'deskripsi' => 'required|string|max:255',
        'tanggal' => 'required|date',
    ]);

    $monthMap = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
        'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
    ];
    $monthNumber = $monthMap[strtolower($bulanSlug)] ?? null;
    $namaBulan = Carbon::createFromDate(null, $monthNumber, 1)->translatedFormat('F');
    $submittedDate = Carbon::parse($validated['tanggal']);

    if ($submittedDate->year != $displayYear || $submittedDate->month !== $monthNumber) {
        throw ValidationException::withMessages([
           'tanggal' => "Tanggal iuran harus berada di dalam bulan {$namaBulan} {$displayYear}.",
        ]);
    }

    $selectedKelas = Kelas::whereHas('jurusan', function ($query) use ($jurusan) {
        $query->where('nama_jurusan', $jurusan);
    })->where('nama_kelas', $kelas)->firstOrFail();

    $students = $selectedKelas->siswas;

    if ($students->isEmpty()) {
        return response()->json(['message' => 'Tidak ada siswa di kelas ini.'], 400);
    }

    DB::transaction(function () use ($students, $validated, $selectedKelas) {
        $newIuran = Iuran::create([
            'kelas_id' => $selectedKelas->id,
            'deskripsi' => $validated['deskripsi'],
        ]);

        foreach ($students as $student) {
            UangKasOther::create([
                'iuran_id' => $newIuran->id,
                'siswa_id' => $student->id,
                'tanggal' => $validated['tanggal'],
                'nominal' => 0,
                'status' => 'unpaid',
            ]);
        }
    });

    return response()->json(['message' => 'Iuran baru berhasil dibuat.'], 201);
}

public function getPayments(Request $request, $kelas, $jurusan, Iuran $iuran)
{
    $selectedKelas = $iuran->kelas;
    $students = $selectedKelas->siswas;

    $existingPayments = $iuran->payments->mapWithKeys(function ($item) {
        return [$item->siswa_id => ['nominal' => $item->nominal, 'status' => $item->status]];
    });

    return response()->json([
        'students' => $students,
        'existingPayments' => $existingPayments,
        'iuran' => $iuran,
    ]);
}

public function storePayments(Request $request, $kelas, $jurusan, Iuran $iuran)
{
    $validated = $request->validate([
        'fixed_nominal' => 'required|numeric|min:0',
        'payments' => 'required|array',
        'payments.*.siswa_id' => 'required|exists:siswas,id',
        'payments.*.status' => 'required|in:paid,unpaid',
    ]);

    DB::transaction(function () use ($validated, $iuran) {
        foreach ($validated['payments'] as $payment) {
            UangKasOther::where('iuran_id', $iuran->id)
                ->where('siswa_id', $payment['siswa_id'])
                ->update([
                    'nominal' => $validated['fixed_nominal'],
                    'status' => $payment['status'],
                    'paid_at' => $payment['status'] === 'paid' ? now() : null,
                ]);
        }
    });

    return response()->json(['message' => 'Pembayaran iuran berhasil diperbarui!']);
}
}