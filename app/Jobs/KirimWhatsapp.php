<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/** Kirim pesan WA order ('masuk' | 'selesai') lewat Fonnte. Pesan dari Order::pesanWa(). */
class KirimWhatsapp implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public int $orderId, public string $jenis) {}

    public static function aktif(): bool
    {
        return config('services.whatsapp.enabled') && filled(config('services.whatsapp.token'));
    }

    public function handle(): void
    {
        $order = Order::with('pelanggan')->find($this->orderId);

        if (! self::aktif() || ! $order) {
            return;
        }

        $res = Http::withHeaders(['Authorization' => config('services.whatsapp.token')])
            ->asForm()->timeout(20)
            ->post(config('services.whatsapp.url'), [
                'target' => $order->pelanggan->no_hp,
                'message' => $order->pesanWa($this->jenis),
            ])
            ->throw();

        // Fonnte membalas 200 + {"status": false, "reason": ...} bila ditolak (token salah, kuota habis)
        if ($res->json('status') === false) {
            throw new RuntimeException('Fonnte: '.$res->json('reason', 'ditolak'));
        }
    }

    public function failed(Throwable $e): void
    {
        Log::warning("Kirim WA {$this->jenis} order #{$this->orderId} gagal: {$e->getMessage()}");
    }
}
