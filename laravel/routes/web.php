<?php

use App\Http\Controllers\DataSiswaController;
use App\Http\Controllers\AbsensiController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('HomePage');
});

Route::get('/data-siswa', [DataSiswaController::class, 'index'])->name('data-siswa.index');
Route::get('/data-siswa/input', [DataSiswaController::class, 'create'])->name('data-siswa.input');
Route::post('/data-siswa', [DataSiswaController::class, 'store'])->name('data-siswa.store');

Route::get('/data-siswa/{kelas}/{jurusan}', [DataSiswaController::class, 'showClass'])->name('data-siswa.class.show');
Route::put('/data-siswa/siswa/{id}', [DataSiswaController::class, 'updateStudent'])->name('data-siswa.student.update');

Route::delete('/data-siswa/siswa/{id}', [DataSiswaController::class, 'destroyStudent'])->name('data-siswa.student.destroy');
Route::delete('/data-siswa/{kelas}/{jurusan}', [DataSiswaController::class, 'destroyClass'])->name('data-siswa.class.destroy');


Route::get('/absensi', [AbsensiController::class, 'selectClass'])->name('absensi.index');
Route::get('/absensi/{kelas}/{jurusan}', [AbsensiController::class, 'selectYear'])->name('absensi.class.show');
Route::get('/absensi/{kelas}/{jurusan}/{tahun}', [AbsensiController::class, 'selectMonth'])->name('absensi.year.show');
Route::get('/absensi/{kelas}/{jurusan}/{tahun}/{bulanSlug}', [AbsensiController::class, 'showMonth'])->name('absensi.month.show');
Route::get('/absensi/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{tanggal}', [AbsensiController::class, 'showDay'])->name('absensi.day.show');
Route::post('/absensi/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{tanggal}', [AbsensiController::class, 'store'])->name('absensi.day.store');
Route::post('/absensi/year', [AbsensiController::class, 'storeYear'])->name('absensi.year.store');