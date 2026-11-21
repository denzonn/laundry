<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Order extends Model
{
    /** Urutan alur. 'batal' di luar alur. */
    public const ALUR = ['antrian', 'dicuci', 'disetrika', 'siap_diambil', 'diambil'];

    public const LABEL = [
        'antrian' => 'Antrian', 'dicuci' => 'Dicuci', 'disetrika' => 'Disetrika',
        'siap_diambil' => 'Siap Diambil', 'diambil' => 'Diambil', 'batal' => 'Batal',
    ];

    /** Warna badge (sesuai docs/ui-guidelines.md). Dipakai Blade & JS (window.WARNA_ORDER). */
    public const WARNA = [
        'antrian' => 'bg-slate-100 text-slate-700', 'dicuci' => 'bg-blue-100 text-blue-700',
        'disetrika' => 'bg-amber-100 text-amber-700', 'siap_diambil' => 'bg-emerald-100 text-emerald-700',
        'diambil' => 'bg-gray-100 text-gray-500', 'batal' => 'bg-red-100 text-red-700',
        'belum' => 'bg-red-100 text-red-700', 'dp' => 'bg-amber-100 text-amber-700', 'lunas' => 'bg-emerald-100 text-emerald-700',
    ];

    protected $fillable = [
        'kode', 'pelanggan_id', 'user_id', 'parfum_id', 'tanggal_masuk', 'estimasi_selesai', 'tanggal_diambil',
        'status', 'subtotal', 'diskon', 'biaya_antar', 'total', 'dibayar', 'status_bayar', 'catatan', 'alasan_batal',
    ];

    protected $casts = [
        'tanggal_masuk' => 'datetime', 'estimasi_selesai' => 'datetime', 'tanggal_diambil' => 'datetime',
        'subtotal' => 'integer', 'diskon' => 'integer', 'biaya_antar' => 'integer', 'total' => 'integer', 'dibayar' => 'integer',
    ];

    protected $appends = ['sisa'];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parfum(): BelongsTo
    {
        return $this->belongsTo(Parfum::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->latest('id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class)->latest('id');
    }

    public function getSisaAttribute(): int
    {
        return max(0, $this->total - $this->dibayar);
    }

    public static function statusBayar(int $dibayar, int $total): string
    {
        return match (true) {
            $dibayar >= $total => 'lunas',
            $dibayar > 0 => 'dp',
            default => 'belum',
        };
    }

    /** Status yang boleh dituju dari status sekarang (tanpa 'batal', itu aksi owner terpisah). */
    public function statusBerikutnya(): array
    {
        $i = array_search($this->status, self::ALUR, true);

        if ($i === false || $this->status === 'diambil') {
            return [];
        }

        // maju 1 langkah, atau lompat ke siap_diambil (layanan tanpa setrika)
        return array_values(array_unique([self::ALUR[$i + 1], ...($i < 3 ? ['siap_diambil'] : [])]));
    }

    public function ubahStatus(string $status, User $user, ?string $catatan = null): void
    {
        if ($status === 'batal') {
            throw_unless($user->isOwner(), ValidationException::withMessages(['status' => 'Hanya owner yang bisa membatalkan order.']));
            throw_if(in_array($this->status, ['diambil', 'batal'], true), ValidationException::withMessages(['status' => 'Order ini sudah tidak bisa dibatalkan.']));
            throw_unless(filled($catatan), ValidationException::withMessages(['catatan' => 'Alasan batal wajib diisi.']));
            $this->alasan_batal = $catatan;
        } else {
            throw_unless(in_array($status, $this->statusBerikutnya(), true),
                ValidationException::withMessages(['status' => 'Perubahan status dari '.self::LABEL[$this->status].' ke '.(self::LABEL[$status] ?? $status).' tidak diizinkan.']));
            throw_if($status === 'diambil' && $this->status_bayar !== 'lunas',
                ValidationException::withMessages(['status' => 'Order harus lunas sebelum diambil.']));
        }

        $this->status = $status;
        $this->tanggal_diambil = $status === 'diambil' ? now() : $this->tanggal_diambil;
        $this->save();

        $this->logs()->create(['user_id' => $user->id, 'status' => $status, 'catatan' => $catatan]);
    }

    public function bayar(int $jumlah, int $metodeId, User $user, ?string $catatan = null): Pembayaran
    {
        throw_if($this->status === 'batal', ValidationException::withMessages(['jumlah' => 'Order batal tidak bisa menerima pembayaran.']));
        throw_if($jumlah < 1 || $jumlah > $this->sisa, ValidationException::withMessages([
            'jumlah' => $this->sisa ? 'Jumlah bayar harus antara 1 dan sisa tagihan ('.number_format($this->sisa, 0, ',', '.').').' : 'Order sudah lunas.',
        ]));

        $bayar = $this->pembayaran()->create([
            'metode_pembayaran_id' => $metodeId, 'user_id' => $user->id,
            'jumlah' => $jumlah, 'tanggal' => now(), 'catatan' => $catatan,
        ]);

        $this->dibayar += $jumlah;
        $this->status_bayar = self::statusBayar($this->dibayar, $this->total);
        $this->save();

        return $bayar;
    }

    /** Pesan WA dari template pengaturan ('masuk' | 'selesai'), placeholder diganti. Butuh relasi pelanggan. */
    public function pesanWa(string $jenis): string
    {
        $rp = fn (int $v) => 'Rp '.number_format($v, 0, ',', '.');

        return strtr((string) Pengaturan::toko()->{'template_wa_'.$jenis}, [
            '{nama}' => $this->pelanggan->nama,
            '{kode}' => $this->kode,
            '{total}' => $rp($this->total),
            '{sisa}' => $rp($this->sisa),
            '{estimasi}' => $this->estimasi_selesai->translatedFormat('d M Y H:i'),
            '{link}' => route('lacak.show', $this->kode),
        ]);
    }

    public function waUrl(string $jenis): string
    {
        return 'https://wa.me/'.$this->pelanggan->no_hp.'?text='.rawurlencode($this->pesanWa($jenis));
    }

    public static function kodeBaru(): string
    {
        $prefix = 'LND-'.now()->format('ymd').'-';
        $last = static::where('kode', 'like', "$prefix%")->lockForUpdate()->max('kode');

        return $prefix.str_pad((int) substr((string) $last, -4) + 1, 4, '0', STR_PAD_LEFT);
    }
}
