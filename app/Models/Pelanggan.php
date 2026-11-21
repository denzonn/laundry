<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    protected $fillable = ['nama', 'no_hp', 'alamat', 'catatan'];

    protected $casts = ['poin' => 'integer'];

    /** "0812-3456 789" / "+62 812..." → "628123456789" (format wa.me, dan agar unik konsisten). */
    public static function normalisasiHp(?string $hp): string
    {
        $hp = preg_replace('/\D/', '', (string) $hp);

        return str_starts_with($hp, '0') ? '62'.substr($hp, 1) : $hp;
    }

    /** Cari by nama atau no HP. "0812" dicari sebagai "62812"; kata tanpa angka tidak mencocokkan no HP. */
    public function scopeCari($query, ?string $s)
    {
        if (blank($s)) {
            return $query;
        }

        $hp = preg_match('/\d/', $s) ? static::normalisasiHp($s) : null;

        return $query->where(fn ($q) => $q->where('nama', 'like', "%$s%")
            ->when($hp, fn ($q) => $q->orWhere('no_hp', 'like', "%$hp%")));
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function riwayatPoin()
    {
        return $this->hasMany(RiwayatPoin::class)->latest('id');
    }

    /**
     * Satu-satunya jalan mengubah saldo poin (lock + catat di riwayat_poin). Panggil di dalam transaksi.
     * $poin bertanda: 'dapat' & 'batal' (refund) positif, 'tukar' negatif.
     */
    public function mutasiPoin(string $jenis, int $poin, ?Order $order = null, ?string $keterangan = null): void
    {
        $p = static::lockForUpdate()->findOrFail($this->id);
        throw_if($p->poin + $poin < 0, ValidationException::withMessages(['poin_ditukar' => "Poin pelanggan tidak cukup (sisa {$p->poin})."]));

        $p->poin += $poin;
        $p->save();
        $p->riwayatPoin()->create([
            'order_id' => $order?->id, 'jenis' => $jenis, 'poin' => $poin, 'saldo_setelah' => $p->poin, 'keterangan' => $keterangan,
        ]);
        $this->poin = $p->poin;
    }

    public function setNoHpAttribute(?string $value): void
    {
        $this->attributes['no_hp'] = static::normalisasiHp($value);
    }
}
