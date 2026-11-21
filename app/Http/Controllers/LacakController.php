<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Pengaturan;
use Illuminate\Http\Request;

/** Halaman publik tanpa login. Kode order bisa ditebak (berurutan), jadi hanya tampilkan data minim & nama disamarkan. */
class LacakController extends Controller
{
    public function index(Request $request)
    {
        if ($kode = strtoupper(trim((string) $request->query('kode')))) {
            return redirect()->route('lacak.show', $kode);
        }

        return view('lacak.show', ['toko' => Pengaturan::toko(), 'order' => null, 'kode' => null]);
    }

    public function show(string $kode)
    {
        $order = Order::with('pelanggan:id,nama')->where('kode', strtoupper($kode))->first();

        return response()->view('lacak.show', ['toko' => Pengaturan::toko(), 'order' => $order, 'kode' => $kode], $order ? 200 : 404);
    }
}
