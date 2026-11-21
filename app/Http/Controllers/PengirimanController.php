<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Pengiriman;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PengirimanController extends Controller
{
    /** Owner/kasir: semua jadwal antar-jemput, filter tanggal/status/kurir lewat query string. */
    public function index(Request $request)
    {
        $tanggal = $request->date('tanggal') ?? today();

        $list = Pengiriman::with(['order:id,kode,pelanggan_id,total,dibayar', 'order.pelanggan:id,nama,no_hp', 'kurir:id,name'])
            ->whereDate('jadwal', $tanggal)
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->kurir, fn ($q, $v) => $v === 'kosong' ? $q->whereNull('kurir_id') : $q->where('kurir_id', $v))
            ->orderBy('jadwal')
            ->get();

        return view('pengiriman.index', [
            'tanggal' => $tanggal,
            'list' => $list,
            'kurir' => User::kurirAktif()->get(['id', 'name']),
            'ringkasan' => Pengiriman::whereDate('jadwal', $tanggal)->selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status'),
            'belumDitugaskan' => Pengiriman::aktif()->whereNull('kurir_id')->count(),
        ]);
    }

    /** Kurir: tugas miliknya yang masih aktif, dikelompokkan hari ini / besok / nanti di view. */
    public function tugas(Request $request)
    {
        $tugas = Pengiriman::with(['order:id,kode,pelanggan_id,total,dibayar', 'order.pelanggan:id,nama,no_hp'])
            ->where('kurir_id', $request->user()->id)
            ->where(fn ($q) => $q->aktif()->orWhere(fn ($q) => $q->where('status', 'selesai')->whereDate('selesai_at', today())))
            ->orderBy('jadwal')
            ->get();

        return view('pengiriman.tugas', ['tugas' => $tugas]);
    }

    public function store(Request $request, Order $order)
    {
        abort_if($order->status === 'batal', 422, 'Order batal tidak bisa dijadwalkan antar/jemput.');

        $order->pengiriman()->create($this->validated($request) + $request->validate([
            'jenis' => ['required', Rule::in(['jemput', 'antar'])],
        ]));

        return response()->json(['message' => 'Jadwal antar/jemput dibuat.']);
    }

    /** Ubah jadwal / tugaskan ulang kurir (selama belum selesai). */
    public function update(Request $request, Pengiriman $pengiriman)
    {
        abort_unless(isset(Pengiriman::ALUR[$pengiriman->status]), 422, 'Pengiriman ini sudah tidak bisa diubah.');

        $pengiriman->update($this->validated($request));

        return response()->json(['message' => 'Jadwal diperbarui.']);
    }

    public function status(Request $request, Pengiriman $pengiriman)
    {
        $user = $request->user();
        abort_if($user->isKurir() && (int) $pengiriman->kurir_id !== $user->id, 403, 'Ini bukan tugas Anda.');

        $data = $request->validate(['status' => ['required', Rule::in(array_keys(Pengiriman::LABEL))]]);

        DB::transaction(fn () => Pengiriman::lockForUpdate()->findOrFail($pengiriman->id)->ubahStatus($data['status'], $user));

        return response()->json(['message' => 'Status: '.Pengiriman::LABEL[$data['status']].'.']);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'kurir_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'kurir')->where('aktif', true)],
            'alamat' => ['required', 'string', 'max:255'],
            'jadwal' => ['required', 'date'],
            'biaya' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], [], ['kurir_id' => 'kurir']);

        return ['biaya' => (int) ($data['biaya'] ?? 0)] + $data;
    }
}
