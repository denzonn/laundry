<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    protected $fillable = ['nama', 'no_hp', 'alamat', 'catatan'];

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

    public function setNoHpAttribute(?string $value): void
    {
        $this->attributes['no_hp'] = static::normalisasiHp($value);
    }
}
