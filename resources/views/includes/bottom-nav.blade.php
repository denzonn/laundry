{{-- Navigasi bawah khusus HP/tablet (lg ke atas pakai sidebar). Sembunyikan per halaman dengan @section('no_bottom_nav', true). --}}
@php
    $kurir = auth()->user()->isKurir();
    $nav = $kurir
        ? [['tugas', 'Tugas', 'fa-route'], ['profil', 'Profil', 'fa-user']]
        : [
            ['dashboard', 'Beranda', 'fa-house'],
            ['order', 'Order', 'fa-receipt'],
            ['order.create', 'Baru', 'fa-plus'],
            ['pelanggan', 'Pelanggan', 'fa-users'],
            [null, 'Menu', 'fa-grip'],
        ];
@endphp

<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200/80 bg-white/95 shadow-[0_-8px_24px_-12px_rgba(15,76,129,0.18)] backdrop-blur lg:hidden print:hidden"
    style="padding-bottom: env(safe-area-inset-bottom)">
    <div class="mx-auto grid h-16 max-w-xl {{ $kurir ? 'grid-cols-2' : 'grid-cols-5' }}">
        @foreach ($nav as [$route, $label, $icon])
            @if ($route === 'order.create')
                <a href="{{ route($route) }}" class="flex flex-col items-center justify-center" aria-label="Order Baru">
                    <span class="-mt-7 flex h-14 w-14 items-center justify-center rounded-full bg-primary text-white shadow-lg shadow-primary/40 ring-4 ring-white transition active:scale-95">
                        <i class="fa-solid {{ $icon }} text-xl"></i>
                    </span>
                    <span class="mt-0.5 text-[11px] font-semibold text-primary">{{ $label }}</span>
                </a>
            @elseif ($route === null)
                <button type="button" data-open-sidebar class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium text-slate-400 active:text-primary">
                    <i class="fa-solid {{ $icon }} text-lg"></i>{{ $label }}
                </button>
            @else
                @php $aktif = request()->routeIs($route); @endphp
                <a href="{{ route($route) }}"
                    class="relative flex flex-col items-center justify-center gap-1 text-[11px] font-medium {{ $aktif ? 'text-primary' : 'text-slate-400 active:text-primary' }}">
                    @if ($aktif)
                        <span class="absolute top-0 h-1 w-8 rounded-b-full bg-primary"></span>
                    @endif
                    <i class="fa-solid {{ $icon }} text-lg"></i>{{ $label }}
                </a>
            @endif
        @endforeach
    </div>
</nav>
