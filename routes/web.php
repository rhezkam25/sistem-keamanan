<?php

use App\Http\Controllers\AbsensiAdminController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\TamuController;
use App\Http\Controllers\PasswordRequestController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Proxy foto absensi — semua user ter-auth bisa akses
    Route::get('/foto/{path}', [AbsensiController::class, 'servePhoto'])
        ->where('path', '.+')
        ->name('foto.serve');

    // Tamu — bisa diakses Admin, Pejabat, Staff
    Route::middleware('role:admin,pejabat,staff')->group(function () {
        Route::resource('tamu', TamuController::class);
        Route::get('/tamu/{tamu}/qr', [TamuController::class, 'showQr'])->name('tamu.qr');
    });

    // Approval — hanya Admin & Pejabat
    Route::middleware('role:admin,pejabat')->group(function () {
        Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
        Route::post('/approval/{tamu}/setujui', [ApprovalController::class, 'setujui'])->name('approval.setujui');
        Route::post('/approval/{tamu}/tolak', [ApprovalController::class, 'tolak'])->name('approval.tolak');
    });

    // Scan QR — Admin, Pejabat, Satpam
    Route::middleware('role:admin,pejabat,satpam')->group(function () {
        Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
        Route::post('/scan', [ScanController::class, 'proses'])
            ->middleware('throttle:30,1')
            ->name('scan.proses');
        Route::post('/scan/checkout/{tamu}', [ScanController::class, 'manualCheckout'])
            ->middleware('throttle:20,1')
            ->name('scan.manual-checkout');
    });

    // Absensi — Satpam & Admin
    Route::middleware('role:admin,satpam')->group(function () {
        Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
        Route::post('/absensi/masuk', [AbsensiController::class, 'masuk'])->name('absensi.masuk');
        Route::post('/absensi/keluar', [AbsensiController::class, 'keluar'])->name('absensi.keluar');
        Route::get('/absensi/riwayat', [AbsensiController::class, 'riwayat'])->name('absensi.riwayat');
    });

    // Data Absensi — Admin & Pejabat (cek can_view_absensi di controller)
    Route::middleware('role:admin,pejabat')->group(function () {
        Route::get('/absensi/data', [AbsensiAdminController::class, 'index'])->name('absensi.admin.index');
        Route::get('/absensi/data/export', [AbsensiAdminController::class, 'export'])->name('absensi.admin.export');
        Route::get('/absensi/data/{user}', [AbsensiAdminController::class, 'show'])->name('absensi.admin.show');
    });

    // Manajemen User — hanya Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/password-requests', [PasswordRequestController::class, 'index'])->name('password-requests.index');
        Route::patch('/admin/password-requests/{request}/selesai', [PasswordRequestController::class, 'selesai'])->name('password-requests.selesai');

        Route::resource('users', UserController::class);
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggleActive');
        Route::patch('/users/{user}/toggle-absensi-access', [UserController::class, 'toggleAbsensiAccess'])->name('users.toggleAbsensiAccess');
        Route::get('/laporan', [DashboardController::class, 'laporan'])->name('laporan.index');
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::patch('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
    });
});

require __DIR__.'/auth.php';
