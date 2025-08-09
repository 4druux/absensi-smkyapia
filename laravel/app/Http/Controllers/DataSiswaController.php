<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

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
                'students.*.nis' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('siswas', 'nis')->where(function ($query) use ($request) {
                        return $query->where('kelas_id', $request->kelas_id);
                    }),
                ],
            ]);

            DB::transaction(function () use ($validatedData) {
                foreach ($validatedData['students'] as $studentData) {
                    Siswa::create([
                        'nama' => trim($studentData['nama']),
                        'nis' => trim($studentData['nis']),
                        'kelas_id' => $validatedData['kelas_id'],
                    ]);
                }
            });

            return response()->json(['message' => 'Data siswa berhasil disimpan!'], 201);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $nisError = collect($errors)->filter(fn ($messages, $key) => str_contains($key, 'nis'))->isNotEmpty();

            if ($nisError) {
                return response()->json([
                    'message' => 'Terdapat NIS yang sudah terdaftar di kelas ini. Mohon periksa kembali data siswa Anda.',
                    'errors' => ['students' => ['Terdapat NIS yang sudah terdaftar di kelas ini.']],
                ], 422);
            }

            return response()->json(['message' => 'Terdapat kesalahan input, periksa kembali data Anda.', 'errors' => $errors], 422);
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
        $siswa->forceDelete();

        return response()->json(['message' => 'Siswa berhasil dihapus secara permanen.']);
    }
}