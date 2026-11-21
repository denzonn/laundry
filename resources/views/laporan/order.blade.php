@extends('layouts.app')

@section('title', 'Laporan Order - ' . config('app.name'))
@section('page_title', 'Laporan Order')
@section('page_description', 'Order masuk per layanan & status (berdasarkan tanggal masuk)')

@use('App\Models\Order')
@php
    $rp = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $qty = fn($v) => rtrim(rtrim(number_format($v, 2, ',', '.'), '0'), ',');
    $batal = (int) ($perStatus['batal'] ?? 0);
    $valid = (int) $perStatus->sum() - $batal;
    $maxLayanan = max(1, (int) $perLayanan->max('subtotal'));
@endphp

@section('content')
    @include('laporan._periode', ['judul' => 'Laporan Order', 'aksen' => 'border-indigo-600 bg-indigo-600 text-white'])

    {{-- SCOREBOARD --}}
    <div class="mt-4 grid grid-cols-2 overflow-hidden rounded-2xl border border-indigo-100 bg-indigo-50 md:grid-cols-4">
        <div class="col-span-2 border-b border-indigo-100 p-4 md:border-b-0 md:border-r">
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-500">Omzet (tanpa batal)</p>
            <p class="mt-1 break-words text-2xl font-bold text-indigo-900 md:text-3xl">{{ $rp($omzet) }}</p>
        </div>
        <div class="border-r border-indigo-100 p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-500">Order</p>
            <p class="mt-1 text-2xl font-bold text-indigo-900">{{ $valid }}</p>
        </div>
        <div class="p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-red-500">Batal</p>
            <p class="mt-1 text-2xl font-bold text-red-700">{{ $batal }}</p>
        </div>
    </div>

    {{-- PER STATUS --}}
    <section class="mt-6">
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wider text-slate-500">Per status</h3>
        <div class="grid grid-cols-3 gap-2 md:grid-cols-6">
            @foreach (Order::LABEL as $status => $label)
                <div class="rounded-xl px-3 py-2 {{ Order::WARNA[$status] }}">
                    <p class="truncate text-[11px] font-medium">{{ $label }}</p>
                    <p class="text-xl font-bold">{{ $perStatus[$status] ?? 0 }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- PER LAYANAN --}}
    <section class="mt-6">
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wider text-slate-500">Per layanan</h3>
        @if ($perLayanan->isEmpty())
            <div class="flex flex-col items-center rounded-2xl border-2 border-dashed border-slate-200 bg-white py-14 text-center">
                <i class="fa-solid fa-shirt text-5xl text-slate-200"></i>
                <h3 class="mt-4 font-semibold text-slate-700">Belum ada order</h3>
                <p class="mt-1 text-sm text-slate-400">Tidak ada order masuk di periode ini.</p>
            </div>
        @else
            {{-- HP: kartu peringkat --}}
            <div class="space-y-2 md:hidden print:hidden">
                @foreach ($perLayanan as $l)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="flex items-start gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-sm font-bold text-indigo-700">{{ $loop->iteration }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-slate-800">{{ $l->nama }}</p>
                                <p class="text-xs text-slate-400">{{ $l->jumlah_order }} order · {{ $qty($l->qty) }} {{ $l->satuan }}</p>
                            </div>
                            <p class="shrink-0 font-semibold text-slate-800">{{ $rp($l->subtotal) }}</p>
                        </div>
                        <div class="mt-3 h-1.5 rounded-full bg-slate-100">
                            <div class="h-1.5 rounded-full bg-indigo-500" style="width: {{ max(2, $l->subtotal / $maxLayanan * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- tablet+: tabel --}}
            <div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white md:block print:block">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Layanan</th>
                            <th class="px-4 py-3 text-right">Order</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                            <th class="w-1/4 px-4 py-3 print:hidden"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($perLayanan as $l)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-indigo-600">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $l->nama }}</td>
                                <td class="px-4 py-3 text-right">{{ $l->jumlah_order }}</td>
                                <td class="px-4 py-3 text-right">{{ $qty($l->qty) }} {{ $l->satuan }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ $rp($l->subtotal) }}</td>
                                <td class="px-4 py-3 print:hidden">
                                    <div class="h-2 rounded-full bg-slate-100">
                                        <div class="h-2 rounded-full bg-indigo-500" style="width: {{ max(2, $l->subtotal / $maxLayanan * 100) }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-xs text-slate-400">Subtotal layanan sebelum diskon & biaya antar, order batal tidak dihitung.</p>
        @endif
    </section>
@endsection
