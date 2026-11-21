<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengeluaranRequest;
use App\Models\KategoriPengeluaran;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PengeluaranController extends Controller
{
    public function index()
    {
        return view('pengeluaran.index');
    }

    /** Pengeluaran satu bulan (?bulan=Y-m, ?kategori=id). Data kecil per bulan → tanpa pagination. */
    public function data(Request $request)
    {
        $request->validate(['bulan' => ['nullable', 'date_format:Y-m'], 'kategori' => ['nullable', 'integer']]);

        $bulan = $request->filled('bulan') ? Carbon::createFromFormat('!Y-m', $request->bulan) : today()->startOfMonth();
        $q = Pengeluaran::whereBetween('tanggal', [$bulan->copy()->startOfMonth(), $bulan->copy()->endOfMonth()]);

        $perKategori = (clone $q)->selectRaw('kategori_pengeluaran_id as id, SUM(jumlah) as total, COUNT(*) as jumlah')
            ->groupBy('kategori_pengeluaran_id')->orderByDesc('total')->get()
            ->map(fn ($r) => ['id' => $r->id, 'total' => (int) $r->total, 'jumlah' => (int) $r->jumlah]);

        return [
            'bulan' => $bulan->format('Y-m'),
            'total' => $perKategori->sum('total'),
            'perKategori' => $perKategori,
            'kategori' => KategoriPengeluaran::withCount('pengeluaran')->orderBy('nama')->get(),
            'items' => $q->when($request->kategori, fn ($q, $k) => $q->where('kategori_pengeluaran_id', $k))
                ->with('user:id,name')->orderByDesc('tanggal')->latest('id')->get(),
        ];
    }

    public function store(PengeluaranRequest $request)
    {
        $p = new Pengeluaran($request->safe()->except('foto_nota') + ['user_id' => $request->user()->id]);
        $p->gantiFoto($request->file('foto_nota'));
        $p->save();

        return response()->json(['message' => 'Pengeluaran dicatat.']);
    }

    public function update(PengeluaranRequest $request, Pengeluaran $pengeluaran)
    {
        $pengeluaran->fill($request->safe()->except('foto_nota'));
        $pengeluaran->gantiFoto($request->file('foto_nota'));
        $pengeluaran->save();

        return response()->json(['message' => 'Pengeluaran diperbarui.']);
    }

    public function destroy(Pengeluaran $pengeluaran)
    {
        $pengeluaran->delete();

        return response()->json(['message' => 'Pengeluaran dihapus.']);
    }

    public function kategoriStore(Request $request)
    {
        KategoriPengeluaran::create($request->validate(['nama' => ['required', 'string', 'max:50', 'unique:kategori_pengeluaran']]));

        return response()->json(['message' => 'Kategori ditambahkan.']);
    }

    public function kategoriUpdate(Request $request, KategoriPengeluaran $kategori)
    {
        $kategori->update($request->validate(['nama' => ['required', 'string', 'max:50', Rule::unique('kategori_pengeluaran')->ignore($kategori)]]));

        return response()->json(['message' => 'Kategori diperbarui.']);
    }

    public function kategoriDestroy(KategoriPengeluaran $kategori)
    {
        $kategori->delete();

        return response()->json(['message' => 'Kategori dihapus.']);
    }
}
