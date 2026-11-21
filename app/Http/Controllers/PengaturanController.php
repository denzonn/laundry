<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengaturanController extends Controller
{
    public function edit()
    {
        return view('pengaturan.edit', ['toko' => Pengaturan::toko()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_toko' => ['required', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'footer_struk' => ['nullable', 'string', 'max:255'],
            'ukuran_struk' => ['required', 'in:58,80'],
            'template_wa_masuk' => ['nullable', 'string', 'max:1000'],
            'template_wa_selesai' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:1024'],
            'poin_aktif' => ['sometimes', 'boolean'],
            'rupiah_per_poin' => ['sometimes', 'required', 'integer', 'min:1000', 'max:10000000'],
            'nilai_poin' => ['sometimes', 'required', 'integer', 'min:1', 'max:1000000'],
        ], [], ['rupiah_per_poin' => 'belanja per poin', 'nilai_poin' => 'nilai 1 poin']);

        $toko = Pengaturan::toko();

        if ($request->hasFile('logo')) {
            $toko->logo && Storage::disk('public')->delete($toko->logo);
            $data['logo'] = $request->file('logo')->store('logo', 'public');
        }

        $toko->update($data);

        return back()->with('success', 'Pengaturan toko disimpan.');
    }
}
