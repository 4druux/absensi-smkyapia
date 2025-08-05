<?php

use App\Http\Controllers\DataSiswaController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\UangKasController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('HomePage');
})->name('home');

// Data Siswa
Route::controller(DataSiswaController::class)->prefix('data-siswa')->name('data-siswa.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/input', 'create')->name('input');
    Route::post('/', 'store')->name('store'); 
    Route::get('/{kelas}/{jurusan}', 'showClass')->name('class.show'); 
    Route::put('/siswa/{id}', 'updateStudent')->name('student.update');
    Route::delete('/siswa/{id}', 'destroyStudent')->name('student.destroy');
    Route::delete('/{kelas}/{jurusan}', 'destroyClass')->name('class.destroy');
});

// Absensi
Route::controller(AbsensiController::class)->prefix('absensi')->name('absensi.')->group(function () {
    Route::get('/', 'selectClass')->name('index');
    Route::get('/{kelas}/{jurusan}', 'selectYear')->name('class.show');
    Route::get('/{kelas}/{jurusan}/{tahun}', 'selectMonth')->name('year.show');
    Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}', 'showMonth')->name('month.show');
    Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{tanggal}', 'showDay')->name('day.show');
    Route::post('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{tanggal}', 'store')->name('day.store');
    Route::post('/year', 'storeYear')->name('year.store'); 
});


// Kas
Route::controller(UangKasController::class)->prefix('uang-kas')->name('uang-kas.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/kelas/{kelas}/{jurusan}', 'showClass')->name('class.show');
    Route::get('/kelas/{kelas}/{jurusan}/{tahun}', 'showYear')->name('year.show');
    Route::get('/kelas/{kelas}/{jurusan}/{tahun}/{bulanSlug}', 'showMonth')->name('month.show');
    Route::get('/kelas/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{minggu}', 'showWeek')->name('week.show');
    Route::post('/kelas/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{minggu}', 'store')->name('week.store');
    Route::post('/year', 'storeYear')->name('year.store');
});