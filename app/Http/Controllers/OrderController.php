<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Layanan;
use App\Models\MetodePembayaran;
use App\Models\Order;
use App\Models\Parfum;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index()
    {
        return view('order.index');
    }

    public function data(Request $request)
    {
        $base = Order::query()
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('kode', 'like', "%$s%")
                ->orWhereHas('pelanggan', fn ($q) => $q->cari($s))))
            ->when($request->status_bayar, fn ($q, $v) => $q->where('status_bayar', $v))
            ->when($request->start && $request->end, fn ($q) => $q->whereBetween('tanggal_masuk', [
                $request->date('start')->startOfDay(), $request->date('end')->endOfDay(),
            ]));

        $orders = (clone $base)
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->with('pelanggan:id,nama,no_hp')
            ->latest('id')
            ->paginate(15);

        return response()->json($orders->toArray() + [
            'counts' => (clone $base)->selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status'),
        ]);
    }

    public function create()
    {
        return view('order.create', [
            'layanan' => Layanan::where('aktif', true)->orderBy('kategori')->orderBy('harga')->get(),
            'parfum' => Parfum::where('aktif', true)->orderBy('nama')->get(),
            'metode' => MetodePembayaran::where('aktif', true)->orderBy('id')->get(),
        ]);
    }

    public function store(OrderRequest $request)
    {
        $data = $request->validated();

        $order = DB::transaction(function () use ($data, $request) {
            $layanan = Layanan::findMany(collect($data['items'])->pluck('layanan_id'))->keyBy('id');

            // harga & subtotal SELALU dari server, bukan dari client
            $items = collect($data['items'])->map(function ($item) use ($layanan) {
                $l = $layanan[$item['layanan_id']];

                return [
                    'layanan_id' => $l->id, 'nama_layanan' => $l->nama, 'satuan' => $l->satuan,
                    'harga' => $l->harga, 'qty' => $item['qty'], 'subtotal' => (int) round($l->harga * $item['qty']),
                ];
            });

            $subtotal = $items->sum('subtotal');
            $diskon = (int) ($data['diskon'] ?? 0);
            $total = $subtotal - $diskon + (int) ($data['biaya_antar'] ?? 0);
            $bayar = (int) ($data['bayar'] ?? 0);

            throw_if($diskon > $subtotal, ValidationException::withMessages(['diskon' => 'Diskon tidak boleh melebihi subtotal.']));
            throw_if($bayar > $total, ValidationException::withMessages(['bayar' => 'Pembayaran melebihi total tagihan.']));
            throw_if($bayar > 0 && empty($data['metode_pembayaran_id']), ValidationException::withMessages(['metode_pembayaran_id' => 'Pilih metode pembayaran.']));

            $order = Order::create([
                'kode' => Order::kodeBaru(),
                'pelanggan_id' => $data['pelanggan_id'],
                'user_id' => $request->user()->id,
                'parfum_id' => $data['parfum_id'] ?? null,
                'tanggal_masuk' => now(),
                'estimasi_selesai' => $data['estimasi_selesai'] ?? now()->addHours($layanan->max('durasi_jam')),
                'subtotal' => $subtotal,
                'diskon' => $diskon,
                'biaya_antar' => (int) ($data['biaya_antar'] ?? 0),
                'total' => $total,
                'status_bayar' => Order::statusBayar(0, $total),
                'catatan' => $data['catatan'] ?? null,
            ]);

            $order->items()->createMany($items);
            $order->logs()->create(['user_id' => $request->user()->id, 'status' => 'antrian', 'catatan' => 'Order dibuat']);

            if ($bayar > 0) {
                $order->bayar($bayar, $data['metode_pembayaran_id'], $request->user());
            }

            return $order;
        });

        return response()->json([
            'message' => "Order {$order->kode} berhasil dibuat.",
            'redirect' => route('order.show', $order),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['pelanggan', 'parfum', 'user:id,name', 'items', 'logs.user:id,name', 'pembayaran.metode', 'pembayaran.user:id,name']);

        return view('order.show', [
            'order' => $order,
            'metode' => MetodePembayaran::where('aktif', true)->orderBy('id')->get(),
        ]);
    }

    public function status(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([...Order::ALUR, 'batal'])],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(fn () => Order::lockForUpdate()->findOrFail($order->id)
            ->ubahStatus($data['status'], $request->user(), $data['catatan'] ?? null));

        return response()->json(['message' => 'Status diubah ke '.Order::LABEL[$data['status']].'.']);
    }

    public function bayar(Request $request, Order $order)
    {
        $data = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1'],
            'metode_pembayaran_id' => ['required', Rule::exists('metode_pembayaran', 'id')->where('aktif', true)],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], [], ['metode_pembayaran_id' => 'metode pembayaran']);

        // lock agar dua pembayaran bersamaan tidak melebihi sisa
        DB::transaction(fn () => Order::lockForUpdate()->findOrFail($order->id)
            ->bayar($data['jumlah'], $data['metode_pembayaran_id'], $request->user(), $data['catatan'] ?? null));

        return response()->json(['message' => 'Pembayaran dicatat.']);
    }
}
