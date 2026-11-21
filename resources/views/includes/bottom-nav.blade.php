{{-- Navigasi bawah khusus HP/tablet (lg ke atas pakai sidebar). Sembunyikan per halaman dengan @section('no_bottom_nav', true). --}}
@php
    $nav = [
        ['dashboard', 'Beranda', 'fa-house'],
        ['order', 'Order', 'fa-receipt'],
        ['order.create', 'Baru', 'fa-plus'],
        ['pelanggan', 'Pelanggan', 'fa-users'],
    ];
@endphp

<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 backdrop-blur lg:hidden print:hidden"
    style="padding-bottom: env(safe-area-inset-bottom)">
    <div class="mx-auto grid h-16 max-w-lg grid-cols-4">
        @foreach ($nav as [$route, $label, $icon])
            @if ($route === 'order.create')
                <a href="{{ route($route) }}" class="flex flex-col items-center justify-center">
                    <span class="-mt-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary text-white shadow-lg shadow-primary/30">
                        <i class="fa-solid {{ $icon }} text-xl"></i>
                    </span>
                    <span class="mt-0.5 text-[11px] font-semibold text-primary">{{ $label }}</span>
                </a>
            @else
                <a href="{{ route($route) }}"
                    class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium {{ request()->routeIs($route) ? 'text-primary' : 'text-slate-400' }}">
                    <i class="fa-solid {{ $icon }} text-lg"></i>{{ $label }}
                </a>
            @endif
        @endforeach
    </div>
</nav>
