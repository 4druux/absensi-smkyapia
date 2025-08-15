<?php

namespace App\Http\Controllers\UangKas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PengeluaranApiController extends Controller
{
    public function index($kelas, $jurusan, $tahun, $bulanSlug)
    {
        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];
        $monthNumber = $monthMap[strtolower($bulanSlug)] ?? null;

        if (!$monthNumber) {
            return response()->json(['message' => 'Bulan tidak valid'], 404);
        }

        [$startYear, $endYear] = explode('-', $tahun);
        $year = $monthNumber >= 7 ? $startYear : $endYear;

        $selectedKelas = Kelas::whereHas('jurusan', fn($q) => $q->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        $pengeluarans = Pengeluaran::where('kelas_id', $selectedKelas->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $monthNumber)
            ->latest()
            ->get();

        $totalPengeluaran = $pengeluarans->where('status', 'approved')->sum('nominal');

        $formattedPengeluarans = $pengeluarans->map(function ($item) {
        return [
            'id' => $item->id,
            'tanggal' => $item->tanggal->format('Y-m-d'), 
            'deskripsi' => $item->deskripsi,
            'nominal' => $item->nominal,
            'status' => $item->status,
            'tanggal_formatted' => $item->tanggal->translatedFormat('d F Y'), 
            ];
        });

       return response()->json([
            'pengeluarans' => $formattedPengeluarans,
            'total_pengeluaran' => $totalPengeluaran,
        ]);
    }

    public function store(Request $request, $kelas, $jurusan, $displayYear, $bulanSlug)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'deskripsi' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:1',
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
               'tanggal' => "Tanggal pengeluaran harus berada di dalam bulan {$namaBulan} {$displayYear}.",
            ]);
        }

        $selectedKelas = Kelas::whereHas('jurusan', fn($q) => $q->where('nama_jurusan', $jurusan))
            ->where('nama_kelas', $kelas)->firstOrFail();

        Pengeluaran::create([
            'kelas_id' => $selectedKelas->id,
            'tanggal' => $validated['tanggal'],
            'deskripsi' => $validated['deskripsi'],
            'nominal' => $validated['nominal'],
        ]);

        return response()->json(['message' => 'Pengajuan pengeluaran berhasil dibuat.'], 201);
    }
    
    public function approve($id)
    {
        $pengeluaran = Pengeluaran::findOrFail($id);
        $pengeluaran->update(['status' => 'approved']);
        return response()->json(['message' => 'Pengeluaran berhasil disetujui.'], 200);
    }

    public function reject($id)
    {
        $pengeluaran = Pengeluaran::findOrFail($id);
        $pengeluaran->update(['status' => 'rejected']);
        return response()->json(['message' => 'Pengeluaran berhasil ditolak.'], 200);
    }
}