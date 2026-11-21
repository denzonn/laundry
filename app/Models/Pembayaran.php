<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = ['metode_pembayaran_id', 'user_id', 'jumlah', 'tanggal', 'catatan'];

    protected $casts = ['jumlah' => 'integer', 'tanggal' => 'datetime'];

    public function metode(): BelongsTo
    {
        return $this->belongsTo(MetodePembayaran::class, 'metode_pembayaran_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
