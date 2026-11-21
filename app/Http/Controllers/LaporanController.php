<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /** Periode dari ?start=&end= (Y-m-d), default bulan ini. */
    private function periode(Request $request): array
    {
        $request->validate(['start' => ['nullable', 'date'], 'end' => ['nullable', 'date', 'after_or_equal:start']]);

        return [
            $request->date('start')?->startOfDay() ?? today()->startOfMonth(),
            $request->date('end')?->endOfDay() ?? today()->endOfDay(),
        ];
    }

    public function pendapatan(Request $request)
    {
        [$start, $end] = $this->periode($request);
        $q = Pembayaran::whereBetween('pembayaran.tanggal', [$start, $end]);

        $perHari = (clone $q)->selectRaw('DATE(tanggal) as tgl, SUM(jumlah) as total, COUNT(*) as jumlah')
            ->groupBy('tgl')->orderBy('tgl')->get();

        $perMetode = (clone $q)->join('metode_pembayaran as m', 'm.id', '=', 'pembayaran.metode_pembayaran_id')
            ->selectRaw('m.nama, SUM(pembayaran.jumlah) as total, COUNT(*) as jumlah')
            ->groupBy('m.id', 'm.nama')->orderByDesc('total')->get();

        return view('laporan.pendapatan', [
            'start' => $start, 'end' => $end, 'perHari' => $perHari, 'perMetode' => $perMetode,
            'total' => (int) $perHari->sum('total'), 'transaksi' => (int) $perHari->sum('jumlah'),
        ]);
    }

    public function order(Request $request)
    {
        [$start, $end] = $this->periode($request);
        $q = Order::whereBetween('tanggal_masuk', [$start, $end]);

        $perLayanan = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('layanan as l', 'l.id', '=', 'order_items.layanan_id')
            ->whereBetween('orders.tanggal_masuk', [$start, $end])
            ->where('orders.status', '!=', 'batal')
            ->selectRaw('l.nama, l.satuan, SUM(order_items.qty) as qty, SUM(order_items.subtotal) as subtotal, COUNT(DISTINCT order_items.order_id) as jumlah_order')
            ->groupBy('l.id', 'l.nama', 'l.satuan')->orderByDesc('subtotal')->get();

        return view('laporan.order', [
            'start' => $start, 'end' => $end, 'perLayanan' => $perLayanan,
            'perStatus' => (clone $q)->selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status'),
            'omzet' => (int) (clone $q)->where('status', '!=', 'batal')->sum('total'),
        ]);
    }

    /** Laba rugi kas: uang masuk (pembayaran) − pengeluaran di periode. */
    public function labaRugi(Request $request)
    {
        [$start, $end] = $this->periode($request);
        $harian = fn ($model) => $model::whereBetween('tanggal', [$start, $end])
            ->selectRaw('DATE(tanggal) as tgl, SUM(jumlah) as total')->groupBy('tgl')->pluck('total', 'tgl');
        $masuk = $harian(Pembayaran::class);
        $keluar = $harian(Pengeluaran::class);

        $perKategori = Pengeluaran::whereBetween('pengeluaran.tanggal', [$start, $end])
            ->join('kategori_pengeluaran as k', 'k.id', '=', 'pengeluaran.kategori_pengeluaran_id')
            ->selectRaw('k.id, k.nama, SUM(pengeluaran.jumlah) as total, COUNT(*) as jumlah')
            ->groupBy('k.id', 'k.nama')->orderByDesc('total')->get();

        // Tren: per hari bila ≤ 62 hari, selebihnya per bulan (dikelompokkan di PHP agar jalan di MySQL & SQLite)
        $bulanan = $start->diffInDays($end) > 62;
        $tren = $masuk->keys()->merge($keluar->keys())->unique()->sort()
            ->groupBy(fn ($tgl) => $bulanan ? substr($tgl, 0, 7) : $tgl)
            ->map(fn ($tgls) => [
                'masuk' => (int) $tgls->sum(fn ($t) => $masuk[$t] ?? 0),
                'keluar' => (int) $tgls->sum(fn ($t) => $keluar[$t] ?? 0),
            ]);

        $pendapatan = (int) $masuk->sum();
        $pengeluaran = (int) $keluar->sum();

        return view('laporan.laba-rugi', compact('start', 'end', 'perKategori', 'tren', 'bulanan', 'pendapatan', 'pengeluaran')
            + ['laba' => $pendapatan - $pengeluaran]);
    }

    public function piutang()
    {
        // ponytail: tanpa pagination, piutang laundry biasanya puluhan baris; paginate() bila ratusan
        $orders = Order::piutang()->with('pelanggan:id,nama,no_hp')->orderBy('tanggal_masuk')->get();

        return view('laporan.piutang', ['orders' => $orders, 'total' => $orders->sum('sisa')]);
    }
}
