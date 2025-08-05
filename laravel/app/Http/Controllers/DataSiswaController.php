<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log; // <-- Tambahkan baris ini


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

        // Validasi duplikasi NIS dalam satu form yang sama
        $allNisInForm = collect($request->students)->pluck('nis')->map(fn($nis) => trim($nis));
        if ($allNisInForm->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'students' => 'Terdapat duplikasi NIS dalam form. Mohon periksa kembali.',
            ]);
        }
        
        // Cek duplikasi siswa yang sudah ada di kelas yang sama
        $duplicates = Siswa::where('kelas', $kelas)
            ->where('jurusan', $jurusan)
            ->whereIn('nis', $allNisInForm)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $duplicateNis = $duplicates->pluck('nis')->implode(', ');
            throw ValidationException::withMessages([
                'students' => "NIS ({$duplicateNis}) sudah terdaftar di kelas {$kelas} - {$jurusan} ini.",
            ]);
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
    $deletedCount = Siswa::where('kelas', $kelas)->where('jurusan', $jurusan)->delete();

    if ($deletedCount > 0) {
        Log::info("Kelas {$kelas} - {$jurusan} berhasil dihapus. Total siswa dihapus: {$deletedCount}");
    } else {
        Log::warning("Gagal menghapus kelas {$kelas} - {$jurusan}. Data tidak ditemukan.");
    }

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
    try {
        $student = Siswa::findOrFail($id);
        $kelas = $student->kelas;
        $jurusan = $student->jurusan;

        // Hapus siswa
        $deleted = $student->delete();

        // Log untuk debugging
        if ($deleted) {
            Log::info("Siswa dengan ID {$id} berhasil dihapus.");
        } else {
            Log::warning("Gagal menghapus siswa dengan ID {$id}. Operasi delete gagal.");
        }

        return Redirect::route('data-siswa.class.show', [
            'kelas' => $kelas,
            'jurusan' => $jurusan,
        ])->with('success', 'Data siswa berhasil dihapus!');
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::warning("Siswa dengan ID {$id} tidak ditemukan.");
        return Redirect::back()->with('error', 'Siswa tidak ditemukan.');
    }
}

}