<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Bahan extends Model
{
    protected $table = 'bahan';

    public const JENIS = ['masuk' => 'Masuk', 'keluar' => 'Pakai', 'penyesuaian' => 'Stok opname'];

    public const SATUAN = ['liter', 'kg', 'pcs', 'pak', 'botol', 'roll'];

    /** Bahan default (seeder). [nama, satuan, stok_minimum] */
    public const DEFAULT = [
        ['Deterjen cair', 'liter', 5], ['Pewangi', 'liter', 3], ['Plastik', 'pak', 2], ['Hanger', 'pcs', 50],
    ];

    protected $fillable = ['nama', 'satuan', 'stok', 'stok_minimum'];

    protected $casts = ['stok' => 'float', 'stok_minimum' => 'float'];

    protected $appends = ['menipis'];

    public function riwayat(): HasMany
    {
        return $this->hasMany(MutasiBahan::class)->latest('id');
    }

    /** Stok sudah di batas minimum atau di bawahnya. */
    public function scopeMenipis($q)
    {
        return $q->whereColumn('stok', '<=', 'stok_minimum');
    }

    public function getMenipisAttribute(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }

    /**
     * Catat mutasi stok. masuk = tambah, keluar = kurangi (tidak boleh minus),
     * penyesuaian = stok diset ke $jumlah (hasil hitung fisik).
     */
    public function mutasi(string $jenis, float $jumlah, User $user, ?string $keterangan = null): MutasiBahan
    {
        throw_unless(isset(self::JENIS[$jenis]), ValidationException::withMessages(['jenis' => 'Jenis mutasi tidak dikenal.']));
        throw_if($jumlah < 0 || ($jenis !== 'penyesuaian' && $jumlah <= 0),
            ValidationException::withMessages(['jumlah' => 'Jumlah harus lebih dari 0.']));

        return DB::transaction(function () use ($jenis, $jumlah, $user, $keterangan) {
            $bahan = static::lockForUpdate()->findOrFail($this->id);

            $stok = round(match ($jenis) {
                'masuk' => $bahan->stok + $jumlah,
                'keluar' => $bahan->stok - $jumlah,
                'penyesuaian' => $jumlah,
            }, 2);

            throw_if($stok < 0, ValidationException::withMessages([
                'jumlah' => 'Stok tidak cukup. Sisa '.self::angka($bahan->stok).' '.$bahan->satuan.'.',
            ]));

            $bahan->update(['stok' => $stok]);
            $this->stok = $stok;

            return $bahan->riwayat()->create([
                'user_id' => $user->id, 'jenis' => $jenis, 'jumlah' => $jumlah, 'stok_setelah' => $stok, 'keterangan' => $keterangan,
            ]);
        });
    }

    /** 12.5 → "12,5", 3.00 → "3" */
    public static function angka(float $v): string
    {
        return rtrim(rtrim(number_format($v, 2, ',', '.'), '0'), ',');
    }
}
