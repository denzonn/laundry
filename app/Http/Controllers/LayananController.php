<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LayananController extends Controller
{
    public const SATUAN = ['kiloan' => ['kg'], 'satuan' => ['pcs', 'pasang', 'm²', 'set']];

    public function index()
    {
        return view('layanan.index');
    }

    // ponytail: tanpa pagination, daftar layanan satu laundry biasanya < 50 baris
    public function data(Request $request)
    {
        return Layanan::query()
            ->when($request->search, fn ($q, $s) => $q->where('nama', 'like', "%$s%"))
            ->orderByDesc('aktif')->orderBy('kategori')->orderBy('harga')
            ->get();
    }

    public function store(Request $request)
    {
        Layanan::create($this->validated($request));

        return response()->json(['message' => 'Layanan ditambahkan.']);
    }

    public function update(Request $request, Layanan $layanan)
    {
        $layanan->update($this->validated($request));

        return response()->json(['message' => 'Layanan diperbarui.']);
    }

    public function toggle(Layanan $layanan)
    {
        $layanan->update(['aktif' => ! $layanan->aktif]);

        return response()->json(['message' => $layanan->aktif ? 'Layanan diaktifkan.' : 'Layanan disembunyikan.']);
    }

    public function destroy(Layanan $layanan)
    {
        $layanan->delete();

        return response()->json(['message' => 'Layanan dihapus.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'kategori' => ['required', Rule::in(array_keys(self::SATUAN))],
            'satuan' => ['required', Rule::in(self::SATUAN[$request->kategori] ?? [])],
            'harga' => ['required', 'integer', 'min:0', 'max:100000000'],
            'durasi_jam' => ['required', 'integer', 'min:1', 'max:720'],
        ], [], ['durasi_jam' => 'durasi']);
    }
}
