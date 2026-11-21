<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'pelanggan_id' => ['required', 'exists:pelanggan,id'],
            'parfum_id' => ['nullable', Rule::exists('parfum', 'id')->where('aktif', true)],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.layanan_id' => ['required', 'distinct', Rule::exists('layanan', 'id')->where('aktif', true)],
            'items.*.qty' => ['required', 'numeric', 'min:0.1', 'max:999'],
            'diskon' => ['nullable', 'integer', 'min:0'],
            'biaya_antar' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'estimasi_selesai' => ['nullable', 'date', 'after:now'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'bayar' => ['nullable', 'integer', 'min:0'],
            'metode_pembayaran_id' => ['nullable', Rule::exists('metode_pembayaran', 'id')->where('aktif', true)],
        ];
    }

    public function attributes(): array
    {
        return [
            'pelanggan_id' => 'pelanggan', 'items' => 'layanan', 'items.*.layanan_id' => 'layanan',
            'items.*.qty' => 'jumlah', 'metode_pembayaran_id' => 'metode pembayaran', 'estimasi_selesai' => 'estimasi selesai',
        ];
    }

    public function messages(): array
    {
        return ['items.required' => 'Pilih minimal 1 layanan.', 'items.*.layanan_id.distinct' => 'Layanan yang sama dipilih dua kali.'];
    }
}
