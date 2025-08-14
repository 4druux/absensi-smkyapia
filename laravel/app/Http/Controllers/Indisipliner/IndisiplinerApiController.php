<?php

namespace App\Http\Controllers\Indisipliner;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Indisipliner;
use App\Models\Kelas;
use App\Models\IndisiplinerDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IndisiplinerApiController extends Controller
{
    public function getClasses()
    {
        $classes = Kelas::whereHas('siswas')->with('jurusan')->get();
        return response()->json($classes->map(fn($c) => ['id' => $c->id, 'kelas' => $c->nama_kelas, 'jurusan' => $c->jurusan->nama_jurusan, 'kelompok' => $c->kelompok,]));
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
    
    public function storeYearApi(Request $request)
    {
        $latestYear = AcademicYear::orderBy('year', 'desc')->first();
        $yearToCreate = $latestYear ? $latestYear->year + 1 : (now()->month >= 7 ? now()->year : now()->year - 1);
        $academicYear = AcademicYear::firstOrCreate(['year' => $yearToCreate]);
        return response()->json(['message' => 'Tahun ajaran berhasil ditambahkan!', 'year' => $academicYear], 201);
    }

    public function getIndisiplinerData(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tahun' => 'required|string',
        ]);

        $kelasId = $request->kelas_id;
        $tahun = $request->tahun;

        $indisiplinerData = Indisipliner::with(['siswa' => fn($query) => $query->select('id', 'nama', 'nis'), 'details'])
            ->where('tahun', $tahun)
            ->whereHas('siswa', fn($query) => $query->where('kelas_id', $kelasId))
            ->orderBy('tanggal_surat', 'desc')
            ->get();

        return response()->json($indisiplinerData);
    }

    public function storeIndisiplinerData(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'kelas_id' => 'required|exists:kelas,id',
            'tahun' => 'required|string',
            'jenis_surat' => 'nullable|string',
            'nomor_surat' => 'nullable|string',
            'tanggal_surat' => 'nullable|date',
            
            'terlambat_alasan' => 'nullable|string',
            'terlambat_poin' => 'nullable|integer',
            'alfa_alasan' => 'nullable|string',
            'alfa_poin' => 'nullable|integer',
            'bolos_alasan' => 'nullable|string',
            'bolos_poin' => 'nullable|integer',
            
            'details' => 'nullable|array',
            'details.*.jenis_pelanggaran' => 'nullable|string', 
            'details.*.alasan' => 'nullable|string',
            'details.*.poin' => 'nullable|integer',
        ], [
            'siswa_id.required' => 'Kolom siswa wajib diisi.',
            'details.*.jenis_pelanggaran.required_with' => 'Jenis pelanggaran tidak boleh kosong.',
            'details.*.poin.required_with' => 'Poin pelanggaran tidak boleh kosong.',
            'details.*.poin.integer' => 'Poin pelanggaran harus berupa angka.',
        ]);

        DB::beginTransaction();

        try {
            $indisipliner = Indisipliner::create([
                'siswa_id' => $validated['siswa_id'],
                'tahun' => $validated['tahun'],
                'jenis_surat' => $validated['jenis_surat'] ?? null,
                'nomor_surat' => $validated['nomor_surat'] ?? null,
                'tanggal_surat' => $validated['tanggal_surat'] ?? null,
            ]);

            $allDetails = [];

            if (!empty($validated['terlambat_alasan']) || !empty($validated['terlambat_poin'])) {
                $allDetails[] = [
                    'jenis_pelanggaran' => 'Terlambat',
                    'alasan' => $validated['terlambat_alasan'] ?? null,
                    'poin' => $validated['terlambat_poin'] ?? 0,
                ];
            }

            if (!empty($validated['alfa_alasan']) || !empty($validated['alfa_poin'])) {
                $allDetails[] = [
                    'jenis_pelanggaran' => 'Alfa',
                    'alasan' => $validated['alfa_alasan'] ?? null,
                    'poin' => $validated['alfa_poin'] ?? 0,
                ];
            }

            if (!empty($validated['bolos_alasan']) || !empty($validated['bolos_poin'])) {
                $allDetails[] = [
                    'jenis_pelanggaran' => 'Bolos',
                    'alasan' => $validated['bolos_alasan'] ?? null,
                    'poin' => $validated['bolos_poin'] ?? 0,
                ];
            }
            
            if (isset($validated['details'])) {
                foreach ($validated['details'] as $detail) {
                    if (!empty($detail['jenis_pelanggaran']) && !empty($detail['poin'])) {
                        $allDetails[] = [
                            'jenis_pelanggaran' => $detail['jenis_pelanggaran'],
                            'alasan' => $detail['alasan'] ?? null,
                            'poin' => $detail['poin'] ?? 0,
                        ];
                    }
                }
            }
            
            if (empty($allDetails)) {
                DB::rollBack();
                return response()->json(['error' => 'Harus ada setidaknya satu pelanggaran yang diisi.'], 422);
            }

            $indisipliner->details()->createMany($allDetails);

            DB::commit();

            return response()->json(['message' => 'Data indisipliner berhasil disimpan!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saat menyimpan data indisipliner: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menyimpan data indisipliner. Silakan coba lagi.'], 500);
        }
    }
    
    public function deleteIndisiplinerData($id)
    {
        $indisipliner = Indisipliner::findOrFail($id);
        $indisipliner->delete();
        
        return response()->json(['message' => 'Data indisipliner berhasil dihapus.']);
    }
}