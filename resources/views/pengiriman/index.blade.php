@extends('layouts.app')

@section('title', 'Antar Jemput - ' . config('app.name'))
@section('page_title', 'Antar Jemput')
@section('page_description', 'Jadwal kurir per hari')

@use('App\Models\Pengiriman')

@php
    $rp = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $q = fn(array $ubah) => route('pengiriman', array_filter($ubah + request()->only('tanggal', 'status', 'kurir')));
    $jenis = ['jemput' => ['fa-house-chimney', 'Jemput', 'bg-violet-600'], 'antar' => ['fa-truck-fast', 'Antar', 'bg-sky-600']];
    $hari = collect(range(-1, 5))->map(fn($i) => today()->addDays($i));
@endphp

@section('content')
    {{-- STRIP TANGGAL --}}
    <div class="rounded-2xl bg-gradient-to-r from-sky-600 to-cyan-500 p-4 text-white md:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs text-sky-100">Jadwal</p>
                <h2 class="truncate text-xl font-bold">{{ $tanggal->translatedFormat('l, d F Y') }}</h2>
            </div>
            <form method="GET" class="shrink-0">
                @foreach (request()->only('status', 'kurir') as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <label class="flex h-11 cursor-pointer items-center gap-2 rounded-xl bg-white/15 px-3 text-sm font-medium backdrop-blur">
                    <i class="fa-regular fa-calendar"></i>
                    <input type="date" name="tanggal" value="{{ $tanggal->toDateString() }}" onchange="this.form.submit()"
                        class="w-[130px] border-0 bg-transparent p-0 text-base text-white focus:ring-0 md:text-sm [color-scheme:dark]">
                </label>
            </form>
        </div>

        <div class="-mx-4 mt-4 overflow-x-auto px-4 md:mx-0 md:px-0">
            <div class="flex min-w-max gap-2">
                @foreach ($hari as $h)
                    @php $aktif = $h->isSameDay($tanggal); @endphp
                    <a href="{{ $q(['tanggal' => $h->toDateString()]) }}"
                        class="flex h-14 w-14 flex-col items-center justify-center rounded-xl text-xs {{ $aktif ? 'bg-white font-semibold text-sky-700' : 'bg-white/10 text-sky-50 hover:bg-white/20' }}">
                        <span>{{ $h->isToday() ? 'Hari ini' : $h->translatedFormat('D') }}</span>
                        <span class="text-lg font-bold leading-none">{{ $h->day }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @if ($belumDitugaskan)
        <a href="{{ $q(['kurir' => 'kosong', 'status' => null]) }}" class="mt-4 flex min-h-[44px] items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-800">
            <i class="fa-solid fa-user-clock text-amber-500"></i>
            <span class="flex-1"><b>{{ $belumDitugaskan }}</b> jadwal aktif belum punya kurir</span>
            <i class="fa-solid fa-chevron-right text-xs text-amber-400"></i>
        </a>
    @endif

    {{-- FILTER --}}
    <div class="mt-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div class="-mx-4 overflow-x-auto px-4 md:mx-0 md:px-0">
            <div class="flex min-w-max gap-2">
                <a href="{{ $q(['status' => null]) }}"
                    class="flex h-11 items-center gap-2 rounded-xl border px-4 text-sm font-medium {{ request('status') ? 'border-slate-200 bg-white text-slate-600' : 'border-slate-800 bg-slate-800 text-white' }}">
                    Semua <span class="rounded bg-black/10 px-1.5 text-xs">{{ $ringkasan->sum() }}</span>
                </a>
                @foreach (Pengiriman::LABEL as $s => $label)
                    <a href="{{ $q(['status' => $s]) }}"
                        class="flex h-11 items-center gap-2 rounded-xl border px-4 text-sm font-medium {{ request('status') === $s ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-200 bg-white text-slate-600' }}">
                        {{ $label }} <span class="rounded bg-black/10 px-1.5 text-xs">{{ $ringkasan[$s] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        <form method="GET">
            @foreach (request()->only('tanggal', 'status') as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
            <select name="kurir" onchange="this.form.submit()" class="h-11 w-full rounded-xl border-slate-200 text-base md:w-56 md:text-sm">
                <option value="">Semua kurir</option>
                <option value="kosong" @selected(request('kurir') === 'kosong')>Belum ada kurir</option>
                @foreach ($kurir as $k)
                    <option value="{{ $k->id }}" @selected(request('kurir') == $k->id)>{{ $k->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- LINIMASA PER JAM --}}
    <div class="mt-5">
        @forelse ($list->groupBy(fn($p) => $p->jadwal->format('H:00')) as $jam => $grup)
            <div class="relative flex gap-3 pb-5 md:gap-5">
                <div class="w-12 shrink-0 pt-3 text-right md:w-16">
                    <p class="text-sm font-bold text-slate-700">{{ $jam }}</p>
                </div>
                <div class="absolute bottom-0 left-[54px] top-4 w-px bg-slate-200 md:left-[74px]"></div>
                <div class="min-w-0 flex-1 space-y-3">
                    @foreach ($grup as $p)
                        @php
                            [$ikon, $label, $warna] = $jenis[$p->jenis];
                            $bisaUbah = isset(Pengiriman::ALUR[$p->status]);
                        @endphp
                        <div class="relative rounded-2xl border border-slate-200 bg-white p-4 {{ $bisaUbah ? '' : 'opacity-60' }}">
                            <span class="absolute -left-3 top-5 h-3 w-3 rounded-full ring-4 ring-slate-50 md:-left-4 {{ $warna }}"></span>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-semibold text-white {{ $warna }}"><i class="fa-solid {{ $ikon }}"></i>{{ $label }}</span>
                                <span class="text-sm font-semibold text-slate-700">{{ $p->jadwal->format('H:i') }}</span>
                                <span class="rounded-lg px-2 py-0.5 text-[11px] font-semibold {{ Pengiriman::WARNA[$p->status] }}">{{ Pengiriman::LABEL[$p->status] }}</span>
                                <a href="{{ route('order.show', $p->order_id) }}" class="ml-auto font-mono text-xs font-semibold text-slate-400 hover:text-primary">{{ $p->order->kode }}</a>
                            </div>

                            <div class="mt-3 grid gap-3 md:grid-cols-2">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-800">{{ $p->order->pelanggan->nama }}</p>
                                    <a href="{{ $p->mapsUrl() }}" target="_blank" class="mt-0.5 flex gap-2 text-sm text-slate-500 hover:text-sky-700">
                                        <i class="fa-solid fa-location-dot mt-0.5 text-sky-500"></i><span>{{ $p->alamat }}</span>
                                    </a>
                                    @if ($p->catatan)
                                        <p class="mt-1 text-xs text-amber-700"><i class="fa-solid fa-note-sticky mr-1"></i>{{ $p->catatan }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-2">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $p->kurir ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-600' }}">
                                        <i class="fa-solid {{ $p->kurir ? 'fa-motorcycle' : 'fa-user-clock' }} text-sm"></i>
                                    </span>
                                    <div class="min-w-0 flex-1 text-sm">
                                        <p class="truncate font-medium text-slate-700">{{ $p->kurir->name ?? 'Belum ada kurir' }}</p>
                                        <p class="text-xs text-slate-400">
                                            {{ $p->order->sisa ? 'Tagih ' . $rp($p->order->sisa) : 'Sudah lunas' }}
                                            @if ($p->selesai_at) · selesai {{ $p->selesai_at->format('H:i') }} @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @if ($bisaUbah)
                                <div class="mt-3 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3 sm:flex sm:justify-end">
                                    <button type="button"
                                        onclick="bukaPengiriman({{ json_encode(['url' => route('pengiriman.update', $p), 'method' => 'PUT', 'judul' => ($p->kurir ? 'Ubah Jadwal ' : 'Tugaskan Kurir ') . $label, 'alamat' => $p->alamat, 'jadwal' => $p->jadwal->format('Y-m-d\TH:i'), 'kurir_id' => $p->kurir_id, 'biaya' => $p->biaya ?: null, 'catatan' => $p->catatan]) }})"
                                        class="h-11 rounded-xl px-4 text-sm font-semibold {{ $p->kurir ? 'border border-slate-200 text-slate-700 hover:bg-slate-50' : 'bg-amber-500 text-white hover:bg-amber-600' }}">
                                        <i class="fa-solid {{ $p->kurir ? 'fa-pen' : 'fa-user-plus' }} mr-1"></i> {{ $p->kurir ? 'Ubah' : 'Tugaskan' }}
                                    </button>
                                    <button type="button" onclick="statusPengiriman({{ $p->id }}, 'batal')" class="h-11 rounded-xl px-4 text-sm font-medium text-red-600 hover:bg-red-50">
                                        <i class="fa-solid fa-ban mr-1"></i> Batal
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center rounded-2xl border-2 border-dashed border-sky-200 bg-white px-6 py-16 text-center">
                <span class="flex h-20 w-20 items-center justify-center rounded-full bg-sky-50"><i class="fa-solid fa-route text-3xl text-sky-300"></i></span>
                <h3 class="mt-4 font-semibold text-slate-700">Tidak ada jadwal di tanggal ini</h3>
                <p class="mt-1 max-w-xs text-sm text-slate-400">Jadwal antar/jemput dibuat dari halaman Detail Order, bagian "Antar / Jemput".</p>
            </div>
        @endforelse
    </div>

    @include('pengiriman._form')
@endsection
