@extends('layouts.app')

@section('title', 'Layanan - ' . config('app.name'))
@section('page_title', 'Daftar Harga Layanan')
@section('page_description', 'Harga & estimasi waktu tiap layanan')

@section('content')
    {{-- TOOLBAR --}}
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-600"><i class="fa-solid fa-tags text-xl"></i></span>
            <div>
                <p class="text-sm text-slate-500">Layanan aktif</p>
                <p class="text-2xl font-bold text-slate-800"><span id="countAktif">-</span><span class="text-base font-normal text-slate-400"> / <span id="countAll">-</span></span></p>
            </div>
        </div>
        <div class="flex gap-2">
            <div class="relative flex-1 md:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input id="search" type="search" placeholder="Cari layanan..."
                    class="h-11 w-full rounded-xl border-slate-200 pl-11 text-base md:text-sm focus:border-sky-500 focus:ring-sky-500">
            </div>
            <button type="button" onclick="formLayanan()"
                class="flex h-11 items-center gap-2 rounded-xl bg-sky-600 px-4 text-sm font-semibold text-white hover:bg-sky-700">
                <i class="fa-solid fa-plus"></i><span class="hidden sm:inline">Layanan</span>
            </button>
        </div>
    </div>

    {{-- BOARD --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        @foreach (['kiloan' => ['fa-weight-scale', 'Kiloan', 'Dihitung per kilogram'], 'satuan' => ['fa-shirt', 'Satuan', 'Dihitung per item']] as $kat => [$icon, $label, $desc])
            <section class="rounded-2xl border border-slate-200 bg-white">
                <div class="flex items-center gap-3 border-b border-dashed border-slate-200 px-5 py-4">
                    <i class="fa-solid {{ $icon }} text-sky-600"></i>
                    <div>
                        <h3 class="font-bold uppercase tracking-wider text-slate-800">{{ $label }}</h3>
                        <p class="text-xs text-slate-400">{{ $desc }}</p>
                    </div>
                </div>
                <div id="list-{{ $kat }}" class="divide-y divide-slate-100"></div>
            </section>
        @endforeach
    </div>

    {{-- MODAL --}}
    <div id="modalLayanan" class="modal-overlay hidden opacity-0 fixed inset-0 z-50 bg-black/40 p-4 transition-opacity duration-300">
        <div class="modal-content w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <form id="formLayanan">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="font-semibold text-slate-800" id="modalTitle">Tambah Layanan</h3>
                    <button type="button" onclick="closeModal('modalLayanan')" class="h-11 w-11 rounded-xl hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div class="space-y-4 px-6 py-5">
                    <input type="hidden" name="id">

                    <div class="grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1">
                        @foreach (['kiloan' => 'Kiloan', 'satuan' => 'Satuan'] as $val => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="kategori" value="{{ $val }}" class="peer sr-only" @checked($val === 'kiloan')>
                                <span class="flex h-10 items-center justify-center rounded-lg text-sm font-medium text-slate-500 peer-checked:bg-white peer-checked:text-sky-700 peer-checked:shadow-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Layanan</label>
                        <input name="nama" placeholder="mis. Cuci Kering Setrika" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                        <p class="err mt-1 text-xs text-red-600" data-for="nama"></p>
                    </div>

                    <div class="grid grid-cols-5 gap-3">
                        <div class="col-span-3">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Harga</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">Rp</span>
                                <input name="harga" inputmode="numeric" class="h-11 w-full rounded-xl border-slate-200 pl-10 text-base font-semibold md:text-sm">
                            </div>
                            <p class="err mt-1 text-xs text-red-600" data-for="harga"></p>
                        </div>
                        <div class="col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Per</label>
                            <select name="satuan" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm"></select>
                            <p class="err mt-1 text-xs text-red-600" data-for="satuan"></p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Estimasi Selesai</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach ([6 => '6 jam', 24 => '1 hari', 48 => '2 hari', 72 => '3 hari'] as $jam => $label)
                                <button type="button" data-jam="{{ $jam }}" class="durasi-chip h-10 rounded-lg border border-slate-200 px-3 text-sm text-slate-600">{{ $label }}</button>
                            @endforeach
                            <div class="relative w-28">
                                <input name="durasi_jam" type="number" min="1" inputmode="numeric" class="h-10 w-full rounded-lg border-slate-200 pr-10 text-base md:text-sm">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">jam</span>
                            </div>
                        </div>
                        <p class="err mt-1 text-xs text-red-600" data-for="durasi_jam"></p>
                    </div>
                </div>

                <div class="flex gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" onclick="closeModal('modalLayanan')" class="h-11 flex-1 rounded-xl border border-slate-200 text-sm font-medium text-slate-600">Batal</button>
                    <button type="submit" class="h-11 flex-1 rounded-xl bg-sky-600 text-sm font-semibold text-white hover:bg-sky-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('addon-script')
    <script>
        const SATUAN = @json(\App\Http\Controllers\LayananController::SATUAN);
        let layanan = {};

        const durasiLabel = (jam) => jam % 24 === 0 ? `${jam / 24} hari` : (jam > 24 ? `${Math.floor(jam / 24)} hari ${jam % 24} jam` : `${jam} jam`);

        function row(l) {
            layanan[l.id] = l;
            return `
            <div class="group flex items-center gap-3 px-5 py-4 ${l.aktif ? '' : 'bg-slate-50'}">
                <div class="min-w-0 flex-1">
                    <p class="truncate font-semibold ${l.aktif ? 'text-slate-800' : 'text-slate-400 line-through'}">${esc(l.nama)}</p>
                    <p class="mt-0.5 text-xs text-slate-400"><i class="fa-regular fa-clock mr-1"></i>${durasiLabel(l.durasi_jam)}</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold ${l.aktif ? 'text-sky-700' : 'text-slate-400'}">${formatRupiah(l.harga)}</p>
                    <p class="text-[11px] text-slate-400">per ${esc(l.satuan)}</p>
                </div>
                <div class="flex shrink-0 gap-1">
                    <button type="button" onclick="toggleLayanan(${l.id})" title="${l.aktif ? 'Sembunyikan dari order' : 'Tampilkan'}"
                        class="h-10 w-10 rounded-lg text-slate-400 hover:bg-slate-100"><i class="fa-regular ${l.aktif ? 'fa-eye' : 'fa-eye-slash'}"></i></button>
                    <button type="button" onclick="formLayanan(${l.id})" class="h-10 w-10 rounded-lg text-slate-400 hover:bg-slate-100"><i class="fa-solid fa-pen"></i></button>
                    <button type="button" onclick="hapusData({url: '{{ url('layanan') }}/${l.id}', name: esc(layanan[${l.id}].nama), reload: loadData})"
                        class="h-10 w-10 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600"><i class="fa-regular fa-trash-can"></i></button>
                </div>
            </div>`;
        }

        const kosong = (kat) => `
            <div class="flex flex-col items-center px-5 py-12 text-center">
                <i class="fa-solid ${kat === 'kiloan' ? 'fa-weight-scale' : 'fa-shirt'} text-4xl text-sky-100"></i>
                <p class="mt-3 text-sm text-slate-400">Belum ada layanan ${kat}.</p>
            </div>`;

        function loadData() {
            ajaxRequest({
                url: "{{ route('layanan.data') }}",
                data: { search: $('#search').val() },
                before: () => $('[id^=list-]').html('<div class="h-16 animate-pulse bg-slate-50"></div>'.repeat(3)),
                success(res) {
                    $('#countAll').text(res.length);
                    $('#countAktif').text(res.filter(l => l.aktif).length);
                    for (const kat of ['kiloan', 'satuan']) {
                        const items = res.filter(l => l.kategori === kat);
                        $(`#list-${kat}`).html(items.length ? items.map(row).join('') : kosong(kat));
                    }
                },
            });
        }

        function isiSatuan(kat, pilih = null) {
            $('[name=satuan]').html(SATUAN[kat].map(s => `<option ${s === pilih ? 'selected' : ''}>${s}</option>`).join(''));
        }

        function pilihDurasi(jam) {
            $('[name=durasi_jam]').val(jam);
            $('.durasi-chip').each(function () {
                $(this).toggleClass('border-sky-600 bg-sky-50 text-sky-700', +this.dataset.jam === +jam);
            });
        }

        function formLayanan(id = null) {
            const f = $('#formLayanan')[0], l = id ? layanan[id] : null;
            f.reset();
            $(f).find('.err').text('');
            $('#modalTitle').text(l ? 'Edit Layanan' : 'Tambah Layanan');
            f.id.value = l?.id ?? '';
            f.nama.value = l?.nama ?? '';
            f.harga.value = l ? formatRupiah(l.harga, false) : '';
            $(f).find(`[name=kategori][value=${l?.kategori ?? 'kiloan'}]`).prop('checked', true);
            isiSatuan(l?.kategori ?? 'kiloan', l?.satuan);
            pilihDurasi(l?.durasi_jam ?? 48);
            showModal('modalLayanan');
        }

        $('[name=kategori]').on('change', function () { isiSatuan(this.value); });
        $('.durasi-chip').on('click', function () { pilihDurasi(this.dataset.jam); });
        $('[name=durasi_jam]').on('input', function () { pilihDurasi(this.value); });
        $('[name=harga]').on('input', function () { this.value = this.value ? formatRupiah(getNominal(this.value), false) : ''; });

        $('#formLayanan').on('submit', function (e) {
            e.preventDefault();
            const id = this.id.value;
            const data = $(this).serializeArray().filter(f => f.name !== 'id').map(f => f.name === 'harga' ? { ...f, value: getNominal(f.value) } : f);
            if (id) data.push({ name: '_method', value: 'PUT' });

            ajaxRequest({
                url: id ? `{{ url('layanan') }}/${id}` : "{{ route('layanan.store') }}",
                type: 'POST',
                data: $.param(data),
                success(res) {
                    App.loading.hide();
                    closeModal('modalLayanan');
                    showToast(res.message);
                    loadData();
                },
                error: (xhr) => formError(xhr, this),
            });
        });

        function toggleLayanan(id) {
            ajaxRequest({
                url: `{{ url('layanan') }}/${id}/toggle`, type: 'POST', data: { _method: 'PATCH' },
                success(res) { showToast(res.message); loadData(); },
            });
        }

        debounceSearch('#search', loadData);
        loadData();
    </script>
@endpush
