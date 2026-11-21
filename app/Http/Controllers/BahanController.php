<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BahanController extends Controller
{
    public function index()
    {
        return view('bahan.index');
    }

    public function data()
    {
        // yang menipis di atas
        return Bahan::orderByRaw('CASE WHEN stok <= stok_minimum THEN 0 ELSE 1 END')->orderBy('nama')->get();
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request) + $request->validate(['stok_awal' => ['nullable', 'numeric', 'min:0', 'max:99999999']]);

        DB::transaction(function () use ($data, $request) {
            $bahan = Bahan::create($data);

            if (($data['stok_awal'] ?? 0) > 0) {
                $bahan->mutasi('masuk', (float) $data['stok_awal'], $request->user(), 'Stok awal');
            }
        });

        return response()->json(['message' => 'Bahan ditambahkan.']);
    }

    public function update(Request $request, Bahan $bahan)
    {
        $bahan->update($this->validasi($request, $bahan));

        return response()->json(['message' => 'Bahan diperbarui.']);
    }

    public function destroy(Bahan $bahan)
    {
        $bahan->delete();

        return response()->json(['message' => 'Bahan dihapus.']);
    }

    public function mutasi(Request $request, Bahan $bahan)
    {
        $data = $request->validate([
            'jenis' => ['required', Rule::in(array_keys(Bahan::JENIS))],
            'jumlah' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $bahan->mutasi($data['jenis'], (float) $data['jumlah'], $request->user(), $data['keterangan'] ?? null);

        return response()->json(['message' => 'Stok '.$bahan->nama.' sekarang '.Bahan::angka($bahan->stok).' '.$bahan->satuan.'.']);
    }

    public function riwayat(Bahan $bahan)
    {
        return $bahan->riwayat()->with('user:id,name')->limit(30)->get();
    }

    private function validasi(Request $request, ?Bahan $bahan = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:50', Rule::unique('bahan')->ignore($bahan)],
            'satuan' => ['required', 'string', 'max:20'],
            'stok_minimum' => ['required', 'numeric', 'min:0', 'max:99999999'],
        ], [], ['stok_minimum' => 'stok minimum']);
    }
}
