{{-- Filter periode laporan (?start=&end=) + tombol Cetak. Butuh $start, $end (Carbon), $judul, $aksen (kelas warna chip aktif). --}}
@php
    $preset = [
        'Hari ini' => [today(), today()],
        '7 hari' => [today()->subDays(6), today()],
        'Bulan ini' => [today()->startOfMonth(), today()],
        'Bulan lalu' => [today()->subMonthNoOverflow()->startOfMonth(), today()->subMonthNoOverflow()->endOfMonth()],
    ];
    $s = $start->toDateString();
    $e = $end->toDateString();
@endphp

<div class="hidden print:block print:mb-4">
    <h1 class="text-xl font-bold">{{ $judul }} · {{ \App\Models\Pengaturan::toko()->nama_toko }}</h1>
    <p class="text-sm">Periode {{ $start->translatedFormat('d M Y') }} s.d {{ $end->translatedFormat('d M Y') }} · dicetak {{ now()->translatedFormat('d M Y H:i') }}</p>
</div>

<form method="GET" id="formPeriode" class="flex flex-col gap-2 print:hidden md:flex-row md:items-center">
    <div class="-mx-4 overflow-x-auto px-4 md:mx-0 md:px-0">
        <div class="flex min-w-max gap-2">
            @foreach ($preset as $label => [$a, $b])
                @php $aktif = $s === $a->toDateString() && $e === $b->toDateString(); @endphp
                <a href="?start={{ $a->toDateString() }}&end={{ $b->toDateString() }}"
                    class="flex h-11 items-center rounded-xl border px-4 text-sm font-medium {{ $aktif ? $aksen : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>
    <div class="flex flex-1 gap-2 md:justify-end">
        <div class="relative flex-1 md:max-w-xs">
            <i class="fa-regular fa-calendar absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input id="periode" readonly placeholder="Pilih tanggal" class="h-11 w-full rounded-xl border-slate-200 bg-white pl-11 text-base md:text-sm">
        </div>
        <input type="hidden" name="start" value="{{ $s }}">
        <input type="hidden" name="end" value="{{ $e }}">
        <button type="button" onclick="window.print()" class="flex h-11 shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 hover:bg-slate-50">
            <i class="fa-solid fa-print"></i><span class="hidden sm:inline">Cetak</span>
        </button>
    </div>
</form>

@push('addon-script')
    <script>
        initDateRangePicker('#periode', (start, end) => {
            if (!start || !end) return;
            $('#formPeriode [name=start]').val(start);
            $('#formPeriode [name=end]').val(end);
            App.loading.show();
            $('#formPeriode')[0].submit();
        }, { defaultDate: [new Date('{{ $s }}T00:00'), new Date('{{ $e }}T00:00')] });
    </script>
@endpush
