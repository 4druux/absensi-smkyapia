<?php

use App\Http\Controllers\DataSiswa\DataSiswaController;
use App\Http\Controllers\DataSiswa\JurusanController;
use App\Http\Controllers\Absensi\AbsensiController; 
use App\Http\Controllers\Absensi\AbsensiExportController;
use App\Http\Controllers\Grafik\GrafikController;
use App\Http\Controllers\Grafik\GrafikExportController;
use App\Http\Controllers\Indisipliner\IndisiplinerController;
use App\Http\Controllers\Indisipliner\IndisiplinerExportController;
use App\Http\Controllers\Kenaikan\KenaikanController;
use App\Http\Controllers\Kenaikan\KenaikanExportController;
use App\Http\Controllers\Permasalahan\PermasalahanController;
use App\Http\Controllers\Permasalahan\PermasalahanExportController;
use App\Http\Controllers\Rekapitulasi\RekapitulasiController;
use App\Http\Controllers\Rekapitulasi\RekapitulasiExportController;
use App\Http\Controllers\UangKas\PengeluaranController;
use App\Http\Controllers\UangKas\UangKasController; 
use App\Http\Controllers\UangKas\UangKasExportController;
use App\Http\Controllers\UangKas\UangKasOtherController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\User\UserApiController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', '/beranda');
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/akses-ditolak', function () {
    return Inertia::render('AccessDenied');
})->name('access.denied');

Route::get('/beranda', action: [UserController::class, 'showDataUser'])->name('home')->middleware(['auth']);

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::middleware(['role:superadmin'])->prefix('api/users')->group(function () {
        Route::get('/pending', [UserApiController::class, 'getPendingUsers'])->name('api.users.pending');
        Route::get('/approved', [UserApiController::class, 'getApprovedUsers'])->name('api.users.approved');
        Route::post('/{user}/approve', [UserApiController::class, 'approveUser'])->name('api.users.approve');
        Route::delete('/{user}/reject', [UserApiController::class, 'rejectUser'])->name('api.users.reject');
    });

    Route::middleware(['role:superadmin,admin'])->group(function () {
        Route::prefix('data-siswa')->name('data-siswa.')->group(function () {
            Route::controller(DataSiswaController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/input', 'create')->name('input');
                Route::get('/kelas/{kelas}', 'showClass')->name('class.show');
            });
            Route::get('/jurusan', [JurusanController::class, 'indexWeb'])->name('jurusan.index');
        });
        Route::prefix('rekapitulasi')->name('rekapitulasi.')->group(function () {
            Route::controller(RekapitulasiController::class)->group(function () {
                Route::get('/', 'selectClass')->name('index');
                Route::get('/{kelas}/{jurusan}', 'selectYear')->name('class.show');
                Route::get('/{kelas}/{jurusan}/{tahun}', 'selectMonth')->name('year.show');
                Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}', 'showMonth')->name('month.show');
                Route::post('/store-note', 'storeStudentNote')->name('store.note');
        });
            Route::controller(RekapitulasiExportController::class)->group(function () {
                Route::get('/{kelas}/{jurusan}/{tahun}/export/excel', 'exportYearExcel')->name('year.export.excel');
                Route::get('/{kelas}/{jurusan}/{tahun}/export/pdf', 'exportYearPdf')->name('year.export.pdf');
                Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/export/excel', 'exportMonthExcel')->name('month.export.excel');
                Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/export/pdf', 'exportMonthPdf')->name('month.export.pdf');
            });
        });

        Route::prefix('grafik')->name('grafik.')->group(function () {
            Route::controller(GrafikController::class)->group(function () {
                Route::get('/', 'selectClass')->name('index');
                Route::get('/{kelas}/{jurusan}', 'selectYear')->name('class.show');
                Route::get('/{kelas}/{jurusan}/{tahun}', 'showYear')->name('year.show');
            });
            Route::controller(GrafikExportController::class)->prefix('absensi')->name('absensi.')->group(function () {
                Route::get('/{kelas}/{jurusan}/{tahun}/export/excel', 'exportYearExcel')->name('year.export.excel');
                Route::get('/{kelas}/{jurusan}/{tahun}/export/pdf', 'exportYearPdf')->name('year.export.pdf');
            });
        });
        Route::prefix('kenaikan-bersyarat')->name('kenaikan-bersyarat.')->group(function () {
            Route::controller(KenaikanController::class)->group(function () {
                Route::get('/', 'selectClass')->name('index');
                Route::get('/{kelas}/{jurusan}', 'selectYear')->name('class.show');
                Route::get('/{kelas}/{jurusan}/{tahun}', 'showYear')->name('year.show');
                Route::get('/{kelas}/{jurusan}/{tahun}/{siswa}', 'showStudentForm')->name('student.show');
            });
            Route::controller(KenaikanExportController::class)->group(function () {
                Route::get('/{kelas}/{jurusan}/{tahun}/export/excel', 'exportExcel')->name('export.excel');
                Route::get('/{kelas}/{jurusan}/{tahun}/export/pdf', 'exportPdf')->name('export.pdf');
            });
        });
        Route::prefix('permasalahan')->name('permasalahan.')->group(function () {
            Route::controller(PermasalahanController::class)->group(function () {
                Route::get('/', 'selectClass')->name('index');
                Route::get('/{kelas}/{jurusan}', 'selectYear')->name('class.show');
                Route::get('/{kelas}/{jurusan}/{tahun}', 'showYear')->name('year.show');
                Route::get('/{kelas}/{jurusan}/{tahun}/class-problems', 'showClassProblems')->name('class-problems.show');
                Route::get('/{kelas}/{jurusan}/{tahun}/student-problems', 'showStudentProblems')->name('student-problems.show');
            });
            Route::controller(PermasalahanExportController::class)->group(function () {
                Route::get('/{kelas}/{jurusan}/{tahun}/export/excel', 'exportCombinedExcel')->name('export.excel');
                Route::get('/{kelas}/{jurusan}/{tahun}/export/pdf', 'exportCombinedPdf')->name('export.pdf');
            });
        });
        Route::prefix('data-indisipliner')->name('indisipliner.')->group(function () {
            Route::controller(IndisiplinerController::class)->group(function () {
                Route::get('/', 'selectClass')->name('index');
                Route::get('/{kelas}/{jurusan}', 'selectYear')->name('class.show');
                Route::get('/{kelas}/{jurusan}/{tahun}', 'showYear')->name('year.show');
            });
            Route::controller(IndisiplinerExportController::class)->group(function () {
                Route::get('/{kelas}/{jurusan}/{tahun}/export/excel', 'exportExcel')->name('export.excel');
                Route::get('/{kelas}/{jurusan}/{tahun}/export/pdf', 'exportPdf')->name('export.pdf');
                Route::get('/siswa/{siswa}/{tahun}/{noUrut}/export/pdf', 'exportStudentPdf')->name('student.export.pdf');
            });
        });
    });

    Route::middleware(['role:superadmin,walikelas,admin'])->group(function () {
        Route::prefix('absensi')->name('absensi.')->group(function () {
            Route::controller(AbsensiController::class)->group(function () {
                Route::get('/', 'selectClass')->name('index');
                Route::get('/{kelas}/{jurusan}', 'selectYear')->name('class.show');
                Route::get('/{kelas}/{jurusan}/{tahun}', 'selectMonth')->name('year.show');
                Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}', 'showMonth')->name('month.show');
                Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{tanggal}', 'showDay')->name('day.show');
                Route::post('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{tanggal}', 'store')->name('day.store');
                Route::post('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{tanggal}/holiday', 'storeHoliday')->name('holiday.store');
                Route::post('/year', 'storeYear')->name('year.store');
            });
            Route::controller(AbsensiExportController::class)->group(function () {
                Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/export/excel', 'exportMonthExcel')->name('month.export.excel');
                Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/export/pdf', 'exportMonthPdf')->name('month.export.pdf');
                Route::get('/{kelas}/{jurusan}/{tahun}/export/rekap/excel', 'exportYearExcel')->name('year.export.rekap.excel');
                Route::get('/{kelas}/{jurusan}/{tahun}/export/rekap/pdf', 'exportYearPdf')->name('year.export.rekap.pdf');
            });
        });
    });

    Route::middleware(['role:superadmin,walikelas,bendaharakelas'])->group(function () {
        Route::prefix('uang-kas')->name('uang-kas.')->group(function () {
            Route::controller(PengeluaranController::class)->group(function () {
                Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/pengeluaran', 'index')->name('pengeluaran.index');
                Route::post('/{kelas}/{jurusan}/pengeluaran', 'store')->name('pengeluaran.store');
            });
            Route::controller(UangKasOtherController::class)->group(function () {
                Route::post('/{kelas}/{jurusan}/other-cash', 'store')->name('other-cash.store');
                Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/other-cash/{iuran}', 'show')->name('other-cash.show');
            });
            Route::controller(UangKasController::class)->group(function () {
                Route::get('/', 'selectClass')->name('index');
                Route::get('/{kelas}/{jurusan}', 'selectYear')->name('class.show');
                Route::get('/{kelas}/{jurusan}/{tahun}', 'selectMonth')->name('year.show');
                Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}', 'showMonth')->name('month.show');
                Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{minggu}', 'showWeek')->name('week.show');
                Route::post('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{minggu}', 'store')->name('week.store');
                Route::post('/year', 'storeYear')->name('year.store');
                Route::post('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/{minggu}/holiday', 'storeHoliday')->name('holiday.store');
            });
            Route::controller(UangKasExportController::class)->group(function () {
                Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/export/excel', 'exportMonthExcel')->name('month.export.excel');
                Route::get('/{kelas}/{jurusan}/{tahun}/{bulanSlug}/export/pdf', 'exportMonthPdf')->name('month.export.pdf');
            });
        });
    });
});