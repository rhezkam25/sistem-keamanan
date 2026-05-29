<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\TamuController;
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

    // Scan QR — hanya Admin & Satpam
    Route::middleware('role:admin,satpam')->group(function () {
        Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
        Route::post('/scan', [ScanController::class, 'proses'])->name('scan.proses');
    });

    // Manajemen User — hanya Admin
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/laporan', [DashboardController::class, 'laporan'])->name('laporan.index');
    });
});

require __DIR__.'/auth.php';
