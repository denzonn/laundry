@php
    // Menu tampil otomatis begitu route-nya terdaftar (lihat docs/roadmap.md).
    // Elemen ke-4 = daftar role yang boleh melihat; tanpa elemen ke-4 = owner & kasir.
    $o = ['owner'];
    $menus = [
        '' => [
            ['dashboard', 'Dashboard', 'fa-house'],
            ['tugas', 'Tugas Saya', 'fa-route', ['kurir']],
            ['profil', 'Profil', 'fa-user', ['kurir']],
        ],
        'Transaksi' => [
            ['order.create', 'Order Baru', 'fa-plus'],
            ['order', 'Daftar Order', 'fa-receipt'],
            ['pengiriman', 'Antar Jemput', 'fa-motorcycle'],
        ],
        'Master' => [
            ['pelanggan', 'Pelanggan', 'fa-users'],
            ['layanan', 'Layanan', 'fa-shirt', $o],
            ['parfum', 'Parfum', 'fa-spray-can-sparkles', $o],
            ['metode-pembayaran', 'Metode Bayar', 'fa-wallet', $o],
        ],
        'Keuangan' => [['pengeluaran', 'Pengeluaran', 'fa-money-bill-wave', $o]],
        'Gudang' => [['bahan', 'Stok Bahan', 'fa-boxes-stacked', $o]],
        'Laporan' => [
            ['laporan.pendapatan', 'Pendapatan', 'fa-chart-line', $o],
            ['laporan.order', 'Order', 'fa-chart-bar', $o],
            ['laporan.piutang', 'Piutang', 'fa-file-invoice-dollar', $o],
            ['laporan.laba-rugi', 'Laba Rugi', 'fa-scale-balanced', $o],
        ],
        'Pengaturan' => [
            ['pengaturan', 'Toko', 'fa-store', $o],
            ['user', 'User', 'fa-user-gear', $o],
        ],
    ];
    $user = auth()->user();
    $toko = \App\Models\Pengaturan::toko();
@endphp

<div class="relative flex h-full flex-col overflow-hidden">
    {{-- aksen gelembung sabun --}}
    <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-sky-400/15 blur-3xl"></div>
    <div class="pointer-events-none absolute -left-10 bottom-24 h-40 w-40 rounded-full bg-sky-400/10 blur-3xl"></div>

    {{-- LOGO --}}
    <div class="relative flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5 lg:h-[72px]">
        <img src="{{ $toko->logo ? asset('storage/' . $toko->logo) : asset('icons/icon-192.png') }}" class="h-10 w-10 rounded-xl bg-white object-contain p-0.5" alt="">
        <div class="min-w-0 flex-1">
            <h2 class="truncate font-semibold leading-tight">{{ $toko->nama_toko }}</h2>
            <p class="text-xs text-sky-200/70">Laundry Management</p>
        </div>
        <button type="button" data-close-sidebar aria-label="Tutup menu"
            class="flex h-11 w-11 items-center justify-center rounded-xl text-white/70 hover:bg-white/10 lg:hidden">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    {{-- MENU --}}
    <nav class="relative flex-1 space-y-6 overflow-y-auto px-3 py-5 [scrollbar-width:thin]">
        @foreach ($menus as $group => $items)
            @php $items = array_filter($items, fn($m) => Route::has($m[0]) && in_array($user->role, $m[3] ?? ['owner', 'kasir'], true)); @endphp
            @continue(!$items)

            <div>
                @if ($group)
                    <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-sky-200/50">{{ $group }}</p>
                @endif

                <div class="space-y-0.5">
                    @foreach ($items as [$route, $label, $icon])
                        @php $aktif = request()->routeIs($route); @endphp
                        <a href="{{ route($route) }}"
                            class="group relative flex h-11 items-center gap-3 rounded-xl px-3 text-sm transition
                                {{ $aktif ? 'bg-white/10 font-semibold text-white' : 'text-sky-100/75 hover:bg-white/5 hover:text-white' }}">
                            @if ($aktif)
                                <span class="absolute inset-y-2 left-0 w-1 rounded-r-full bg-sky-300"></span>
                            @endif
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $aktif ? 'bg-white text-primary shadow-lg' : 'bg-white/5 group-hover:bg-white/10' }}">
                                <i class="fa-solid {{ $icon }} text-[13px]"></i>
                            </span>
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    {{-- USER + LOGOUT --}}
    <div class="relative shrink-0 border-t border-white/10 p-3" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))">
        <div class="flex items-center gap-3 rounded-2xl bg-white/5 p-2">
            <a href="{{ route('profil') }}" class="flex min-w-0 flex-1 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white/15 font-semibold text-white">
                    @if ($user->foto_url)
                        <img src="{{ $user->foto_url }}" class="h-full w-full object-cover" alt="">
                    @else
                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                    @endif
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold">{{ $user->name }}</span>
                    <span class="block text-xs capitalize text-sky-200/60">{{ $user->role }}</span>
                </span>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button aria-label="Logout" title="Logout"
                    class="flex h-11 w-11 items-center justify-center rounded-xl text-sky-100/70 hover:bg-red-500/20 hover:text-red-200">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </div>
</div>
