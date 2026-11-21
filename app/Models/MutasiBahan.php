<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiBahan extends Model
{
    protected $table = 'mutasi_bahan';

    protected $fillable = ['bahan_id', 'user_id', 'jenis', 'jumlah', 'stok_setelah', 'keterangan'];

    protected $casts = ['jumlah' => 'float', 'stok_setelah' => 'float'];

    public function bahan(): BelongsTo
    {
        return $this->belongsTo(Bahan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
