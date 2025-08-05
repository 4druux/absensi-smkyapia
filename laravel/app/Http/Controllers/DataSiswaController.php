<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;


class DataSiswaController extends Controller
{
    public function index()
    {
        $classes = Siswa::select('kelas', 'jurusan')->distinct()->get();

        return Inertia::render('DataSiswa/AllClasses', [
            'classes' => $classes,
        ]);
    }

    public function showClass($kelas, $jurusan)
    {
        $students = Siswa::where('kelas', $kelas)->where('jurusan', $jurusan)->get();

        return Inertia::render('DataSiswa/ShowClass', [
            'selectedClass' => [
                'kelas' => $kelas,
                'jurusan' => $jurusan
            ],
            'students' => $students,
        ]);
    }
    
    public function create()
    {
        return Inertia::render('DataSiswa/InputData');
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

    public function destroyClass($kelas, $jurusan)
{
    Siswa::where('kelas', $kelas)->where('jurusan', $jurusan)->delete();

    return redirect()->route('data-siswa.index')->with('success', "Kelas {$kelas} - {$jurusan} beserta semua siswanya berhasil dihapus!");
}

    public function updateStudent(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nis' => 'required|string|max:20|unique:siswas,nis,'.$id,
            'kelas' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
        ]);

         $student = Siswa::findOrFail($id);
    $student->update($request->all());

    return Redirect::route('data-siswa.class.show', [
        'kelas' => $student->kelas,
        'jurusan' => $student->jurusan,
    ])->with('success', 'Data siswa berhasil diperbarui!');
}

public function destroyStudent($id)
{
    $student = Siswa::findOrFail($id);
    $kelas = $student->kelas;
    $jurusan = $student->jurusan;
    
    Siswa::destroy($id);

    return Redirect::route('data-siswa.class.show', [
        'kelas' => $kelas,
        'jurusan' => $jurusan,
    ])->with('success', 'Data siswa berhasil dihapus!');
}

}