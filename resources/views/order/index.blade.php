@extends('layouts.app')

@section('title', 'Daftar Order - ' . config('app.name'))
@section('page_title', 'Daftar Order')
@section('page_description', 'Pantau cucian dari antrian sampai diambil')

@php
    $tabs = ['' => ['Semua', 'fa-layer-group']] + [
        'antrian' => ['Antrian', 'fa-hourglass-start'], 'dicuci' => ['Dicuci', 'fa-soap'],
        'disetrika' => ['Disetrika', 'fa-shirt'], 'siap_diambil' => ['Siap', 'fa-box-open'],
        'diambil' => ['Diambil', 'fa-circle-check'], 'batal' => ['Batal', 'fa-ban'],
    ];
@endphp

@section('content')
    {{-- PIPELINE TABS --}}
    <div class="-mx-4 overflow-x-auto px-4 md:mx-0 md:px-0">
        <div class="flex min-w-max gap-2" id="tabs">
            @foreach ($tabs as $val => [$label, $icon])
                <button type="button" data-status="{{ $val }}"
                    class="status-tab flex h-12 items-center gap-2 rounded-2xl border px-4 text-sm font-medium transition
                        {{ $val === '' ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-200 bg-white text-slate-600' }}">
                    <i class="fa-solid {{ $icon }}"></i>{{ $label }}
                    <span class="count rounded-lg bg-black/10 px-1.5 text-xs" data-count="{{ $val }}">0</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- FILTER --}}
    <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
        <div class="relative sm:col-span-2">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input id="search" type="search" placeholder="Cari kode, nama, no HP..."
                class="h-11 w-full rounded-xl border-slate-200 pl-11 text-base md:text-sm">
        </div>
        <select id="statusBayar" class="h-11 rounded-xl border-slate-200 text-base md:text-sm">
            <option value="">Semua pembayaran</option>
            <option value="belum">Belum bayar</option>
            <option value="dp">DP</option>
            <option value="lunas">Lunas</option>
        </select>
        <input id="tanggal" placeholder="Filter tanggal masuk" readonly class="h-11 rounded-xl border-slate-200 bg-white text-base md:text-sm">
    </div>

    {{-- TIKET --}}
    <div id="tiket" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3"></div>

    <div class="mt-6 flex flex-col items-center gap-3 md:flex-row md:justify-between">
        <p id="info" class="text-sm text-slate-500"></p>
        <div id="pagination" class="flex flex-wrap gap-2"></div>
    </div>
@endsection

@push('addon-script')
    <script>
        const ALUR = @json(\App\Models\Order::ALUR);
        const GARIS = { antrian: 'border-l-slate-400', dicuci: 'border-l-blue-500', disetrika: 'border-l-amber-500', siap_diambil: 'border-l-emerald-500', diambil: 'border-l-gray-300', batal: 'border-l-red-500' };
        let filter = { status: '', start: '', end: '' }, orders = {};

        function sisaWaktu(o) {
            if (['diambil', 'batal'].includes(o.status)) return `<span class="text-slate-400">${formatTanggalIndo(o.tanggal_diambil ?? o.updated_at)}</span>`;
            if (o.status === 'siap_diambil') return '<span class="font-medium text-emerald-600"><i class="fa-solid fa-bell mr-1"></i>Menunggu diambil</span>';
            const jam = Math.round((new Date(o.estimasi_selesai) - Date.now()) / 3600e3);
            const teks = Math.abs(jam) >= 24 ? `${Math.round(Math.abs(jam) / 24)} hari` : `${Math.abs(jam)} jam`;
            return jam < 0
                ? `<span class="font-semibold text-red-600"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Terlambat ${teks}</span>`
                : `<span class="text-slate-500"><i class="fa-regular fa-clock mr-1"></i>Selesai ${jam === 0 ? '< 1 jam' : teks + ' lagi'}</span>`;
        }

        function tombolLanjut(o) {
            const i = ALUR.indexOf(o.status);
            if (i < 0 || o.status === 'diambil') return '';
            const next = ALUR[i + 1];
            if (next === 'diambil' && o.status_bayar !== 'lunas') {
                return `<a href="{{ url('order') }}/${o.id}" class="flex h-10 items-center rounded-xl bg-amber-500 px-3 text-xs font-semibold text-white">Lunasi & ambil</a>`;
            }
            return `<button type="button" onclick="lanjut(${o.id}, '${next}')" class="flex h-10 items-center gap-1 rounded-xl bg-slate-800 px-3 text-xs font-semibold text-white hover:bg-slate-900">
                ${esc(LABEL_ORDER[next])} <i class="fa-solid fa-arrow-right"></i></button>`;
        }

        function tiket(o) {
            orders[o.id] = o;
            return `
            <div class="rounded-2xl border border-l-[6px] border-slate-200 ${GARIS[o.status]} bg-white shadow-sm">
                <a href="{{ url('order') }}/${o.id}" class="block p-4">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-semibold text-slate-500">${esc(o.kode)}</span>
                        ${badgeOrder(o.status)}
                    </div>
                    <p class="mt-2 truncate text-lg font-semibold text-slate-800">${esc(o.pelanggan?.nama)}</p>
                    <p class="mt-1 text-sm">${sisaWaktu(o)}</p>
                </a>
                <div class="flex items-center justify-between gap-2 border-t border-dashed border-slate-200 px-4 py-3">
                    <div>
                        <p class="font-bold text-slate-800">${formatRupiah(o.total)}</p>
                        ${badgeOrder(o.status_bayar)}
                    </div>
                    <div class="flex gap-2">
                        ${o.wa_url ? `<a href="${esc(o.wa_url)}" target="_blank" title="Kirim WA: laundry selesai" class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-600 text-white hover:bg-green-700"><i class="fa-brands fa-whatsapp text-lg"></i></a>` : ''}
                        ${tombolLanjut(o)}
                    </div>
                </div>
            </div>`;
        }

        function loadData(page = 1) {
            ajaxRequest({
                url: "{{ route('order.data') }}",
                data: { page, ...filter, search: $('#search').val(), status_bayar: $('#statusBayar').val() },
                before: () => $('#tiket').html('<div class="h-44 animate-pulse rounded-2xl bg-slate-200/60"></div>'.repeat(6)),
                success(res) {
                    $('[data-count]').each(function () {
                        const v = this.dataset.count;
                        $(this).text(v ? (res.counts[v] ?? 0) : Object.values(res.counts).reduce((a, b) => a + b, 0));
                    });
                    $('#tiket').html(res.data.length ? res.data.map(tiket).join('') : `
                        <div class="col-span-full flex flex-col items-center rounded-2xl border-2 border-dashed border-slate-200 py-16 text-center">
                            <i class="fa-solid fa-jug-detergent text-5xl text-slate-200"></i>
                            <h3 class="mt-4 font-semibold text-slate-700">Tidak ada order</h3>
                            <p class="mt-1 text-sm text-slate-400">Belum ada cucian di kategori ini.</p>
                            <a href="{{ route('order.create') }}" class="mt-4 flex h-11 items-center rounded-xl bg-primary px-5 text-sm font-semibold text-white">+ Order Baru</a>
                        </div>`);
                    renderInfo('#info', res);
                    renderPagination(res, 'loadData', '#pagination');
                },
                error: () => $('#tiket').html('<p class="col-span-full py-14 text-center text-red-500">Gagal mengambil data.</p>'),
            });
        }

        function lanjut(id, status) {
            ajaxRequest({
                url: `{{ url('order') }}/${id}/status`, type: 'POST', data: { _method: 'PATCH', status },
                success(res) { showToast(res.message); loadData(); },
                error: (xhr) => Swal.fire({ icon: 'error', title: 'Gagal', text: Object.values(xhr.responseJSON?.errors ?? {})[0]?.[0] ?? xhr.responseJSON?.message }),
            });
        }

        $('.status-tab').on('click', function () {
            $('.status-tab').removeClass('border-slate-800 bg-slate-800 text-white').addClass('border-slate-200 bg-white text-slate-600');
            $(this).addClass('border-slate-800 bg-slate-800 text-white').removeClass('border-slate-200 bg-white text-slate-600');
            filter.status = this.dataset.status;
            loadData();
        });

        initDateRangePicker('#tanggal', (start, end) => {
            filter.start = start;
            filter.end = end;
            if ((start && end) || (!start && !end)) loadData();
        });

        $('#statusBayar').on('change', () => loadData());
        debounceSearch('#search', () => loadData());

        // ?status=xxx dari dashboard → buka tab itu
        const statusAwal = new URLSearchParams(location.search).get('status');
        const tabAwal = $('.status-tab').filter((_, el) => statusAwal && el.dataset.status === statusAwal);
        tabAwal.length ? tabAwal.trigger('click') : loadData();
    </script>
@endpush
