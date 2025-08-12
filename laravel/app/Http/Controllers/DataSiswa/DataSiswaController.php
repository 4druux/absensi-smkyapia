<?php

namespace App\Http\Controllers\DataSiswa;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;


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
    DB::beginTransaction();
    try {
        $validatedData = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'students' => 'required|array|min:1',
            'students.*.nama' => 'required|string|max:255',
            'students.*.nis' => 'required|string|max:20',
        ]);

        $nises = collect($validatedData['students'])->pluck('nis')->map(fn($nis) => trim($nis));

        $existingNisInClass = Siswa::where('kelas_id', $validatedData['kelas_id'])
                                    ->whereIn('nis', $nises)
                                    ->pluck('nis');

        if ($existingNisInClass->isNotEmpty()) {
            throw ValidationException::withMessages([
                'students' => ['Siswa sudah terdaftar dalam kelas ini.']
            ]);
        }

        foreach ($validatedData['students'] as $studentData) {
            Siswa::create([
                'nama' => trim($studentData['nama']),
                'nis' => trim($studentData['nis']),
                'kelas_id' => $validatedData['kelas_id'],
            ]);
        }

        DB::commit();

        return response()->json(['message' => 'Data siswa berhasil disimpan!'], 201);

    } catch (ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'message' => $e->errors()['students'][0] ?? 'Terdapat kesalahan input pada data siswa.',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Gagal menyimpan data siswa via API: ' . $e->getMessage());
        return response()->json(['message' => 'Terjadi kesalahan pada server.'], 500);
    }
}

    public function updateStudentApi(Request $request, Siswa $siswa)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'nis' => ['required', 'string', 'max:20', Rule::unique('siswas')->where('kelas_id', $siswa->kelas_id)->ignore($siswa->id)],
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