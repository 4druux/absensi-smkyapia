<?php

use App\Http\Controllers\DataSiswaController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\Absensi\AbsensiController; 
use App\Http\Controllers\Absensi\AbsensiExportController; 
use App\Http\Controllers\UangKas\UangKasController; 
use App\Http\Controllers\UangKas\UangKasExportController; 
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('HomePage');
})->name('home');

// Data Siswa
Route::controller(DataSiswaController::class)->prefix('data-siswa')->name('data-siswa.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/input', 'create')->name('input');
    Route::get('/kelas/{kelas}', 'showClass')->name('class.show');
});

Route::get('/jurusan', [JurusanController::class, 'indexWeb'])->name('jurusan.index');


// Absensi
Route::controller(AbsensiController::class)->prefix('absensi')->name('absensi.')->group(function () {
    Route::get('/', 'selectClass')->name('index');
    Route::get('/{kelas}/{jurusan}', 'selectYear')->name('class.show');
    Route::get('/{kelas}/{jurusan}/{tahun}', 'selectMonth')->name('year.show');
    Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}', 'showMonth')->name('month.show');
    Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{tanggal}', 'showDay')->name('day.show');
    Route::post('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{tanggal}', 'store')->name('day.store');
    Route::post('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{tanggal}/holiday', 'storeHoliday')->name('holiday.store');
    Route::post('/year', 'storeYear')->name('year.store');
});

// Absensi Exports
Route::controller(AbsensiExportController::class)->prefix('absensi')->name('absensi.')->group(function () {
    Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/export/excel', 'exportMonthExcel')->name('month.export.excel');
    Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/export/pdf', 'exportMonthPdf')->name('month.export.pdf');
});


// Uang Kas
Route::controller(UangKasController::class)->prefix('uang-kas')->name('uang-kas.')->group(function () {
    Route::get('/', 'selectClass')->name('index');
    Route::get('/{kelas}/{jurusan}', 'selectYear')->name('class.show');
    Route::get('/{kelas}/{jurusan}/{tahun}', 'selectMonth')->name('year.show');
    Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}', 'showMonth')->name('month.show');
    Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{minggu}', 'showWeek')->name('week.show');
    Route::post('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{minggu}', 'store')->name('week.store');
    Route::post('/year', 'storeYear')->name('year.store');
    Route::post('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{minggu}/holiday', 'storeHoliday')->name('holiday.store');
});

// Uang Kas Exports
Route::controller(UangKasExportController::class)->prefix('uang-kas')->name('uang-kas.')->group(function () {
    Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/export/excel', 'exportMonthExcel')->name('month.export.excel');
    Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/export/pdf', 'exportMonthPdf')->name('month.export.pdf');
});