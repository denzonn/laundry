{{-- Halaman publik "download" aplikasi = pasang PWA ke layar utama (Android/iOS/desktop). --}}
@php $toko = \App\Models\Pengaturan::toko(); @endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Download Aplikasi - {{ $toko->nama_toko }}</title>
    @include('includes.style')
    @include('includes.pwa')
</head>

<body class="min-h-[100dvh] bg-background font-poppins text-slate-800 antialiased">
    {{-- HERO --}}
    <header class="relative overflow-hidden bg-gradient-to-br from-primary_dark via-primary to-sky-700 px-5 pb-28 pt-10 text-white">
        <span class="absolute -right-12 -top-12 h-44 w-44 rounded-full border border-white/15 bg-white/5"></span>
        <span class="absolute right-20 top-28 h-8 w-8 rounded-full border border-white/25"></span>
        <span class="absolute -bottom-20 -left-10 h-56 w-56 rounded-full bg-sky-400/20 blur-3xl"></span>

        <div class="relative mx-auto max-w-3xl text-center">
            <img src="{{ asset('icons/icon-192.png') }}" alt="" class="mx-auto h-24 w-24 rounded-[28px] shadow-2xl shadow-black/30 ring-4 ring-white/20">
            <h1 class="mt-5 text-2xl font-bold md:text-3xl">{{ $toko->nama_toko }}</h1>
            <p class="mt-1 text-sm text-sky-100/80">Aplikasi kasir laundry · Android, iPhone & komputer</p>
            <div class="mt-4 flex flex-wrap justify-center gap-2 text-xs">
                <span class="rounded-full bg-white/10 px-3 py-1.5 ring-1 ring-white/15"><i class="fa-solid fa-feather mr-1"></i>Ringan, &lt; 1 MB</span>
                <span class="rounded-full bg-white/10 px-3 py-1.5 ring-1 ring-white/15"><i class="fa-solid fa-store-slash mr-1"></i>Tanpa Play Store</span>
                <span class="rounded-full bg-white/10 px-3 py-1.5 ring-1 ring-white/15"><i class="fa-solid fa-rotate mr-1"></i>Selalu versi terbaru</span>
            </div>
        </div>
    </header>

    <main class="relative mx-auto -mt-20 max-w-3xl px-4 pb-12">
        <div class="grid gap-4 md:grid-cols-5">

            {{-- KARTU PASANG --}}
            <section class="rounded-3xl bg-white p-5 shadow-xl shadow-primary/10 ring-1 ring-slate-100 md:col-span-3 md:p-6">

                {{-- sudah terpasang --}}
                <div data-state="installed" class="hidden text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-2xl text-primary"><i class="fa-solid fa-circle-check"></i></span>
                    <h2 class="mt-3 text-lg font-bold">Aplikasi sudah terpasang</h2>
                    <p class="mt-1 text-sm text-slate-500">Buka dari ikon <b>{{ $toko->nama_toko }}</b> di layar utama.</p>
                    <a href="{{ route('dashboard') }}" class="mt-5 flex h-12 items-center justify-center rounded-xl bg-primary font-semibold text-white hover:bg-primary_hover">Buka Aplikasi</a>
                </div>

                {{-- tombol pasang (Chrome/Edge Android & desktop) --}}
                <div data-state="prompt" class="hidden">
                    <h2 class="text-lg font-bold">Pasang dalam 1 tap</h2>
                    <p class="mt-1 text-sm text-slate-500">Ikon aplikasi akan muncul di layar utama dan terbuka layar penuh seperti aplikasi biasa.</p>
                    <button type="button" id="btnInstall"
                        class="mt-5 flex h-14 w-full items-center justify-center gap-3 rounded-2xl bg-primary text-base font-semibold text-white shadow-lg shadow-primary/30 transition hover:bg-primary_hover active:scale-[0.98]">
                        <i class="fa-solid fa-download"></i> Download Aplikasi
                    </button>
                </div>

                {{-- iPhone / iPad (Safari tidak punya tombol pasang) --}}
                <div data-state="ios" class="hidden">
                    <h2 class="text-lg font-bold">Pasang di iPhone / iPad</h2>
                    <p class="mt-1 text-sm text-slate-500">Buka halaman ini di <b>Safari</b>, lalu:</p>
                    <ol class="mt-4 space-y-3 text-sm">
                        <li class="flex items-center gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"><i class="fa-solid fa-arrow-up-from-bracket"></i></span>Tap tombol <b>Bagikan</b> di bawah layar</li>
                        <li class="flex items-center gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"><i class="fa-regular fa-square-plus"></i></span>Pilih <b>Tambah ke Layar Utama</b></li>
                        <li class="flex items-center gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"><i class="fa-solid fa-check"></i></span>Tap <b>Tambah</b> di pojok kanan atas</li>
                    </ol>
                </div>

                {{-- manual (prompt tidak muncul: browser lain / bukan HTTPS / sudah ditolak) --}}
                <div data-state="manual">
                    <h2 class="text-lg font-bold">Pasang lewat menu browser</h2>
                    <p class="mt-1 text-sm text-slate-500">Buka halaman ini di <b>Chrome</b> (Android) atau Chrome/Edge (komputer), lalu:</p>
                    <ol class="mt-4 space-y-3 text-sm">
                        <li class="flex items-center gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"><i class="fa-solid fa-ellipsis-vertical"></i></span>Tap menu <b>⋮</b> di pojok kanan atas</li>
                        <li class="flex items-center gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"><i class="fa-solid fa-mobile-screen-button"></i></span>Pilih <b>Instal aplikasi</b> / <b>Tambahkan ke layar utama</b></li>
                        <li class="flex items-center gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"><i class="fa-solid fa-check"></i></span>Konfirmasi <b>Instal</b></li>
                    </ol>
                </div>
            </section>

            {{-- QR: buka di HP dari komputer --}}
            <section class="hidden rounded-3xl bg-white p-6 text-center ring-1 ring-slate-100 md:col-span-2 md:block">
                <p class="text-sm font-semibold">Scan dengan kamera HP</p>
                <div id="qr" class="mx-auto mt-4 flex h-40 w-40 items-center justify-center rounded-2xl bg-white p-2 ring-1 ring-slate-100"></div>
                <p class="mt-3 break-all text-xs text-slate-400">{{ route('download') }}</p>
            </section>
        </div>

        {{-- KEUNTUNGAN --}}
        <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4">
            @foreach ([['fa-bolt', 'Buka cepat', 'langsung dari layar utama'], ['fa-expand', 'Layar penuh', 'tanpa bar browser'], ['fa-print', 'Cetak struk', 'printer thermal'], ['fa-brands fa-whatsapp', 'Nota WA', 'kirim ke pelanggan']] as [$ikon, $judul, $ket])
                <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-100">
                    <i class="{{ str_contains($ikon, 'fa-brands') ? $ikon : 'fa-solid ' . $ikon }} text-lg text-primary"></i>
                    <p class="mt-2 text-sm font-semibold">{{ $judul }}</p>
                    <p class="text-xs text-slate-400">{{ $ket }}</p>
                </div>
            @endforeach
        </div>

        <p class="mt-8 text-center text-sm text-slate-500">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-primary">Masuk</a>
            · <a href="{{ route('lacak') }}" class="font-semibold text-primary">Lacak cucian</a>
        </p>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        const tampil = (s) => document.querySelectorAll('[data-state]').forEach((el) => el.classList.toggle('hidden', el.dataset.state !== s));
        const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        const terpasang = () => matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
        let promptEvt = null;

        tampil(terpasang() ? 'installed' : isIos ? 'ios' : 'manual');

        // Chrome/Edge memicu event ini bila syarat PWA terpenuhi (HTTPS/localhost + manifest + service worker)
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            promptEvt = e;
            tampil('prompt');
        });
        window.addEventListener('appinstalled', () => tampil('installed'));

        document.getElementById('btnInstall').addEventListener('click', async () => {
            if (!promptEvt) return tampil('manual');
            promptEvt.prompt();
            const { outcome } = await promptEvt.userChoice;
            promptEvt = null;
            tampil(outcome === 'accepted' ? 'installed' : 'manual');
        });

        if (window.QRCode) new QRCode(document.getElementById('qr'), { text: @json(route('download')), width: 144, height: 144, colorDark: '#0F4C81' });
    </script>
</body>

</html>
