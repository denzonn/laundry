<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias(['role' => \App\Http\Middleware\RoleMiddleware::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Hapus data master yang sudah dipakai transaksi (FK restrictOnDelete) → pesan jelas, bukan error 500
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            $fk = ($e->errorInfo[1] ?? null) == 1451 || str_contains($e->getMessage(), 'FOREIGN KEY constraint failed');

            if ($fk && $request->expectsJson()) {
                return response()->json(['message' => 'Data ini sudah dipakai di transaksi sehingga tidak bisa dihapus. Nonaktifkan saja.'], 422);
            }
        });
    })->create();
