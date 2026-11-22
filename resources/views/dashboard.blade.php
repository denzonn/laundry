@extends('layouts.app')

@section('title', 'Dashboard - ' . config('app.name'))
@section('page_title', 'Beranda')
@section('page_description', 'Ringkasan laundry hari ini')

@php
    $rp = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
    // label pendek di atas batang grafik: 125rb, 1,2jt
    $singkat = fn($v) => $v >= 1e6 ? rtrim(rtrim(number_format($v / 1e6, 1, ',', '.'), '0'), ',') . 'jt' : ($v >= 1e3 ? round($v / 1e3) . 'rb' : (string) $v);
    $jam = now()->hour;
    $sapa = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
    $pipa = [
        'antrian' => ['Antrian', 'fa-hourglass-start', 'bg-slate-400'],
        'dicuci' => ['Dicuci', 'fa-soap', 'bg-blue-500'],
        'disetrika' => ['Setrika', 'fa-shirt', 'bg-amber-500'],
        'siap_diambil' => ['Siap', 'fa-box-open', 'bg-emerald-500'],
    ];
    $isOwner = auth()->user()->isOwner();
@endphp

@section('content')
    {{-- HERO SAPAAN + ANGKA UTAMA --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary_dark via-primary to-sky-700 p-5 text-white shadow-lg shadow-primary/20 md:p-6">
        <span class="pointer-events-none absolute -right-8 -top-10 h-36 w-36 rounded-full border border-white/15 bg-white/5"></span>
        <span class="pointer-events-none absolute right-24 top-6 h-8 w-8 rounded-full border border-white/20"></span>
        <span class="pointer-events-none absolute -bottom-16 left-1/3 h-40 w-40 rounded-full bg-sky-400/20 blur-3xl"></span>

        <div class="relative">
            <p class="text-xs text-sky-100/80">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h2 class="truncate text-2xl font-bold">{{ $sapa }}, {{ strtok(auth()->user()->name, ' ') }} 👋</h2>

            <div class="mt-5 grid grid-cols-2 gap-2.5 lg:grid-cols-4 lg:gap-3">
                <div class="rounded-2xl bg-white/10 p-3.5 ring-1 ring-white/10 backdrop-blur">
                    <p class="text-[11px] text-sky-100/80"><i class="fa-solid fa-basket-shopping mr-1"></i>Order masuk hari ini</p>
                    <p class="mt-0.5 text-3xl font-bold">{{ $orderHariIni }}</p>
                </div>
                <a href="#terlambat" class="rounded-2xl p-3.5 ring-1 backdrop-blur {{ $jumlahTerlambat ? 'bg-red-500/90 ring-red-300/40' : 'bg-white/10 ring-white/10' }}">
                    <p class="text-[11px] {{ $jumlahTerlambat ? 'text-red-50' : 'text-sky-100/80' }}"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Terlambat</p>
                    <p class="mt-0.5 text-3xl font-bold">{{ $jumlahTerlambat }}</p>
                </a>
                @if ($isOwner)
                    <div class="rounded-2xl bg-white p-3.5 text-primary shadow-lg">
                        <p class="text-[11px] text-slate-500"><i class="fa-solid fa-coins mr-1"></i>Pendapatan hari ini</p>
                        <p class="mt-0.5 truncate text-xl font-bold md:text-2xl">{{ $rp($pendapatanHariIni) }}</p>
                    </div>
                    <a href="{{ route('laporan.piutang') }}" class="rounded-2xl bg-white/10 p-3.5 ring-1 ring-white/10 backdrop-blur hover:bg-white/15">
                        <p class="text-[11px] text-amber-200"><i class="fa-solid fa-file-invoice-dollar mr-1"></i>Total piutang</p>
                        <p class="mt-0.5 truncate text-xl font-bold md:text-2xl">{{ $rp($piutang) }}</p>
                    </a>
                @else
                    <a href="{{ route('order', ['status' => 'siap_diambil']) }}" class="col-span-2 flex items-center justify-between rounded-2xl bg-white p-3.5 text-primary shadow-lg">
                        <span>
                            <span class="block text-[11px] text-slate-500"><i class="fa-solid fa-box-open mr-1"></i>Siap diambil</span>
                            <span class="mt-0.5 block text-3xl font-bold">{{ $antrian['siap_diambil'] ?? 0 }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-primary/40"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- STOK BAHAN MENIPIS (owner) --}}
    @if (($bahanMenipis ?? collect())->isNotEmpty())
        <a href="{{ route('bahan') }}" class="mt-3 flex min-h-[44px] items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-800 hover:bg-amber-100">
            <i class="fa-solid fa-jug-detergent shrink-0 text-amber-500"></i>
            <span class="min-w-0 flex-1 truncate"><b>Stok menipis:</b> {{ $bahanMenipis->join(', ') }}</span>
            <i class="fa-solid fa-chevron-right shrink-0 text-xs text-amber-400"></i>
        </a>
    @endif

    {{-- ANTAR-JEMPUT HARI INI (termasuk yang terlewat) --}}
    @if ($antarJemput)
        <a href="{{ route('pengiriman') }}" class="mt-3 flex min-h-[44px] items-center gap-3 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-2.5 text-sm text-sky-800 hover:bg-sky-100">
            <i class="fa-solid fa-motorcycle shrink-0 text-sky-500"></i>
            <span class="min-w-0 flex-1 truncate"><b>{{ $antarJemput }} antar/jemput</b> belum selesai hari ini</span>
            <i class="fa-solid fa-chevron-right shrink-0 text-xs text-sky-400"></i>
        </a>
    @endif

    {{-- PIPA ANTRIAN --}}
    <section class="mt-6">
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wider text-slate-500">Cucian berjalan</h3>
        <div class="grid grid-cols-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            @foreach ($pipa as $status => [$label, $icon, $warna])
                <a href="{{ route('order', ['status' => $status]) }}"
                    class="relative flex min-h-[88px] flex-col items-center justify-center gap-1 px-1 py-3 text-center hover:bg-slate-50 {{ $loop->last ? '' : 'border-r border-dashed border-slate-200' }}">
                    <span class="absolute inset-x-0 top-0 h-1 {{ $warna }}"></span>
                    <i class="fa-solid {{ $icon }} text-primary/40"></i>
                    <span class="text-2xl font-bold text-slate-800">{{ $antrian[$status] ?? 0 }}</span>
                    <span class="text-[11px] font-medium text-slate-500">{{ $label }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- TERLAMBAT --}}
        <section id="terlambat" class="scroll-mt-24">
            <h3 class="mb-2 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-red-600">
                <i class="fa-solid fa-fire"></i> Lewat estimasi
            </h3>
            @forelse ($terlambat as $o)
                <a href="{{ route('order.show', $o) }}" class="mb-2 flex min-h-[56px] items-center gap-3 rounded-xl border border-red-100 bg-red-50/60 px-4 py-3 hover:bg-red-50">
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold text-slate-800">{{ $o->pelanggan->nama }}</p>
                        <p class="font-mono text-xs text-slate-500">{{ $o->kode }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ \App\Models\Order::WARNA[$o->status] }}">{{ \App\Models\Order::LABEL[$o->status] }}</span>
                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $o->estimasi_selesai->locale('id')->diffForHumans(['parts' => 1]) }}</p>
                    </div>
                </a>
            @empty
                <div class="flex items-center gap-3 rounded-xl border border-dashed border-emerald-200 bg-white px-4 py-5 text-sm text-slate-500">
                    <i class="fa-solid fa-face-smile-beam text-2xl text-emerald-400"></i> Semua cucian masih sesuai jadwal.
                </div>
            @endforelse
            @if ($jumlahTerlambat > $terlambat->count())
                <p class="text-xs text-slate-400">+{{ $jumlahTerlambat - $terlambat->count() }} order lain terlambat</p>
            @endif
        </section>

        {{-- SIAP DIAMBIL --}}
        <section>
            <h3 class="mb-2 flex items-center justify-between text-sm font-semibold uppercase tracking-wider text-emerald-600">
                <span><i class="fa-solid fa-bell mr-1"></i> Menunggu diambil</span>
                <a href="{{ route('order', ['status' => 'siap_diambil']) }}" class="flex h-11 items-center text-xs normal-case tracking-normal text-slate-400 hover:text-slate-600">Lihat semua</a>
            </h3>
            <div class="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                @forelse ($siap as $o)
                    <div class="flex items-center gap-3 px-4 py-2">
                        <a href="{{ route('order.show', $o) }}" class="min-w-0 flex-1 py-1">
                            <p class="truncate font-medium text-slate-800">{{ $o->pelanggan->nama }}</p>
                            <p class="text-xs text-slate-400"><span class="font-mono">{{ $o->kode }}</span> · {{ \App\Models\Order::LABEL_BAYAR[$o->status_bayar] }}</p>
                        </a>
                        <a href="{{ $o->waUrl('selesai') }}" target="_blank" title="Kirim WA: laundry selesai"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-green-600 text-white hover:bg-green-700">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                        </a>
                    </div>
                @empty
                    <div class="flex items-center gap-3 px-4 py-5 text-sm text-slate-500">
                        <i class="fa-solid fa-box-open text-2xl text-slate-200"></i> Belum ada cucian yang siap diambil.
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    {{-- GRAFIK 7 HARI (owner) --}}
    @if ($isOwner)
        @php $max = max(1, $grafik->max()); @endphp
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 md:p-5">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Uang masuk 7 hari</h3>
                <a href="{{ route('laporan.pendapatan') }}" class="flex h-11 items-center text-xs text-slate-400 hover:text-slate-600">Laporan <i class="fa-solid fa-arrow-right ml-1"></i></a>
            </div>
            <p class="text-lg font-bold text-slate-800">{{ $rp($grafik->sum()) }}</p>
            <div class="mt-4 grid h-40 grid-cols-7 items-end gap-1.5 md:gap-3">
                @foreach ($grafik as $tgl => $nilai)
                    @php $hariIni = $loop->last; @endphp
                    <div class="flex h-full flex-col items-center justify-end gap-1" title="{{ $rp($nilai) }}">
                        <span class="text-[10px] font-semibold {{ $hariIni ? 'text-primary' : 'text-slate-500' }}">{{ $nilai ? $singkat($nilai) : '' }}</span>
                        <div class="w-full rounded-t-lg {{ $hariIni ? 'bg-gradient-to-t from-primary to-sky-500' : 'bg-primary/15' }}" style="height: {{ $nilai ? max(4, round($nilai / $max * 85)) : 2 }}%"></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-1 grid grid-cols-7 gap-1.5 border-t border-slate-100 pt-1 text-center text-[11px] text-slate-400 md:gap-3">
                @foreach ($grafik as $tgl => $nilai)
                    <span class="{{ $loop->last ? 'font-semibold text-primary' : '' }}">{{ \Illuminate\Support\Carbon::parse($tgl)->translatedFormat('D') }}</span>
                @endforeach
            </div>
        </section>
    @endif
@endsection
