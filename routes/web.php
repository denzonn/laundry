<?php

use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('profil', [ProfilController::class, 'edit'])->name('profil');
    Route::put('profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::put('profil/password', [ProfilController::class, 'password'])->name('profil.password');

    Route::middleware('role:owner')->group(function () {
        Route::get('user', [UserController::class, 'index'])->name('user');
        Route::get('user/data', [UserController::class, 'data'])->name('user.data');
        Route::post('user', [UserController::class, 'store'])->name('user.store');
        Route::put('user/{user}', [UserController::class, 'update'])->name('user.update');
        Route::patch('user/{user}/toggle', [UserController::class, 'toggle'])->name('user.toggle');
        Route::delete('user/{user}', [UserController::class, 'destroy'])->name('user.destroy');

        Route::get('pengaturan', [PengaturanController::class, 'edit'])->name('pengaturan');
        Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
    });
});

require __DIR__ . '/auth.php';
