@extends('layouts.app')

@section('title', 'Tugas Saya - ' . config('app.name'))
@section('page_title', 'Tugas Saya')
@section('page_description', 'Antar & jemput cucian')

@php
    $rp = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $aktif = $tugas->where('status', '!=', 'selesai');
    $grup = [
        'Hari ini' => $aktif->filter(fn($t) => $t->jadwal->lte(today()->endOfDay())),
        'Besok' => $aktif->filter(fn($t) => $t->jadwal->isTomorrow()),
        'Nanti' => $aktif->filter(fn($t) => $t->jadwal->gt(today()->addDay()->endOfDay())),
    ];
    $selesai = $tugas->where('status', 'selesai');
    $jenis = ['jemput' => ['fa-house-chimney', 'JEMPUT', 'bg-violet-600', 'Ambil cucian kotor'], 'antar' => ['fa-truck-fast', 'ANTAR', 'bg-sky-600', 'Kirim cucian bersih']];
@endphp

@section('content')
    {{-- HERO --}}
    <div class="relative overflow-hidden rounded-2xl bg-slate-900 p-5 text-white">
        <i class="fa-solid fa-route absolute -bottom-4 -right-2 text-[110px] text-white/5"></i>
        <p class="text-sm text-slate-400">{{ now()->translatedFormat('l, d F') }}</p>
        <h2 class="text-xl font-bold">Halo, {{ strtok(auth()->user()->name, ' ') }}</h2>
        <div class="relative mt-4 grid grid-cols-3 gap-2 text-center">
            <div class="rounded-xl bg-white/10 py-2"><p class="text-2xl font-bold">{{ $grup['Hari ini']->count() }}</p><p class="text-[11px] text-slate-300">hari ini</p></div>
            <div class="rounded-xl bg-white/10 py-2"><p class="text-2xl font-bold">{{ $grup['Besok']->count() + $grup['Nanti']->count() }}</p><p class="text-[11px] text-slate-300">berikutnya</p></div>
            <div class="rounded-xl bg-emerald-500/20 py-2"><p class="text-2xl font-bold text-emerald-300">{{ $selesai->count() }}</p><p class="text-[11px] text-emerald-200">selesai hari ini</p></div>
        </div>
    </div>

    @foreach ($grup as $judul => $list)
        @continue($list->isEmpty() && $judul !== 'Hari ini')
        <h3 class="mb-3 mt-6 text-sm font-semibold uppercase tracking-wider text-slate-500">{{ $judul }}</h3>

        <div class="grid gap-4 md:grid-cols-2">
            @forelse ($list as $t)
                @php
                    [$ikon, $label, $warna, $desc] = $jenis[$t->jenis];
                    $pel = $t->order->pelanggan;
                    $jalan = $t->status === 'dalam_perjalanan';
                @endphp
                <article class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 {{ $jalan ? 'ring-2 ring-amber-400' : 'ring-slate-200' }}">
                    {{-- kepala tiket --}}
                    <div class="flex items-center gap-3 px-4 py-3 text-white {{ $warna }}">
                        <i class="fa-solid {{ $ikon }} text-xl"></i>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold tracking-widest">{{ $label }}</p>
                            <p class="truncate text-[11px] text-white/80">{{ $desc }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold leading-none">{{ $t->jadwal->format('H:i') }}</p>
                            @if ($t->jadwal->isPast() && ! $jalan)
                                <p class="text-[11px] font-semibold text-yellow-200">terlewat</p>
                            @elseif (! $t->jadwal->isToday())
                                <p class="text-[11px] text-white/80">{{ $t->jadwal->translatedFormat('D, d M') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-3 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-lg font-semibold text-slate-800">{{ $pel->nama }}</p>
                                <p class="font-mono text-xs text-slate-400">{{ $t->order->kode }}</p>
                            </div>
                            @if ($jalan)
                                <span class="shrink-0 rounded-lg bg-amber-100 px-2 py-1 text-[11px] font-semibold text-amber-700"><i class="fa-solid fa-person-biking mr-1"></i>Di jalan</span>
                            @endif
                        </div>

                        {{-- alamat + maps --}}
                        <a href="{{ $t->mapsUrl() }}" target="_blank" class="flex items-center gap-3 rounded-xl border border-dashed border-slate-300 p-3 hover:bg-slate-50">
                            <i class="fa-solid fa-location-dot text-xl text-red-500"></i>
                            <span class="min-w-0 flex-1 text-sm text-slate-700">{{ $t->alamat }}</span>
                            <span class="shrink-0 text-xs font-semibold text-sky-600">Buka Maps <i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                        </a>
                        @if ($t->catatan)
                            <p class="rounded-xl bg-amber-50 px-3 py-2 text-sm text-amber-800"><i class="fa-solid fa-note-sticky mr-1"></i>{{ $t->catatan }}</p>
                        @endif

                        <div class="grid grid-cols-2 gap-2">
                            <a href="tel:+{{ $pel->no_hp }}" class="flex h-12 items-center justify-center gap-2 rounded-xl bg-slate-100 text-sm font-semibold text-slate-700"><i class="fa-solid fa-phone"></i> Telepon</a>
                            <a href="https://wa.me/{{ $pel->no_hp }}" target="_blank" class="flex h-12 items-center justify-center gap-2 rounded-xl bg-green-50 text-sm font-semibold text-green-700"><i class="fa-brands fa-whatsapp text-lg"></i> WhatsApp</a>
                        </div>

                        {{-- tagihan: kurir hanya menyampaikan, pembayaran dicatat kasir --}}
                        <div class="flex items-center justify-between rounded-xl px-3 py-2 {{ $t->order->sisa ? 'bg-red-50' : 'bg-emerald-50' }}">
                            <span class="text-sm {{ $t->order->sisa ? 'text-red-700' : 'text-emerald-700' }}">
                                <i class="fa-solid {{ $t->order->sisa ? 'fa-hand-holding-dollar' : 'fa-circle-check' }} mr-1"></i>{{ $t->order->sisa ? 'Tagih tunai' : 'Sudah lunas' }}
                            </span>
                            @if ($t->order->sisa)
                                <b class="text-lg text-red-700">{{ $rp($t->order->sisa) }}</b>
                            @endif
                        </div>

                        <button type="button" onclick="ubahTugas({{ $t->id }}, '{{ $jalan ? 'selesai' : 'dalam_perjalanan' }}')"
                            class="flex h-14 w-full items-center justify-center gap-2 rounded-xl text-base font-bold text-white active:scale-[.98] {{ $jalan ? 'bg-emerald-600' : 'bg-slate-900' }}">
                            <i class="fa-solid {{ $jalan ? 'fa-flag-checkered' : 'fa-person-biking' }}"></i> {{ $jalan ? 'Selesai' : 'Mulai jalan' }}
                        </button>
                    </div>
                </article>
            @empty
                <div class="flex flex-col items-center rounded-2xl border-2 border-dashed border-slate-200 bg-white px-6 py-12 text-center md:col-span-2">
                    <i class="fa-solid fa-mug-hot text-4xl text-slate-200"></i>
                    <p class="mt-3 font-semibold text-slate-600">Tidak ada tugas hari ini</p>
                    <p class="text-sm text-slate-400">Tugas baru dari kasir akan muncul di sini.</p>
                </div>
            @endforelse
        </div>
    @endforeach

    @if ($selesai->isNotEmpty())
        <h3 class="mb-3 mt-6 text-sm font-semibold uppercase tracking-wider text-emerald-600">Selesai hari ini</h3>
        <div class="divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white">
            @foreach ($selesai as $t)
                <div class="flex min-h-[56px] items-center gap-3 px-4 py-2 text-sm">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span class="min-w-0 flex-1 truncate"><b>{{ ucfirst($t->jenis) }}</b> · {{ $t->order->pelanggan->nama }}</span>
                    <span class="text-xs text-slate-400">{{ $t->selesai_at->format('H:i') }}</span>
                </div>
            @endforeach
        </div>
    @endif
@endsection

@push('addon-script')
    <script>
        function ubahTugas(id, status) {
            Swal.fire({
                icon: 'question', title: status === 'selesai' ? 'Tugas sudah selesai?' : 'Mulai jalan sekarang?',
                showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Belum',
            }).then((r) => {
                if (!r.isConfirmed) return;
                App.loading.show();
                ajaxRequest({
                    url: `{{ url('pengiriman') }}/${id}/status`, type: 'POST', data: { _method: 'PATCH', status },
                    success: () => location.reload(),
                    error: (xhr) => { App.loading.hide(); Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.errors?.status?.[0] ?? xhr.responseJSON?.message ?? 'Terjadi kesalahan.' }); },
                });
            });
        }
    </script>
@endpush
