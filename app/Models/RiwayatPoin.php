<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPoin extends Model
{
    protected $table = 'riwayat_poin';

    protected $fillable = ['pelanggan_id', 'order_id', 'jenis', 'poin', 'saldo_setelah', 'keterangan'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
