@php
    // Menu tampil otomatis begitu route-nya terdaftar (lihat docs/roadmap.md). Elemen ke-4 = true → khusus owner.
    $menus = [
        '' => [['dashboard', 'Dashboard', 'fa-house']],
        'Transaksi' => [
            ['order.create', 'Order Baru', 'fa-plus'],
            ['order', 'Daftar Order', 'fa-receipt'],
        ],
        'Master' => [
            ['pelanggan', 'Pelanggan', 'fa-users'],
            ['layanan', 'Layanan', 'fa-shirt', true],
            ['parfum', 'Parfum', 'fa-spray-can-sparkles', true],
            ['metode-pembayaran', 'Metode Bayar', 'fa-wallet', true],
        ],
        'Keuangan' => [['pengeluaran', 'Pengeluaran', 'fa-money-bill-wave', true]],
        'Gudang' => [['bahan', 'Stok Bahan', 'fa-boxes-stacked', true]],
        'Laporan' => [
            ['laporan.pendapatan', 'Pendapatan', 'fa-chart-line', true],
            ['laporan.order', 'Order', 'fa-chart-bar', true],
            ['laporan.piutang', 'Piutang', 'fa-file-invoice-dollar', true],
            ['laporan.laba-rugi', 'Laba Rugi', 'fa-scale-balanced', true],
        ],
        'Pengaturan' => [
            ['pengaturan', 'Toko', 'fa-store', true],
            ['user', 'User', 'fa-user-gear', true],
        ],
    ];
    $isOwner = auth()->user()->isOwner();
    $toko = \App\Models\Pengaturan::toko();
@endphp

<div class="flex flex-col h-full">

    {{-- LOGO --}}
    <div class="h-[72px] flex items-center px-6 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center">
                <img src="{{ $toko->logo ? asset('storage/' . $toko->logo) : asset('icons/icon-192.png') }}" class="max-h-10 rounded-lg" alt="">
            </div>
            <div>
                <h2 class="font-semibold text-slate-800">{{ $toko->nama_toko }}</h2>
                <p class="text-xs text-slate-400">Laundry Management</p>
            </div>
        </div>
    </div>

    {{-- MENU --}}
    <div class="flex-1 overflow-y-auto px-4 py-6 space-y-8">
        @foreach ($menus as $group => $items)
            @php $items = array_filter($items, fn($m) => Route::has($m[0]) && ($isOwner || empty($m[3]))); @endphp
            @continue(!$items)

            <div>
                @if ($group)
                    <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">{{ $group }}</p>
                @endif

                <div class="space-y-1">
                    @foreach ($items as [$route, $label, $icon])
                        <a href="{{ route($route) }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs($route) ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                            <i class="fa-solid {{ $icon }} w-5 text-center"></i>
                            <span class="font-medium">{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- LOGOUT --}}
    <div class="border-t border-slate-200 p-4">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button
                class="w-full rounded-xl py-3 border border-red-200 text-red-600 hover:bg-red-600 hover:text-white transition">
                <i class="fa-solid fa-right-from-bracket mr-2"></i>
                Logout
            </button>
        </form>
    </div>

</div>
