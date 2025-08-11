<?php

use App\Http\Controllers\DataSiswaController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\Absensi\AbsensiController; 
use App\Http\Controllers\Absensi\AbsensiExportController;
use App\Http\Controllers\Grafik\GrafikController;
use App\Http\Controllers\Grafik\GrafikExportController;
use App\Http\Controllers\Kenaikan\KenaikanController;
use App\Http\Controllers\Kenaikan\KenaikanExportController;
use App\Http\Controllers\Permasalahan\PermasalahanController;
use App\Http\Controllers\Rekapitulasi\RekapitulasiController;
use App\Http\Controllers\Rekapitulasi\RekapitulasiExportController;
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
    Route::get('/{kelas}/{jurusan}/{tahun}/export/rekap/excel', 'exportYearExcel')->name('year.export.rekap.excel'); // Rute baru untuk ekspor Excel tahunan
    Route::get('/{kelas}/{jurusan}/{tahun}/export/rekap/pdf', 'exportYearPdf')->name('year.export.rekap.pdf');
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

// Rekapitulasi
Route::controller(RekapitulasiController::class)->prefix('rekapitulasi')->name('rekapitulasi.')->group(function () {
    Route::get('/', 'selectClass')->name('index');
    Route::get('/{kelas}/{jurusan}', 'selectYear')->name('class.show');
    Route::get('/{kelas}/{jurusan}/{tahun}', 'selectMonth')->name('year.show');
    Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}', 'showMonth')->name('month.show');
});

// Rekapitulasi Exports
Route::controller(RekapitulasiExportController::class)->prefix('rekapitulasi')->name('rekapitulasi.')->group(function () {
    Route::get('/{kelas}/{jurusan}/{tahun}/export/excel', 'exportYearExcel')->name('year.export.excel');
    Route::get('/{kelas}/{jurusan}/{tahun}/export/pdf', 'exportYearPdf')->name('year.export.pdf');
    Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/export/excel', 'exportMonthExcel')->name('month.export.excel');
    Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/export/pdf', 'exportMonthPdf')->name('month.export.pdf');
});

// Grafik
Route::controller(GrafikController::class)->prefix('grafik')->name('grafik.')->group(function () {
    Route::get('/', 'selectClass')->name('index');
    Route::get('/{kelas}/{jurusan}', 'selectYear')->name('class.show');
    Route::get('/{kelas}/{jurusan}/{tahun}', 'showYear')->name('year.show');
});

// Grafik Exports
Route::controller(GrafikExportController::class)->prefix('grafik/absensi')->name('grafik.absensi.')->group(function () {
    Route::get('/{kelas}/{jurusan}/{tahun}/export/excel', 'exportYearExcel')->name('year.export.excel');
    Route::get('/{kelas}/{jurusan}/{tahun}/export/pdf', 'exportYearPdf')->name('year.export.pdf');
});

// Kenaikan Bersyarat
Route::controller(KenaikanController::class)->prefix('kenaikan-bersyarat')->name('kenaikan-bersyarat.')->group(function () {
    Route::get('/', 'selectClass')->name('index');
    Route::get('/{kelas}/{jurusan}', 'selectYear')->name('class.show');
    Route::get('/{kelas}/{jurusan}/{tahun}', 'showYear')->name('year.show');
    Route::get('/{kelas}/{jurusan}/{tahun}/{siswa}', 'showStudentForm')->name('student.show');
});

// Kenaikan Bersyarat Exports
Route::controller(KenaikanExportController::class)->prefix('kenaikan-bersyarat')->name('kenaikan-bersyarat.')->group(function () {
    Route::get('/{kelas}/{jurusan}/{tahun}/export/excel', 'exportExcel')->name('export.excel');
    Route::get('/{kelas}/{jurusan}/{tahun}/export/pdf', 'exportPdf')->name('export.pdf');
});

// Laporan Permasalahan
Route::controller(PermasalahanController::class)->prefix('permasalahan')->name('permasalahan.')->group(function () {
    Route::get('/', 'selectClass')->name('index');
    Route::get('/{kelas}/{jurusan}', 'selectYear')->name('class.show');
    Route::get('/{kelas}/{jurusan}/{tahun}', 'showYear')->name('year.show');
    Route::get('/{kelas}/{jurusan}/{tahun}/class-problems', 'showClassProblems')->name('class-problems.show');
    Route::get('/{kelas}/{jurusan}/{tahun}/student-problems', 'showStudentProblems')->name('student-problems.show');
});
Route::controller(App\Http\Controllers\Permasalahan\PermasalahanExportController::class)->prefix('permasalahan')->name('permasalahan.')->group(function () {
    Route::get('/{kelas}/{jurusan}/{tahun}/export/excel', 'exportCombinedExcel')->name('export.excel');
    Route::get('/{kelas}/{jurusan}/{tahun}/export/pdf', 'exportCombinedPdf')->name('export.pdf');
});