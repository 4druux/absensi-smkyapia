<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DataSiswaController extends Controller
{
    public function index()
    {
        return Inertia::render('DataSiswa/AllClasses');
    }

    public function showClass(Kelas $kelas)
    {
        $kelas->load(['jurusan', 'siswas' => function ($query) {
            $query->orderBy('nama');
        }]);
        
        return Inertia::render('DataSiswa/ShowClass', [
            'selectedClass' => $kelas,
        ]);
    }

    
    public function create()
    {
        return Inertia::render('DataSiswa/InputData');
    }

    public function storeApi(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'kelas_id' => 'required|exists:kelas,id',
                'students' => 'required|array|min:1',
                'students.*.nama' => 'required|string|max:255',
                'students.*.nis' => 'required|string|max:20',
            ]);

            $allNisInForm = collect($validatedData['students'])->pluck('nis')->map(fn($nis) => trim($nis));

            $duplicates = Siswa::withTrashed()
                ->where('kelas_id', $validatedData['kelas_id'])
                ->whereIn('nis', $allNisInForm)
                ->get();

            if ($duplicates->isNotEmpty()) {
                $errorMessage = 'Ada NIS yang sudah terdaftar di kelas ini. Mohon periksa kembali.';
                return response()->json([
                    'message' => $errorMessage,
                    'errors' => ['students' => [$errorMessage]]
                ], 409);
            }

            foreach ($validatedData['students'] as $studentData) {
                Siswa::create([
                    'nama' => trim($studentData['nama']),
                    'nis' => trim($studentData['nis']),
                    'kelas_id' => $validatedData['kelas_id'],
                ]);
            }

            return response()->json(['message' => 'Data siswa berhasil disimpan!'], 201);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Terdapat kesalahan input, periksa kembali data Anda.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan data siswa via API: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan pada server.'], 500);
        }
    }

    public function updateStudentApi(Request $request, Siswa $siswa)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'nis' => ['required', 'string', 'max:20', Rule::unique('siswas')->ignore($siswa->id)],
        ]);

        $siswa->update($validatedData);
        return response()->json(['message' => 'Data siswa berhasil diperbarui!', 'student' => $siswa]);
    }

    public function destroyStudentApi(Siswa $siswa)
    {
        $siswa->delete();
        return response()->json(['message' => 'Siswa berhasil dihapus.']);
    }
}