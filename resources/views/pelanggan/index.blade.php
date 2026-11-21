@extends('layouts.app')

@section('title', 'Pelanggan - ' . config('app.name'))
@section('page_title', 'Buku Pelanggan')
@section('page_description', 'Kontak pelanggan laundry')

@section('content')
    <div class="grid gap-6 lg:grid-cols-5">

        {{-- LIST --}}
        <div class="lg:col-span-3">
            <div class="sticky top-[72px] z-10 -mx-4 bg-slate-50/95 px-4 py-3 backdrop-blur md:-mx-6 md:px-6 lg:mx-0 lg:px-0">
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="search" type="search" placeholder="Cari nama atau no HP..."
                            class="h-12 w-full rounded-2xl border-slate-200 pl-11 text-base shadow-sm focus:border-emerald-500 focus:ring-emerald-500 md:text-sm">
                    </div>
                    <button type="button" onclick="formPelanggan()"
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 md:w-auto md:gap-2 md:px-5">
                        <i class="fa-solid fa-user-plus"></i><span class="hidden text-sm font-semibold md:inline">Pelanggan</span>
                    </button>
                </div>
                <p class="mt-2 px-1 text-xs text-slate-400"><span id="total">0</span> pelanggan</p>
            </div>

            <div id="list" class="overflow-hidden rounded-2xl border border-slate-200 bg-white"></div>

            <button id="btnMore" type="button" onclick="loadData(nextPage)"
                class="mt-4 hidden h-11 w-full rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50">
                Muat lebih banyak
            </button>
        </div>

        {{-- DETAIL (desktop: panel kanan, HP: modal) --}}
        <aside class="hidden lg:col-span-2 lg:block">
            <div id="detailDesktop" class="sticky top-24"></div>
        </aside>
    </div>

    <div id="modalDetail" class="modal-overlay hidden opacity-0 fixed inset-0 z-50 bg-black/40 p-4 transition-opacity duration-300 lg:!hidden">
        <div class="modal-content w-full max-w-md scale-95 -translate-y-6 transition-all duration-300" id="detailMobile"></div>
    </div>

    {{-- MODAL FORM --}}
    <div id="modalPelanggan" class="modal-overlay hidden opacity-0 fixed inset-0 z-50 bg-black/40 p-4 transition-opacity duration-300">
        <div class="modal-content w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <form id="formPelanggan">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="font-semibold text-slate-800" id="modalTitle">Tambah Pelanggan</h3>
                    <button type="button" onclick="closeModal('modalPelanggan')" class="h-11 w-11 rounded-xl hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="space-y-4 px-6 py-5">
                    <input type="hidden" name="id">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                        <input name="nama" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                        <p class="err mt-1 text-xs text-red-600" data-for="nama"></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">No HP / WhatsApp</label>
                        <input name="no_hp" type="tel" placeholder="08xxxxxxxxxx" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                        <p class="err mt-1 text-xs text-red-600" data-for="no_hp"></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Alamat <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input name="alamat" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                        <p class="err mt-1 text-xs text-red-600" data-for="alamat"></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input name="catatan" placeholder="mis. alergi parfum, minta lipat rapi" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                        <p class="err mt-1 text-xs text-red-600" data-for="catatan"></p>
                    </div>
                </div>
                <div class="flex gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" onclick="closeModal('modalPelanggan')" class="h-11 flex-1 rounded-xl border border-slate-200 text-sm font-medium text-slate-600">Batal</button>
                    <button type="submit" class="h-11 flex-1 rounded-xl bg-emerald-600 text-sm font-semibold text-white hover:bg-emerald-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('addon-script')
    <script>
        let pelanggan = {}, nextPage = null, lastHuruf = null, selectedId = null;

        const huruf = (nama) => /[a-z]/i.test(nama[0]) ? nama[0].toUpperCase() : '#';

        function row(p) {
            pelanggan[p.id] = p;
            let header = '';
            if (huruf(p.nama) !== lastHuruf) {
                lastHuruf = huruf(p.nama);
                header = `<div class="bg-slate-50 px-5 py-1.5 text-xs font-bold text-emerald-700">${lastHuruf}</div>`;
            }
            return header + `
            <button type="button" onclick="detail(${p.id})" data-row="${p.id}"
                class="flex w-full items-center gap-4 border-b border-slate-100 px-5 py-3 text-left hover:bg-emerald-50/50">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 font-semibold text-emerald-700">${esc(huruf(p.nama))}</span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate font-medium text-slate-800">${esc(p.nama)}</span>
                    <span class="block text-sm text-slate-400">${formatHp(p.no_hp)}</span>
                </span>
                ${p.catatan ? '<i class="fa-regular fa-note-sticky text-amber-400" title="Ada catatan"></i>' : ''}
                <i class="fa-solid fa-chevron-right text-xs text-slate-300"></i>
            </button>`;
        }

        function detailHtml(p) {
            const info = (icon, val) => val ? `<p class="flex gap-3 text-sm text-slate-600"><i class="fa-solid ${icon} mt-0.5 w-4 text-slate-400"></i><span>${esc(val)}</span></p>` : '';
            return `
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 px-6 pb-6 pt-8 text-center text-white">
                    <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white/20 text-3xl font-bold">${esc(huruf(p.nama))}</span>
                    <h3 class="mt-3 text-lg font-semibold">${esc(p.nama)}</h3>
                    <p class="text-emerald-100">${formatHp(p.no_hp)}</p>
                </div>
                <div class="grid grid-cols-2 gap-px bg-slate-100">
                    <a href="https://wa.me/${esc(p.no_hp)}" target="_blank" class="flex h-14 items-center justify-center gap-2 bg-white text-sm font-medium text-green-600 hover:bg-green-50">
                        <i class="fa-brands fa-whatsapp text-lg"></i> WhatsApp</a>
                    <a href="tel:+${esc(p.no_hp)}" class="flex h-14 items-center justify-center gap-2 bg-white text-sm font-medium text-sky-600 hover:bg-sky-50">
                        <i class="fa-solid fa-phone"></i> Telepon</a>
                </div>
                <div class="space-y-3 p-5">
                    ${info('fa-location-dot', p.alamat)}
                    ${info('fa-note-sticky', p.catatan)}
                    <p class="flex gap-3 text-sm text-slate-400"><i class="fa-regular fa-calendar mt-0.5 w-4"></i>Pelanggan sejak ${formatTanggalIndo(p.created_at)}</p>
                    <div class="riwayat-order rounded-xl bg-slate-50 p-3"><div class="h-10 animate-pulse rounded-lg bg-slate-200"></div></div>
                </div>
                <div class="flex gap-2 border-t border-slate-100 p-4">
                    <button type="button" onclick="formPelanggan(${p.id})" class="h-11 flex-1 rounded-xl bg-slate-100 text-sm font-medium text-slate-700 hover:bg-slate-200"><i class="fa-solid fa-pen mr-1"></i> Edit</button>
                    <button type="button" onclick="hapusPelanggan(${p.id})" class="h-11 w-12 rounded-xl text-red-600 hover:bg-red-50"><i class="fa-regular fa-trash-can"></i></button>
                </div>
            </div>`;
        }

        const detailKosong = `
            <div class="flex flex-col items-center rounded-2xl border-2 border-dashed border-slate-200 px-6 py-20 text-center">
                <i class="fa-regular fa-address-book text-5xl text-emerald-200"></i>
                <p class="mt-4 text-sm text-slate-400">Pilih pelanggan untuk melihat detail</p>
            </div>`;

        function detail(id) {
            selectedId = id;
            $('[data-row]').removeClass('bg-emerald-50');
            $(`[data-row=${id}]`).addClass('bg-emerald-50');
            const html = detailHtml(pelanggan[id]);
            $('#detailDesktop').html(html);
            if (window.innerWidth < 1024) {
                $('#detailMobile').html(html);
                showModal('modalDetail');
            }
            muatRiwayat(id);
        }

        function muatRiwayat(id) {
            ajaxRequest({
                url: `{{ url('pelanggan') }}/${id}/orders`,
                success(res) {
                    const list = res.data.map(o => `
                        <a href="{{ url('order') }}/${o.id}" class="flex items-center justify-between gap-2 rounded-lg px-2 py-2 hover:bg-white">
                            <span><span class="block font-mono text-xs text-slate-500">${esc(o.kode)}</span>${badgeOrder(o.status)}</span>
                            <span class="text-right text-sm font-semibold text-slate-700">${formatRupiah(o.total)}<span class="block">${badgeOrder(o.status_bayar)}</span></span>
                        </a>`).join('');
                    $('.riwayat-order').html(`
                        <div class="mb-2 grid grid-cols-2 gap-2 text-center">
                            <div class="rounded-lg bg-white p-2"><p class="text-lg font-bold text-slate-800">${res.jumlah}</p><p class="text-[11px] text-slate-400">order</p></div>
                            <div class="rounded-lg bg-white p-2"><p class="text-sm font-bold text-emerald-700">${formatRupiah(res.total)}</p><p class="text-[11px] text-slate-400">total belanja</p></div>
                        </div>
                        ${list || '<p class="py-2 text-center text-xs text-slate-400">Belum pernah order.</p>'}`);
                },
            });
        }

        function loadData(page = 1) {
            ajaxRequest({
                url: "{{ route('pelanggan.data') }}",
                data: { page, search: $('#search').val() },
                before() {
                    if (page === 1) {
                        lastHuruf = null;
                        $('#list').html('<div class="h-16 animate-pulse border-b border-slate-100 bg-slate-50"></div>'.repeat(6));
                    }
                },
                success(res) {
                    const html = res.data.map(row).join('');
                    if (page === 1) {
                        $('#list').html(html || `
                            <div class="flex flex-col items-center px-6 py-16 text-center">
                                <i class="fa-solid fa-user-group text-5xl text-emerald-100"></i>
                                <h3 class="mt-4 font-semibold text-slate-700">Belum ada pelanggan</h3>
                                <p class="mt-1 text-sm text-slate-400">Pelanggan juga bisa ditambah langsung saat membuat order.</p>
                            </div>`);
                    } else {
                        $('#list').append(html);
                    }
                    $('#total').text(res.total);
                    nextPage = res.current_page < res.last_page ? res.current_page + 1 : null;
                    $('#btnMore').toggleClass('hidden', !nextPage);
                    if (page === 1 && !pelanggan[selectedId]) $('#detailDesktop').html(detailKosong);
                },
            });
        }

        function formPelanggan(id = null) {
            const f = $('#formPelanggan')[0], p = id ? pelanggan[id] : null;
            f.reset();
            $(f).find('.err').text('');
            $('#modalTitle').text(p ? 'Edit Pelanggan' : 'Tambah Pelanggan');
            f.id.value = p?.id ?? '';
            if (p) {
                f.nama.value = p.nama;
                f.no_hp.value = String(p.no_hp).replace(/^62/, '0');
                f.alamat.value = p.alamat ?? '';
                f.catatan.value = p.catatan ?? '';
            }
            closeModal('modalDetail');
            showModal('modalPelanggan');
        }

        $('#formPelanggan').on('submit', function (e) {
            e.preventDefault();
            const id = this.id.value;
            ajaxRequest({
                url: id ? `{{ url('pelanggan') }}/${id}` : "{{ route('pelanggan.store') }}",
                type: 'POST',
                data: $(this).serialize() + (id ? '&_method=PUT' : ''),
                success(res) {
                    App.loading.hide();
                    closeModal('modalPelanggan');
                    showToast(res.message);
                    pelanggan[res.data.id] = res.data;
                    selectedId = res.data.id;
                    $('#detailDesktop').html(detailHtml(res.data));
                    muatRiwayat(res.data.id);
                    loadData();
                },
                error: (xhr) => formError(xhr, this),
            });
        });

        function hapusPelanggan(id) {
            closeModal('modalDetail');
            hapusData({
                url: `{{ url('pelanggan') }}/${id}`, name: esc(pelanggan[id].nama),
                reload() { delete pelanggan[id]; selectedId = null; loadData(); },
            });
        }

        debounceSearch('#search', () => loadData());
        loadData();
    </script>
@endpush
