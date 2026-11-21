<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Pengiriman extends Model
{
    protected $table = 'pengiriman';

    /** Alur maju: status sekarang → status berikutnya. 'batal' aksi terpisah (owner/kasir). */
    public const ALUR = ['dijadwalkan' => 'dalam_perjalanan', 'dalam_perjalanan' => 'selesai'];

    public const LABEL = ['dijadwalkan' => 'Dijadwalkan', 'dalam_perjalanan' => 'Dalam Perjalanan', 'selesai' => 'Selesai', 'batal' => 'Batal'];

    public const WARNA = [
        'dijadwalkan' => 'bg-sky-100 text-sky-700', 'dalam_perjalanan' => 'bg-amber-100 text-amber-700',
        'selesai' => 'bg-emerald-100 text-emerald-700', 'batal' => 'bg-red-100 text-red-700',
    ];

    protected $fillable = ['order_id', 'jenis', 'kurir_id', 'alamat', 'jadwal', 'status', 'biaya', 'catatan', 'selesai_at'];

    protected $casts = ['jadwal' => 'datetime', 'selesai_at' => 'datetime', 'biaya' => 'integer'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function kurir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kurir_id');
    }

    public function scopeAktif($q)
    {
        return $q->whereIn('status', ['dijadwalkan', 'dalam_perjalanan']);
    }

    public function mapsUrl(): string
    {
        return 'https://www.google.com/maps/search/?api=1&query='.urlencode($this->alamat);
    }

    /** Hak akses (kurir hanya tugasnya sendiri) dicek di controller; di sini aturan transisinya. */
    public function ubahStatus(string $status, User $user): void
    {
        if ($status === 'batal') {
            throw_if($user->isKurir(), ValidationException::withMessages(['status' => 'Kurir tidak bisa membatalkan tugas. Hubungi kasir.']));
            throw_unless(isset(self::ALUR[$this->status]), ValidationException::withMessages(['status' => 'Pengiriman ini sudah tidak bisa dibatalkan.']));
        } else {
            throw_unless((self::ALUR[$this->status] ?? null) === $status, ValidationException::withMessages([
                'status' => 'Perubahan status dari '.self::LABEL[$this->status].' ke '.(self::LABEL[$status] ?? $status).' tidak diizinkan.',
            ]));
        }

        $this->status = $status;
        $this->selesai_at = $status === 'selesai' ? now() : $this->selesai_at;
        $this->save();
    }
}
