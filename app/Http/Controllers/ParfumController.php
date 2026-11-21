<?php

namespace App\Http\Controllers;

use App\Models\Parfum;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParfumController extends Controller
{
    public function index()
    {
        return view('parfum.index');
    }

    public function data()
    {
        return Parfum::orderByDesc('aktif')->orderBy('nama')->get();
    }

    public function store(Request $request)
    {
        Parfum::create($request->validate(['nama' => ['required', 'string', 'max:50', 'unique:parfum']]));

        return response()->json(['message' => 'Parfum ditambahkan.']);
    }

    public function update(Request $request, Parfum $parfum)
    {
        $parfum->update($request->validate(['nama' => ['required', 'string', 'max:50', Rule::unique('parfum')->ignore($parfum)]]));

        return response()->json(['message' => 'Parfum diperbarui.']);
    }

    public function toggle(Parfum $parfum)
    {
        $parfum->update(['aktif' => ! $parfum->aktif]);

        return response()->json(['message' => $parfum->aktif ? 'Parfum diaktifkan.' : 'Parfum dinonaktifkan.']);
    }

    public function destroy(Parfum $parfum)
    {
        $parfum->delete();

        return response()->json(['message' => 'Parfum dihapus.']);
    }
}
