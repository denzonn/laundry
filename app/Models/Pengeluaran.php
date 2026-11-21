<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';

    protected $fillable = ['kategori_pengeluaran_id', 'user_id', 'tanggal', 'jumlah', 'keterangan', 'foto_nota'];

    protected $casts = ['jumlah' => 'integer', 'tanggal' => 'date:Y-m-d'];

    protected $appends = ['foto_url'];

    protected static function booted(): void
    {
        static::deleted(fn (self $p) => $p->foto_nota && Storage::disk('public')->delete($p->foto_nota));
    }

    /** Simpan foto nota baru ke disk public, hapus yang lama. */
    public function gantiFoto(?UploadedFile $file): void
    {
        if (! $file) {
            return;
        }

        $this->foto_nota && Storage::disk('public')->delete($this->foto_nota);
        $this->foto_nota = $file->store('nota', 'public');
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto_nota ? Storage::disk('public')->url($this->foto_nota) : null;
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriPengeluaran::class, 'kategori_pengeluaran_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
