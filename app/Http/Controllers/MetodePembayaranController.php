<?php

namespace App\Http\Controllers;

use App\Models\MetodePembayaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MetodePembayaranController extends Controller
{
    public function index()
    {
        return view('metode-pembayaran.index');
    }

    public function data()
    {
        return MetodePembayaran::orderBy('id')->get();
    }

    public function store(Request $request)
    {
        MetodePembayaran::create($request->validate(['nama' => ['required', 'string', 'max:50', 'unique:metode_pembayaran']]));

        return response()->json(['message' => 'Metode pembayaran ditambahkan.']);
    }

    public function update(Request $request, MetodePembayaran $metode)
    {
        $metode->update($request->validate(['nama' => ['required', 'string', 'max:50', Rule::unique('metode_pembayaran')->ignore($metode)]]));

        return response()->json(['message' => 'Metode pembayaran diperbarui.']);
    }

    public function toggle(MetodePembayaran $metode)
    {
        abort_if($metode->aktif && MetodePembayaran::where('aktif', true)->count() === 1, 422, 'Minimal harus ada 1 metode pembayaran aktif.');

        $metode->update(['aktif' => ! $metode->aktif]);

        return response()->json(['message' => $metode->aktif ? 'Metode diaktifkan.' : 'Metode dinonaktifkan.']);
    }

    public function destroy(MetodePembayaran $metode)
    {
        abort_if($metode->aktif && MetodePembayaran::where('aktif', true)->count() === 1, 422, 'Minimal harus ada 1 metode pembayaran aktif.');

        $metode->delete();

        return response()->json(['message' => 'Metode pembayaran dihapus.']);
    }
}
