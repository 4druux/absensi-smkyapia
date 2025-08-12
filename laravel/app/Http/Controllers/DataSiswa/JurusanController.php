<?php

namespace App\Http\Controllers\DataSiswa;

use App\Models\Jurusan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;


class JurusanController extends Controller
{
    public function index()
    {
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        return response()->json($jurusans);
    }

    public function indexWeb()
{
    return Inertia::render('Jurusan/Index');
}

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_jurusan' => 'required|string|max:255|unique:jurusans',
            'singkatan' => 'nullable|string|max:20',
        ]);

        $jurusan = Jurusan::create($validatedData);
        return response()->json(['message' => 'Jurusan berhasil ditambahkan.', 'jurusan' => $jurusan], 201);
    }

    public function getKelasByJurusan(Jurusan $jurusan)
    {
        $kelas = $jurusan->kelas()->orderBy('nama_kelas')->orderBy('kelompok')->get();
        return response()->json($kelas);
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $validatedData = $request->validate([
            'nama_jurusan' => 'required|string|max:255|unique:jurusans,nama_jurusan,' . $jurusan->id,
            'singkatan' => 'nullable|string|max:20',
        ]);

        $jurusan->update($validatedData);
        return response()->json(['message' => 'Jurusan berhasil diperbarui.', 'jurusan' => $jurusan]);
    }

    public function destroy(Jurusan $jurusan)
    {
        $jurusan->delete();
        return response()->json(['message' => 'Jurusan berhasil dihapus.']);
    }
}