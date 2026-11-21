@extends('layouts.app')

@section('title', 'Laporan Pendapatan - ' . config('app.name'))
@section('page_title', 'Laporan Pendapatan')
@section('page_description', 'Uang yang benar-benar diterima (dari pembayaran)')

@php
    $rp = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $warna = ['bg-emerald-500', 'bg-sky-500', 'bg-violet-500', 'bg-amber-500', 'bg-rose-500', 'bg-slate-500'];
    $maxHari = max(1, (int) $perHari->max('total'));
    $hari = max(1, $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1);
@endphp

@section('content')
    @include('laporan._periode', ['judul' => 'Laporan Pendapatan', 'aksen' => 'border-emerald-600 bg-emerald-600 text-white'])

    {{-- TOTAL --}}
    <div class="mt-4 rounded-2xl bg-slate-900 p-5 text-white md:p-6 print:border print:border-slate-300 print:bg-white print:text-slate-900">
        <p class="text-sm text-slate-400">Total uang masuk · {{ $start->translatedFormat('d M') }} – {{ $end->translatedFormat('d M Y') }}</p>
        <p class="mt-1 break-words text-3xl font-bold tracking-tight text-emerald-400 md:text-4xl print:text-slate-900">{{ $rp($total) }}</p>
        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-white/10 pt-4 text-sm print:border-slate-200">
            <div>
                <p class="text-slate-400">Transaksi</p>
                <p class="text-lg font-semibold">{{ $transaksi }}</p>
            </div>
            <div>
                <p class="text-slate-400">Rata-rata / hari</p>
                <p class="text-lg font-semibold">{{ $rp(intdiv($total, (int) $hari)) }}</p>
            </div>
        </div>
    </div>

    @if ($total)
        <div class="mt-6 grid gap-6 lg:grid-cols-5">
            {{-- PER METODE --}}
            <section class="lg:col-span-2">
                <h3 class="mb-2 text-sm font-semibold uppercase tracking-wider text-slate-500">Per metode bayar</h3>
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="flex h-4 overflow-hidden rounded-full bg-slate-100">
                        @foreach ($perMetode as $m)
                            <div class="{{ $warna[$loop->index % count($warna)] }}" style="width: {{ $m->total / $total * 100 }}%"></div>
                        @endforeach
                    </div>
                    <ul class="mt-4 space-y-3">
                        @foreach ($perMetode as $m)
                            <li class="flex items-center gap-3">
                                <span class="h-3 w-3 shrink-0 rounded-full {{ $warna[$loop->index % count($warna)] }}"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium text-slate-800">{{ $m->nama }}</p>
                                    <p class="text-xs text-slate-400">{{ $m->jumlah }} transaksi · {{ round($m->total / $total * 100) }}%</p>
                                </div>
                                <p class="shrink-0 font-semibold text-slate-800">{{ $rp($m->total) }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            {{-- PER HARI --}}
            <section class="lg:col-span-3">
                <h3 class="mb-2 text-sm font-semibold uppercase tracking-wider text-slate-500">Per hari</h3>
                <div class="divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white">
                    @foreach ($perHari as $h)
                        @php $tgl = \Illuminate\Support\Carbon::parse($h->tgl); @endphp
                        <div class="flex items-center gap-3 px-4 py-3 print:break-inside-avoid">
                            <div class="w-12 shrink-0 text-center leading-tight">
                                <p class="text-[11px] uppercase text-slate-400">{{ $tgl->translatedFormat('D') }}</p>
                                <p class="text-lg font-bold text-slate-800">{{ $tgl->format('d') }}</p>
                                <p class="text-[11px] text-slate-400">{{ $tgl->translatedFormat('M') }}</p>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-baseline justify-between gap-2">
                                    <p class="truncate font-semibold text-slate-800">{{ $rp($h->total) }}</p>
                                    <p class="shrink-0 text-xs text-slate-400">{{ $h->jumlah }} trx</p>
                                </div>
                                <div class="mt-1.5 h-2 rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full bg-emerald-500" style="width: {{ max(2, $h->total / $maxHari * 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    @else
        <div class="mt-6 flex flex-col items-center rounded-2xl border-2 border-dashed border-slate-200 bg-white py-14 text-center">
            <i class="fa-solid fa-sack-dollar text-5xl text-slate-200"></i>
            <h3 class="mt-4 font-semibold text-slate-700">Belum ada uang masuk</h3>
            <p class="mt-1 text-sm text-slate-400">Tidak ada pembayaran tercatat di periode ini.</p>
        </div>
    @endif
@endsection
