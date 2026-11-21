<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PengeluaranRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'kategori_pengeluaran_id' => ['required', 'integer', 'exists:kategori_pengeluaran,id'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'foto_nota' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return ['kategori_pengeluaran_id' => 'kategori', 'foto_nota' => 'foto nota'];
    }
}
