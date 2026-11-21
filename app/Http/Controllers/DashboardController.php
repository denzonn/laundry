<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use App\Models\Order;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'orderHariIni' => Order::whereDate('tanggal_masuk', today())->where('status', '!=', 'batal')->count(),
            'antrian' => Order::whereIn('status', ['antrian', 'dicuci', 'disetrika', 'siap_diambil'])
                ->selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status'),
            'terlambat' => Order::terlambat()->with('pelanggan:id,nama')->orderBy('estimasi_selesai')->limit(10)->get(),
            'jumlahTerlambat' => Order::terlambat()->count(),
            'siap' => Order::where('status', 'siap_diambil')->with('pelanggan:id,nama,no_hp')->orderBy('updated_at')->limit(10)->get(),
        ];

        if ($request->user()->isOwner()) {
            $harian = Pembayaran::where('tanggal', '>=', today()->subDays(6))
                ->selectRaw('DATE(tanggal) as tgl, SUM(jumlah) as total')->groupBy('tgl')->pluck('total', 'tgl');

            $data['grafik'] = collect(range(6, 0))->mapWithKeys(fn ($i) => [
                ($d = today()->subDays($i)->toDateString()) => (int) ($harian[$d] ?? 0),
            ]);
            $data['pendapatanHariIni'] = $data['grafik']->last();
            $data['piutang'] = (int) Order::piutang()->sum(DB::raw('total - dibayar'));
            $data['bahanMenipis'] = Bahan::menipis()->orderBy('nama')->pluck('nama');
        }

        return view('dashboard', $data);
    }
}
