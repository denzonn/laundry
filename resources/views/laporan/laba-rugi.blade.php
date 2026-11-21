@extends('layouts.app')

@section('title', 'Laba Rugi - ' . config('app.name'))
@section('page_title', 'Laba Rugi')
@section('page_description', 'Uang masuk dikurangi pengeluaran (berdasarkan uang masuk, bukan omzet order)')

@use('App\Models\KategoriPengeluaran')
@php
    $rp = fn($v) => ($v < 0 ? '− ' : '') . 'Rp ' . number_format(abs($v), 0, ',', '.');
    $untung = $laba >= 0;
    $maxTren = max(1, $tren->max(fn($t) => max($t['masuk'], $t['keluar'])));
    $margin = $pendapatan ? round($laba / $pendapatan * 100) : null;
@endphp

@section('content')
    @include('laporan._periode', ['judul' => 'Laporan Laba Rugi', 'aksen' => 'border-teal-600 bg-teal-600 text-white'])

    <div class="mt-4 grid gap-6 lg:grid-cols-5">
        {{-- LEMBAR LABA RUGI --}}
        <section class="lg:col-span-2">
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm print:shadow-none">
                <div class="h-1.5 {{ $untung ? 'bg-teal-500' : 'bg-rose-500' }} print:hidden"></div>
                <div class="p-5 md:p-6">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500"><i class="fa-solid fa-scale-balanced mr-1 text-teal-600"></i>Ikhtisar</h3>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] text-slate-500">{{ $start->translatedFormat('d M') }} – {{ $end->translatedFormat('d M Y') }}</span>
                    </div>

                    <dl class="mt-5 space-y-1 text-sm">
                        <div class="flex items-baseline gap-2">
                            <dt class="font-semibold text-slate-800">Pendapatan</dt>
                            <span class="flex-1 border-b border-dotted border-slate-300"></span>
                            <dd class="font-semibold text-emerald-700">{{ $rp($pendapatan) }}</dd>
                        </div>
                        <p class="pb-3 text-xs text-slate-400">Total pembayaran diterima di periode ini</p>

                        <div class="flex items-baseline gap-2">
                            <dt class="font-semibold text-slate-800">Pengeluaran</dt>
                            <span class="flex-1 border-b border-dotted border-slate-300"></span>
                            <dd class="font-semibold text-rose-700">{{ $rp(-$pengeluaran) }}</dd>
                        </div>
                        @forelse ($perKategori as $k)
                            <div class="flex items-baseline gap-2 pl-4 text-slate-500">
                                <span class="h-2 w-2 shrink-0 rounded-full" style="background: {{ KategoriPengeluaran::warnaUntuk($k->id) }}"></span>
                                <dt class="min-w-0 truncate">{{ $k->nama }} <span class="text-xs text-slate-400">({{ $k->jumlah }})</span></dt>
                                <span class="flex-1 border-b border-dotted border-slate-200"></span>
                                <dd class="shrink-0">{{ $rp($k->total) }}</dd>
                            </div>
                        @empty
                            <p class="pl-4 text-xs text-slate-400">Tidak ada pengeluaran tercatat.</p>
                        @endforelse
                    </dl>

                    <div class="mt-5 border-t-4 border-double border-slate-300 pt-4">
                        <p class="text-xs font-medium uppercase tracking-wider {{ $untung ? 'text-teal-600' : 'text-rose-600' }}">
                            {{ $untung ? 'Laba bersih' : 'Rugi' }}
                        </p>
                        <p class="mt-1 break-words text-3xl font-bold tracking-tight md:text-4xl {{ $untung ? 'text-teal-700' : 'text-rose-600' }}">{{ $rp($laba) }}</p>
                        @if ($margin !== null)
                            <p class="mt-1 text-xs text-slate-500">Margin {{ $margin }}% dari pendapatan</p>
                        @endif
                    </div>
                </div>
            </div>

            <p class="mt-3 rounded-xl bg-teal-50 px-4 py-3 text-xs leading-relaxed text-teal-800">
                <i class="fa-solid fa-circle-info mr-1"></i>Dihitung <b>berdasarkan uang masuk</b>: pendapatan = pembayaran yang diterima (DP & pelunasan) pada tanggal bayar,
                bukan total order. Piutang belum dihitung sampai dibayar.
            </p>
        </section>

        {{-- TREN MASUK vs KELUAR --}}
        <section class="lg:col-span-3">
            <div class="mb-2 flex items-center justify-between gap-2">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Arus {{ $bulanan ? 'per bulan' : 'per hari' }}</h3>
                <div class="flex gap-3 text-[11px] text-slate-500">
                    <span><span class="mr-1 inline-block h-2 w-2 rounded-full bg-rose-400"></span>Keluar</span>
                    <span><span class="mr-1 inline-block h-2 w-2 rounded-full bg-emerald-500"></span>Masuk</span>
                </div>
            </div>

            @if ($tren->isEmpty())
                <div class="flex flex-col items-center rounded-2xl border-2 border-dashed border-slate-200 bg-white py-14 text-center">
                    <i class="fa-solid fa-scale-unbalanced text-5xl text-slate-200"></i>
                    <h3 class="mt-4 font-semibold text-slate-700">Belum ada arus uang</h3>
                    <p class="mt-1 text-sm text-slate-400">Tidak ada pembayaran maupun pengeluaran di periode ini.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white">
                    @foreach ($tren as $tgl => $t)
                        @php
                            $net = $t['masuk'] - $t['keluar'];
                            $label = $bulanan
                                ? \Illuminate\Support\Carbon::createFromFormat('!Y-m', $tgl)->translatedFormat('M Y')
                                : \Illuminate\Support\Carbon::parse($tgl)->translatedFormat('D, d M');
                        @endphp
                        <div class="px-4 py-3 print:break-inside-avoid">
                            <div class="flex items-baseline justify-between gap-2 text-sm">
                                <p class="font-medium text-slate-700">{{ $label }}</p>
                                <p class="shrink-0 font-semibold {{ $net >= 0 ? 'text-teal-700' : 'text-rose-600' }}">{{ $net >= 0 ? '+' : '' }}{{ $rp($net) }}</p>
                            </div>
                            {{-- butterfly bar: keluar ke kiri, masuk ke kanan --}}
                            <div class="mt-1.5 grid grid-cols-2 gap-px">
                                <div class="flex h-2.5 justify-end rounded-l-full bg-slate-100">
                                    <div class="h-2.5 rounded-l-full bg-rose-400" style="width: {{ $t['keluar'] / $maxTren * 100 }}%"></div>
                                </div>
                                <div class="flex h-2.5 rounded-r-full bg-slate-100">
                                    <div class="h-2.5 rounded-r-full bg-emerald-500" style="width: {{ $t['masuk'] / $maxTren * 100 }}%"></div>
                                </div>
                            </div>
                            <div class="mt-1 flex justify-between text-[11px] text-slate-400">
                                <span>{{ $rp($t['keluar']) }}</span>
                                <span>{{ $rp($t['masuk']) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
