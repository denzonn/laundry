<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LacakController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\MetodePembayaranController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ParfumController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// publik, tanpa login
Route::middleware('throttle:30,1')->group(function () {
    Route::get('lacak', [LacakController::class, 'index'])->name('lacak');
    Route::get('lacak/{kode}', [LacakController::class, 'show'])->name('lacak.show');
});

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('profil', [ProfilController::class, 'edit'])->name('profil');
    Route::put('profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::put('profil/password', [ProfilController::class, 'password'])->name('profil.password');

    Route::get('pelanggan', [PelangganController::class, 'index'])->name('pelanggan');
    Route::get('pelanggan/data', [PelangganController::class, 'data'])->name('pelanggan.data');
    Route::post('pelanggan', [PelangganController::class, 'store'])->name('pelanggan.store');
    Route::put('pelanggan/{pelanggan}', [PelangganController::class, 'update'])->name('pelanggan.update');
    Route::delete('pelanggan/{pelanggan}', [PelangganController::class, 'destroy'])->name('pelanggan.destroy');
    Route::get('pelanggan/{pelanggan}/orders', [PelangganController::class, 'orders'])->name('pelanggan.orders');

    Route::get('order', [OrderController::class, 'index'])->name('order');
    Route::get('order/data', [OrderController::class, 'data'])->name('order.data');
    Route::get('order/baru', [OrderController::class, 'create'])->name('order.create');
    Route::post('order', [OrderController::class, 'store'])->name('order.store');
    Route::get('order/{order}', [OrderController::class, 'show'])->name('order.show');
    Route::get('order/{order}/struk', [OrderController::class, 'struk'])->name('order.struk');
    Route::patch('order/{order}/status', [OrderController::class, 'status'])->name('order.status');
    Route::post('order/{order}/bayar', [OrderController::class, 'bayar'])->name('order.bayar');

    Route::middleware('role:owner')->group(function () {
        Route::get('layanan', [LayananController::class, 'index'])->name('layanan');
        Route::get('layanan/data', [LayananController::class, 'data'])->name('layanan.data');
        Route::post('layanan', [LayananController::class, 'store'])->name('layanan.store');
        Route::put('layanan/{layanan}', [LayananController::class, 'update'])->name('layanan.update');
        Route::patch('layanan/{layanan}/toggle', [LayananController::class, 'toggle'])->name('layanan.toggle');
        Route::delete('layanan/{layanan}', [LayananController::class, 'destroy'])->name('layanan.destroy');

        Route::get('parfum', [ParfumController::class, 'index'])->name('parfum');
        Route::get('parfum/data', [ParfumController::class, 'data'])->name('parfum.data');
        Route::post('parfum', [ParfumController::class, 'store'])->name('parfum.store');
        Route::put('parfum/{parfum}', [ParfumController::class, 'update'])->name('parfum.update');
        Route::patch('parfum/{parfum}/toggle', [ParfumController::class, 'toggle'])->name('parfum.toggle');
        Route::delete('parfum/{parfum}', [ParfumController::class, 'destroy'])->name('parfum.destroy');

        Route::get('metode-pembayaran', [MetodePembayaranController::class, 'index'])->name('metode-pembayaran');
        Route::get('metode-pembayaran/data', [MetodePembayaranController::class, 'data'])->name('metode-pembayaran.data');
        Route::post('metode-pembayaran', [MetodePembayaranController::class, 'store'])->name('metode-pembayaran.store');
        Route::put('metode-pembayaran/{metode}', [MetodePembayaranController::class, 'update'])->name('metode-pembayaran.update');
        Route::patch('metode-pembayaran/{metode}/toggle', [MetodePembayaranController::class, 'toggle'])->name('metode-pembayaran.toggle');
        Route::delete('metode-pembayaran/{metode}', [MetodePembayaranController::class, 'destroy'])->name('metode-pembayaran.destroy');

        Route::get('user', [UserController::class, 'index'])->name('user');
        Route::get('user/data', [UserController::class, 'data'])->name('user.data');
        Route::post('user', [UserController::class, 'store'])->name('user.store');
        Route::put('user/{user}', [UserController::class, 'update'])->name('user.update');
        Route::patch('user/{user}/toggle', [UserController::class, 'toggle'])->name('user.toggle');
        Route::delete('user/{user}', [UserController::class, 'destroy'])->name('user.destroy');

        Route::get('laporan/pendapatan', [LaporanController::class, 'pendapatan'])->name('laporan.pendapatan');
        Route::get('laporan/order', [LaporanController::class, 'order'])->name('laporan.order');
        Route::get('laporan/piutang', [LaporanController::class, 'piutang'])->name('laporan.piutang');

        Route::get('pengaturan', [PengaturanController::class, 'edit'])->name('pengaturan');
        Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
    });
});

require __DIR__.'/auth.php';
