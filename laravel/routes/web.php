<?php

use App\Http\Controllers\DataSiswaController;
use App\Http\Controllers\AbsensiController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('HomePage');
});

Route::get('/data-siswa', [DataSiswaController::class, 'index'])->name('data-siswa.index');
Route::post('/data-siswa', [DataSiswaController::class, 'store'])->name('data-siswa.store');


Route::get('/absensi', [AbsensiController::class, 'selectYear'])->name('absensi.index'); 
Route::get('/absensi/{tahun}', [AbsensiController::class, 'selectMonth'])->name('absensi.year.show'); 
Route::get('/absensi/{tahun}/{bulan}', [AbsensiController::class, 'showMonth'])->name('absensi.month.show');
Route::get('/absensi/{tahun}/{bulan}/{tanggal}', [AbsensiController::class, 'showDay'])->name('absensi.day.show');
Route::post('/absensi/{tahun}/{bulan}/{tanggal}', [AbsensiController::class, 'store'])->name('absensi.day.store');
Route::post('/absensi/year', [AbsensiController::class, 'storeYear'])->name('absensi.year.store');
Route::get('/absensi/{tahun}', [AbsensiController::class, 'selectMonth'])->name('absensi.year.show');
