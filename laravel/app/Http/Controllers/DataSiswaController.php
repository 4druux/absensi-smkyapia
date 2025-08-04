<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DataSiswaController extends Controller
{
    public function index()
    {
        return Inertia::render('DataSiswaPage', [
            'students' => Siswa::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'students' => 'required|array|min:1',
            'students.*.nama' => 'required|string|max:255',
            'students.*.nis' => 'required|string|max:20',
        ]);

        $kelas = trim($request->kelas);
        $jurusan = trim($request->jurusan);
        $allNis = collect($request->students)->pluck('nis')->map(fn($nis) => trim($nis));

        if ($allNis->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'students' => 'Terdapat duplikasi NIS dalam form. Mohon periksa kembali.',
            ]);
        }

        foreach ($request->students as $index => $studentData) {
            $namaSiswa = trim($studentData['nama']);
            $nisSiswa = trim($studentData['nis']);

            if (Siswa::where('nis', $nisSiswa)->exists()) {
                 throw ValidationException::withMessages([
                    "students.{$index}.nis" => "NIS '{$nisSiswa}' sudah terdaftar.",
                ]);
            }

            $isDuplicate = Siswa::where('nama', $namaSiswa)
                ->where('nis', $nisSiswa)
                ->where('kelas', $kelas)
                ->where('jurusan', $jurusan)
                ->exists();

            if ($isDuplicate) {
                throw ValidationException::withMessages([
                    'students' => "Data untuk siswa '{$namaSiswa}' ({$nisSiswa}) di kelas & jurusan ini sudah ada.",
                ]);
            }
        }
        
        foreach ($request->students as $studentData) {
            Siswa::create([
                'nama' => trim($studentData['nama']),
                'nis' => trim($studentData['nis']),
                'kelas' => $kelas,
                'jurusan' => $jurusan,
            ]);
        }

        return redirect()->route('data-siswa.index')->with('success', 'Data siswa berhasil disimpan!');
    }
}