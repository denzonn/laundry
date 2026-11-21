<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $fillable = [
        'nama_toko', 'alamat', 'no_hp', 'logo', 'footer_struk',
        'ukuran_struk', 'template_wa_masuk', 'template_wa_selesai',
        'poin_aktif', 'rupiah_per_poin', 'nilai_poin',
    ];

    protected $casts = ['poin_aktif' => 'boolean', 'rupiah_per_poin' => 'integer', 'nilai_poin' => 'integer'];

    /** Satu-satunya baris pengaturan toko, dibuat otomatis bila belum ada. */
    public static function toko(): self
    {
        return once(fn () => static::firstOrCreate([], [
            'nama_toko' => config('app.name'),
            'footer_struk' => 'Terima kasih telah mempercayakan cucian Anda.',
            'template_wa_masuk' => "Halo {nama}, order laundry {kode} sudah kami terima.\nTotal: {total}\nEstimasi selesai: {estimasi}\nLacak: {link}",
            'template_wa_selesai' => "Halo {nama}, laundry {kode} sudah selesai dan siap diambil.\nSisa tagihan: {sisa}\nTerima kasih!",
        ]));
    }
}
