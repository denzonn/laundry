@extends('layouts.app')

@section('title', 'Laporan Piutang - ' . config('app.name'))
@section('page_title', 'Laporan Piutang')
@section('page_description', 'Order yang belum lunas, terlama di atas')

@use('App\Models\Order')
@php
    $rp = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $umur = fn($o) => (int) $o->tanggal_masuk->copy()->startOfDay()->diffInDays(today());
    // kelompok umur tagihan: [label, batas atas hari, warna lingkaran]
    $kelompok = [['0–3 hari', 3, 'bg-slate-100 text-slate-700'], ['4–7 hari', 7, 'bg-amber-100 text-amber-700'], ['> 7 hari', PHP_INT_MAX, 'bg-red-100 text-red-700']];
    $grup = fn($o) => collect($kelompok)->search(fn($k) => $umur($o) <= $k[1]);
    $perGrup = $orders->groupBy($grup);
@endphp

@section('content')
    <div class="hidden print:block print:mb-4">
        <h1 class="text-xl font-bold">Laporan Piutang · {{ \App\Models\Pengaturan::toko()->nama_toko }}</h1>
        <p class="text-sm">Per {{ now()->translatedFormat('d M Y H:i') }}</p>
    </div>

    {{-- TOTAL + UMUR --}}
    <div class="rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50 p-5 md:p-6">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm text-amber-700"><i class="fa-solid fa-file-invoice-dollar mr-1"></i>Belum tertagih</p>
                <p class="mt-1 break-words text-3xl font-bold text-amber-900 md:text-4xl">{{ $rp($total) }}</p>
                <p class="mt-1 text-sm text-amber-700">{{ $orders->count() }} order</p>
            </div>
            <button type="button" onclick="window.print()" class="flex h-11 shrink-0 items-center gap-2 rounded-xl bg-white px-4 text-sm font-medium text-amber-800 shadow-sm hover:bg-amber-100 print:hidden">
                <i class="fa-solid fa-print"></i><span class="hidden sm:inline">Cetak</span>
            </button>
        </div>
        <div class="mt-4 grid grid-cols-3 gap-2">
            @foreach ($kelompok as $i => [$label, , $warna])
                <div class="rounded-xl bg-white/70 px-3 py-2">
                    <p class="text-[11px] font-medium text-slate-500">{{ $label }}</p>
                    <p class="truncate text-sm font-bold text-slate-800">{{ $rp(($perGrup[$i] ?? collect())->sum('sisa')) }}</p>
                    <p class="text-[11px] text-slate-400">{{ ($perGrup[$i] ?? collect())->count() }} order</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- DAFTAR --}}
    <div class="mt-6 space-y-3">
        @forelse ($orders as $o)
            @php $hari = $umur($o); @endphp
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 md:p-4 print:break-inside-avoid">
                <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-full {{ $kelompok[$grup($o)][2] }}">
                    <span class="text-lg font-bold leading-none">{{ $hari }}</span>
                    <span class="text-[10px]">hari</span>
                </div>
                <a href="{{ route('order.show', $o) }}" class="min-w-0 flex-1">
                    <p class="truncate font-semibold text-slate-800">{{ $o->pelanggan->nama }}</p>
                    <p class="truncate text-xs text-slate-400"><span class="font-mono">{{ $o->kode }}</span> · {{ $o->tanggal_masuk->translatedFormat('d M Y') }}</p>
                    <div class="mt-1 flex flex-wrap gap-1">
                        <span class="rounded-md px-2 py-0.5 text-[11px] font-medium {{ Order::WARNA[$o->status] }}">{{ Order::LABEL[$o->status] }}</span>
                        <span class="rounded-md px-2 py-0.5 text-[11px] font-medium {{ Order::WARNA[$o->status_bayar] }}">{{ Order::LABEL_BAYAR[$o->status_bayar] }}</span>
                    </div>
                </a>
                <div class="flex shrink-0 flex-col items-end gap-2 md:flex-row md:items-center md:gap-4">
                    <div class="text-right">
                        <p class="font-bold text-slate-800">{{ $rp($o->sisa) }}</p>
                        <p class="text-[11px] text-slate-400">dari {{ $rp($o->total) }}</p>
                    </div>
                    <a href="{{ $o->waUrl('tagihan') }}" target="_blank" title="Kirim pengingat tagihan via WA"
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-600 text-white hover:bg-green-700 print:hidden">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center rounded-2xl border-2 border-dashed border-emerald-200 bg-white py-14 text-center">
                <i class="fa-solid fa-hand-holding-dollar text-5xl text-emerald-300"></i>
                <h3 class="mt-4 font-semibold text-slate-700">Semua order sudah lunas</h3>
                <p class="mt-1 text-sm text-slate-400">Tidak ada tagihan yang tertunda.</p>
            </div>
        @endforelse
    </div>
@endsection
