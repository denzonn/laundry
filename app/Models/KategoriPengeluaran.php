<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriPengeluaran extends Model
{
    protected $table = 'kategori_pengeluaran';

    protected $fillable = ['nama'];

    protected $appends = ['warna'];

    /** Warna titik kategori (hex, bukan kelas Tailwind agar tidak perlu safelist), dipilih dari id. */
    public const WARNA = ['#0ea5e9', '#f59e0b', '#8b5cf6', '#10b981', '#ef4444', '#ec4899', '#64748b', '#14b8a6', '#f97316', '#6366f1'];

    public const DEFAULT = ['Deterjen & Bahan', 'Listrik & Air', 'Gaji', 'Sewa', 'Perawatan Mesin', 'Plastik & Kemasan', 'Lain-lain'];

    public static function warnaUntuk(int $id): string
    {
        return self::WARNA[($id - 1) % count(self::WARNA)];
    }

    public function getWarnaAttribute(): string
    {
        return self::warnaUntuk($this->id);
    }

    public function pengeluaran(): HasMany
    {
        return $this->hasMany(Pengeluaran::class);
    }
}
