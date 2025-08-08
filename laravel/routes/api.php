<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataSiswaController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\Absensi\AbsensiApiController;
use App\Http\Controllers\UangKas\UangKasApiController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Jurusan Routes
Route::apiResource('/jurusan', JurusanController::class);
Route::get('/jurusan/{jurusan}/kelas', [JurusanController::class, 'getKelasByJurusan'])->name('api.jurusan.kelas');

// Kelas Routes
Route::get('/kelas', [KelasController::class, 'index'])->name('api.kelas.index');
Route::get('/kelas/{kelas}', [KelasController::class, 'show'])->name('api.kelas.show');
Route::post('/kelas', [KelasController::class, 'store'])->name('api.kelas.store');
Route::delete('/kelas/{kelas}', [KelasController::class, 'destroy'])->name('api.kelas.destroy');

// Siswa Routes
Route::post('/siswa', [DataSiswaController::class, 'storeApi'])->name('api.siswa.store');
Route::put('/siswa/{siswa}', [DataSiswaController::class, 'updateStudentApi'])->name('api.siswa.update');
Route::delete('/siswa/{siswa}', [DataSiswaController::class, 'destroyStudentApi'])->name('api.siswa.destroy');

Route::prefix('absensi')->name('api.absensi.')->group(function () {
    Route::get('/classes', [AbsensiApiController::class, 'getClasses'])->name('classes');
    Route::get('/years', [AbsensiApiController::class, 'getYears'])->name('years');
    Route::post('/years', [AbsensiApiController::class, 'storeYearApi'])->name('storeYear');
    Route::get('/{kelas}/{jurusan}/months/{tahun}', [AbsensiApiController::class, 'getMonths'])->name('months');
    Route::get('/{kelas}/{jurusan}/days/{tahun}/{bulanSlug}', [AbsensiApiController::class, 'getDays'])->name('days');
    Route::get('/{kelas}/{jurusan}/attendance/{tahun}/{bulanSlug}/{tanggal}', [AbsensiApiController::class, 'getAttendance'])->name('attendance');
    Route::post('/{kelas}/{jurusan}/attendance/{tahun}/{bulanSlug}/{tanggal}', [AbsensiApiController::class, 'storeAttendance'])->name('storeAttendance');
    Route::post('/{kelas}/{jurusan}/holidays/{tahun}/{bulanSlug}/{tanggal}', [AbsensiApiController::class, 'storeHolidayApi'])->name('storeHoliday');
});

Route::prefix('uang-kas')->name('api.uang-kas.')->group(function () {
    Route::get('/classes', [UangKasApiController::class, 'getClasses'])->name('classes');
    Route::get('/years', [UangKasApiController::class, 'getYears'])->name('years');
    Route::post('/years', [UangKasApiController::class, 'storeYearApi'])->name('storeYear');
    Route::get('/{kelas}/{jurusan}/months/{tahun}', [UangKasApiController::class, 'getMonths'])->name('months');
    Route::get('/{kelas}/{jurusan}/weeks/{tahun}/{bulanSlug}', [UangKasApiController::class, 'getWeeks'])->name('weeks');
    Route::get('/{kelas}/{jurusan}/payments/{tahun}/{bulanSlug}/{minggu}', [UangKasApiController::class, 'getPayments'])->name('payments');
    Route::post('/{kelas}/{jurusan}/payments/{tahun}/{bulanSlug}/{minggu}', [UangKasApiController::class, 'storePayments'])->name('storePayments');
    Route::post('/{kelas}/{jurusan}/holidays/{tahun}/{bulanSlug}/{minggu}', [UangKasApiController::class, 'storeHolidayApi'])->name('storeHoliday');
});