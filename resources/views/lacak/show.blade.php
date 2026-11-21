<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Lacak Laundry - {{ $toko->nama_toko }}</title>
    @include('includes.style')
    @include('includes.pwa')
</head>

@use('App\Models\Order')

@php
    $ikon = ['antrian' => 'fa-hourglass-start', 'dicuci' => 'fa-soap', 'disetrika' => 'fa-shirt', 'siap_diambil' => 'fa-box-open', 'diambil' => 'fa-circle-check'];
    $ket = [
        'antrian' => 'Cucian sudah kami terima', 'dicuci' => 'Sedang dicuci & dikeringkan', 'disetrika' => 'Sedang disetrika & dilipat',
        'siap_diambil' => 'Sudah bisa diambil di toko', 'diambil' => 'Sudah diambil. Terima kasih!',
    ];
    $posisi = $order ? array_search($order->status, Order::ALUR, true) : false;
@endphp

<body class="min-h-screen bg-gradient-to-b from-sky-500 via-sky-100 to-white font-poppins text-slate-800">
    <main class="mx-auto max-w-md px-4 pb-10 pt-8">
        {{-- toko --}}
        <div class="flex items-center gap-3 text-white">
            @if ($toko->logo)
                <img src="{{ asset('storage/' . $toko->logo) }}" alt="" class="h-11 w-11 rounded-full bg-white object-contain p-1">
            @else
                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white/20"><i class="fa-solid fa-shirt"></i></span>
            @endif
            <div>
                <p class="text-xs uppercase tracking-widest text-sky-100">Lacak Laundry</p>
                <h1 class="text-lg font-bold">{{ $toko->nama_toko }}</h1>
            </div>
        </div>

        {{-- form kode --}}
        <form action="{{ route('lacak') }}" method="GET" class="mt-6 flex gap-2 rounded-full bg-white p-1.5 shadow-lg shadow-sky-900/10">
            <input name="kode" value="{{ $order->kode ?? $kode }}" placeholder="Kode order, mis. LND-..." required autocomplete="off"
                class="h-11 min-w-0 flex-1 rounded-full border-0 px-4 font-mono text-base uppercase focus:ring-2 focus:ring-sky-400">
            <button class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-sky-600 text-white hover:bg-sky-700" aria-label="Cari">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>

        @if ($order)
            <section class="mt-6 overflow-hidden rounded-3xl bg-white shadow-xl shadow-sky-900/10">
                <div class="px-5 pt-5">
                    <p class="font-mono text-xs font-semibold text-slate-400">{{ $order->kode }}</p>
                    <h2 class="text-xl font-bold">Halo, {{ mb_substr($order->pelanggan->nama, 0, 2) }}***</h2>
                    @if ($order->status === 'batal')
                        <p class="mt-3 rounded-2xl bg-red-50 p-3 text-sm text-red-700"><i class="fa-solid fa-ban mr-1"></i> Order ini dibatalkan. Hubungi toko untuk info lebih lanjut.</p>
                    @elseif ($order->status !== 'diambil')
                        <p class="mt-1 text-sm text-slate-500">Estimasi selesai <b class="text-slate-700">{{ $order->estimasi_selesai->translatedFormat('l, d M Y H:i') }}</b></p>
                    @endif
                </div>

                @unless ($order->status === 'batal')
                    {{-- stepper vertikal --}}
                    <ol class="mt-5 px-5">
                        @foreach (Order::ALUR as $i => $s)
                            @php $done = $i <= $posisi; $now = $i === $posisi; @endphp
                            <li class="relative flex gap-4 pb-5 last:pb-0">
                                @unless ($loop->last)
                                    <span class="absolute left-5 top-10 h-full w-0.5 -translate-x-1/2 {{ $i < $posisi ? 'bg-sky-500' : 'bg-slate-200' }}"></span>
                                @endunless
                                <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full
                                    {{ $now ? 'bg-sky-600 text-white ring-4 ring-sky-200' : ($done ? 'bg-sky-500 text-white' : 'bg-slate-100 text-slate-300') }}">
                                    <i class="fa-solid {{ $ikon[$s] }} text-sm"></i>
                                </span>
                                <div class="pt-1">
                                    <p class="font-semibold {{ $done ? '' : 'text-slate-400' }}">{{ Order::LABEL[$s] }}</p>
                                    @if ($now)
                                        <p class="text-sm text-sky-700">{{ $ket[$s] }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endunless

                <div class="mt-5 flex items-center justify-between border-t border-dashed border-slate-200 bg-slate-50 px-5 py-4">
                    <div>
                        <p class="text-xs text-slate-400">Total tagihan</p>
                        <p class="text-lg font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1.5 text-sm font-semibold {{ Order::WARNA[$order->status_bayar] }}">
                        {{ ['belum' => 'Belum Bayar', 'dp' => 'DP · sisa Rp ' . number_format($order->sisa, 0, ',', '.'), 'lunas' => 'Lunas'][$order->status_bayar] }}
                    </span>
                </div>
            </section>
        @elseif ($kode)
            <section class="mt-6 rounded-3xl bg-white px-6 py-10 text-center shadow-xl shadow-sky-900/10">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-sky-50 text-sky-400"><i class="fa-solid fa-shirt text-2xl"></i></span>
                <h2 class="mt-4 font-bold">Order tidak ditemukan</h2>
                <p class="mt-1 text-sm text-slate-500">Kode <span class="font-mono font-semibold">{{ $kode }}</span> tidak ada. Cek lagi kode di struk Anda.</p>
            </section>
        @else
            <section class="mt-6 rounded-3xl bg-white/80 px-6 py-8 text-center text-sm text-slate-500 backdrop-blur">
                <i class="fa-solid fa-receipt text-3xl text-sky-300"></i>
                <p class="mt-3">Masukkan kode order yang tertera di struk atau pesan WhatsApp untuk melihat status cucian Anda.</p>
            </section>
        @endif

        @if ($toko->no_hp)
            <a href="https://wa.me/{{ \App\Models\Pelanggan::normalisasiHp($toko->no_hp) }}" target="_blank"
                class="mt-6 flex h-12 items-center justify-center gap-2 rounded-full border border-sky-200 bg-white text-sm font-semibold text-sky-700">
                <i class="fa-brands fa-whatsapp text-lg text-green-600"></i> Tanya toko
            </a>
        @endif
    </main>
</body>

</html>
