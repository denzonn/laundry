<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PelangganController extends Controller
{
    public function index()
    {
        return view('pelanggan.index');
    }

    public function data(Request $request)
    {
        return Pelanggan::cari($request->search)->orderBy('nama')->paginate(20);
    }

    public function orders(Pelanggan $pelanggan)
    {
        return response()->json([
            'jumlah' => $pelanggan->orders()->where('status', '!=', 'batal')->count(),
            'total' => (int) $pelanggan->orders()->where('status', '!=', 'batal')->sum('total'),
            'data' => $pelanggan->orders()->latest('id')->limit(5)->get(['id', 'kode', 'status', 'status_bayar', 'total', 'tanggal_masuk']),
        ]);
    }

    public function store(Request $request)
    {
        $pelanggan = Pelanggan::create($this->validated($request));

        // dikembalikan juga agar halaman Order Baru bisa langsung memilih pelanggan ini
        return response()->json(['message' => 'Pelanggan ditambahkan.', 'data' => $pelanggan]);
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $pelanggan->update($this->validated($request, $pelanggan));

        return response()->json(['message' => 'Pelanggan diperbarui.', 'data' => $pelanggan]);
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $pelanggan->delete();

        return response()->json(['message' => 'Pelanggan dihapus.']);
    }

    private function validated(Request $request, ?Pelanggan $pelanggan = null): array
    {
        $request->merge(['no_hp' => Pelanggan::normalisasiHp($request->no_hp)]);

        return $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'digits_between:9,15', Rule::unique('pelanggan')->ignore($pelanggan)],
            'alamat' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], ['no_hp.unique' => 'No HP ini sudah terdaftar sebagai pelanggan lain.'], ['no_hp' => 'no HP']);
    }
}
