@extends('layouts.app')

@section('title', 'Pengeluaran - ' . config('app.name'))
@section('page_title', 'Buku Pengeluaran')
@section('page_description', 'Catat uang keluar harian: bahan, listrik, gaji, dll')

@section('content')
    {{-- KEPALA BUKU: navigasi bulan + total --}}
    <div class="rounded-2xl border border-rose-100 bg-gradient-to-br from-rose-50 to-white p-4 md:p-6">
        <div class="flex items-center gap-2">
            <button type="button" onclick="geserBulan(-1)" aria-label="Bulan sebelumnya"
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-rose-600 shadow-sm hover:bg-rose-100"><i class="fa-solid fa-chevron-left"></i></button>
            <label class="relative flex h-11 min-w-0 flex-1 cursor-pointer items-center justify-center rounded-xl bg-white px-3 shadow-sm md:flex-none md:px-6">
                <i class="fa-regular fa-calendar mr-2 text-rose-400"></i>
                <span id="labelBulan" class="truncate font-semibold text-slate-800">-</span>
                <input type="month" id="bulan" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" max="{{ today()->format('Y-m') }}">
            </label>
            <button type="button" onclick="geserBulan(1)" id="btnNext" aria-label="Bulan berikutnya"
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-rose-600 shadow-sm hover:bg-rose-100 disabled:opacity-30"><i class="fa-solid fa-chevron-right"></i></button>
            <div class="ml-auto hidden gap-2 md:flex">
                <button type="button" onclick="showModal('modalKategori')" class="flex h-11 items-center gap-2 rounded-xl border border-rose-200 bg-white px-4 text-sm font-medium text-rose-700 hover:bg-rose-50">
                    <i class="fa-solid fa-palette"></i>Kategori
                </button>
                <button type="button" onclick="formPengeluaran()" class="flex h-11 items-center gap-2 rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white hover:bg-rose-700">
                    <i class="fa-solid fa-pen-to-square"></i>Catat Pengeluaran
                </button>
            </div>
        </div>

        <div class="mt-5 flex items-end justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm text-rose-700"><i class="fa-solid fa-arrow-trend-down mr-1"></i>Total keluar bulan ini</p>
                <p id="total" class="mt-1 break-words text-3xl font-bold tracking-tight text-rose-700 md:text-4xl">-</p>
            </div>
            <button type="button" onclick="showModal('modalKategori')" class="flex h-11 shrink-0 items-center gap-2 rounded-xl bg-white px-3 text-sm font-medium text-rose-700 shadow-sm md:hidden">
                <i class="fa-solid fa-palette"></i>Kategori
            </button>
        </div>

        {{-- komposisi per kategori --}}
        <div id="bar" class="mt-4 flex h-3 overflow-hidden rounded-full bg-rose-100"></div>
        <div class="-mx-4 mt-3 overflow-x-auto px-4 md:mx-0 md:px-0">
            <div id="chips" class="flex min-w-max gap-2 md:min-w-0 md:flex-wrap"></div>
        </div>
    </div>

    {{-- JURNAL per tanggal --}}
    <div id="jurnal" class="mt-6 space-y-5"></div>

    {{-- FAB HP --}}
    <button type="button" onclick="formPengeluaran()" aria-label="Catat pengeluaran"
        class="fixed bottom-[calc(5rem+env(safe-area-inset-bottom))] right-4 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-rose-600 text-white shadow-lg shadow-rose-600/30 md:hidden">
        <i class="fa-solid fa-pen-to-square text-xl"></i>
    </button>

    {{-- MODAL PENGELUARAN --}}
    <div id="modalPengeluaran" class="modal-overlay hidden opacity-0 fixed inset-0 z-50 bg-black/40 p-4 transition-opacity duration-300">
        <div class="modal-content w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <form id="formPengeluaran">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="font-semibold text-slate-800" id="modalTitle">Catat Pengeluaran</h3>
                    <button type="button" onclick="closeModal('modalPengeluaran')" class="h-11 w-11 rounded-xl hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div class="max-h-[70vh] space-y-4 overflow-y-auto px-6 py-5">
                    <input type="hidden" name="id">

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Jumlah</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-semibold text-rose-400">Rp</span>
                            <input name="jumlah" inputmode="numeric" autocomplete="off" placeholder="0"
                                class="h-14 w-full rounded-xl border-slate-200 pl-12 text-2xl font-bold text-rose-700 focus:border-rose-500 focus:ring-rose-500">
                        </div>
                        <p class="err mt-1 text-xs text-red-600" data-for="jumlah"></p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kategori</label>
                        <div id="pilihKategori" class="flex flex-wrap gap-2"></div>
                        <p class="err mt-1 text-xs text-red-600" data-for="kategori_pengeluaran_id"></p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal</label>
                            <input type="date" name="tanggal" max="{{ today()->toDateString() }}"
                                class="h-11 w-full rounded-xl border-slate-200 text-base focus:border-rose-500 focus:ring-rose-500 md:text-sm">
                            <p class="err mt-1 text-xs text-red-600" data-for="tanggal"></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Foto nota <span class="font-normal text-slate-400">(opsional)</span></label>
                            <label class="flex h-11 cursor-pointer items-center gap-2 rounded-xl border border-dashed border-slate-300 px-3 text-sm text-slate-500 hover:bg-slate-50">
                                <img id="notaPreview" class="hidden h-8 w-8 rounded object-cover" alt="">
                                <i id="notaIcon" class="fa-solid fa-camera text-slate-400"></i>
                                <span id="notaLabel" class="truncate">Pilih / foto nota</span>
                                <input type="file" name="foto_nota" accept="image/*" class="sr-only">
                            </label>
                            <p class="err mt-1 text-xs text-red-600" data-for="foto_nota"></p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Keterangan</label>
                        <input name="keterangan" maxlength="255" placeholder="mis. Deterjen 5 L, token listrik" autocomplete="off"
                            class="h-11 w-full rounded-xl border-slate-200 text-base focus:border-rose-500 focus:ring-rose-500 md:text-sm">
                        <p class="err mt-1 text-xs text-red-600" data-for="keterangan"></p>
                    </div>
                </div>

                <div class="flex gap-2 border-t border-slate-100 px-6 py-4">
                    <button type="button" onclick="closeModal('modalPengeluaran')" class="h-11 flex-1 rounded-xl border border-slate-200 font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="h-11 flex-1 rounded-xl bg-rose-600 font-semibold text-white hover:bg-rose-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL KATEGORI --}}
    <div id="modalKategori" class="modal-overlay hidden opacity-0 fixed inset-0 z-50 bg-black/40 p-4 transition-opacity duration-300">
        <div class="modal-content w-full max-w-md scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="font-semibold text-slate-800"><i class="fa-solid fa-palette mr-2 text-rose-500"></i>Kategori Pengeluaran</h3>
                <button type="button" onclick="closeModal('modalKategori')" class="h-11 w-11 rounded-xl hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="px-6 py-5">
                <form id="formKategori" class="flex gap-2">
                    <div class="flex-1">
                        <input name="nama" maxlength="50" placeholder="Kategori baru, mis. Bensin" autocomplete="off"
                            class="h-11 w-full rounded-xl border-slate-200 text-base focus:border-rose-500 focus:ring-rose-500 md:text-sm">
                        <p class="err mt-1 text-xs text-red-600" data-for="nama"></p>
                    </div>
                    <button type="submit" aria-label="Tambah kategori" class="h-11 w-11 shrink-0 rounded-xl bg-rose-600 text-white hover:bg-rose-700"><i class="fa-solid fa-plus"></i></button>
                </form>
                <ul id="listKategori" class="mt-4 max-h-[50vh] divide-y divide-slate-100 overflow-y-auto"></ul>
            </div>
        </div>
    </div>
@endsection

@push('addon-script')
    <script>
        const BASE = "{{ url('pengeluaran') }}";
        const HARI_INI = "{{ today()->toDateString() }}";
        let state = { bulan: "{{ today()->format('Y-m') }}", kategori: '' };
        let kategori = {}, items = {};

        const namaBulan = (ym) => new Date(ym + '-01T00:00').toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        const namaHari = (tgl) => new Date(tgl + 'T00:00').toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'short' });
        const dot = (k) => `<span class="inline-block h-2.5 w-2.5 shrink-0 rounded-full" style="background:${k?.warna ?? '#cbd5e1'}"></span>`;

        function geserBulan(n) {
            const [y, m] = state.bulan.split('-').map(Number);
            const d = new Date(y, m - 1 + n, 1);
            const ym = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
            if (ym > HARI_INI.slice(0, 7)) return;
            state.bulan = ym;
            loadData();
        }

        $('#bulan').on('change', function () {
            if (!this.value) return;
            state.bulan = this.value;
            loadData();
        });

        function filterKategori(id) {
            state.kategori = state.kategori == id ? '' : id;
            loadData();
        }

        function renderRingkasan(res) {
            const total = res.total;
            $('#total').text(formatRupiah(total));
            $('#bar').html(res.perKategori.map(p =>
                `<div title="${esc(kategori[p.id]?.nama)}" style="width:${p.total / total * 100}%;background:${kategori[p.id]?.warna}"></div>`).join(''));

            const chip = (id, label, nilai, warna) => {
                const aktif = String(state.kategori) === String(id);
                return `<button type="button" onclick="filterKategori('${id}')"
                    class="flex h-11 shrink-0 items-center gap-2 rounded-full border px-4 text-sm transition
                    ${aktif ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'}">
                    ${warna ? `<span class="h-2.5 w-2.5 rounded-full" style="background:${warna}"></span>` : ''}
                    <span>${esc(label)}</span>${nilai !== null ? `<span class="font-semibold">${formatRupiah(nilai, false)}</span>` : ''}
                </button>`;
            };
            $('#chips').html(chip('', 'Semua', null, null) +
                res.perKategori.map(p => chip(p.id, kategori[p.id]?.nama, p.total, kategori[p.id]?.warna)).join(''));
        }

        function renderJurnal(list) {
            if (!list.length) {
                $('#jurnal').html(`
                    <div class="flex flex-col items-center rounded-2xl border-2 border-dashed border-rose-100 bg-white py-14 text-center">
                        <span class="flex h-20 w-20 items-center justify-center rounded-full bg-rose-50"><i class="fa-solid fa-receipt text-4xl text-rose-200"></i></span>
                        <h3 class="mt-4 font-semibold text-slate-700">Buku masih bersih</h3>
                        <p class="mt-1 max-w-xs text-sm text-slate-400">Belum ada pengeluaran ${state.kategori ? 'di kategori ini ' : ''}pada ${namaBulan(state.bulan)}.</p>
                        <button type="button" onclick="formPengeluaran()" class="mt-4 h-11 rounded-xl bg-rose-600 px-5 text-sm font-semibold text-white hover:bg-rose-700">Catat pengeluaran</button>
                    </div>`);
                return;
            }

            const perTanggal = {};
            list.forEach(p => (perTanggal[p.tanggal] ??= []).push(p));

            $('#jurnal').html(Object.entries(perTanggal).map(([tgl, rows]) => `
                <section>
                    <div class="mb-2 flex items-baseline justify-between gap-2 px-1">
                        <h3 class="text-sm font-semibold text-slate-700">${tgl === HARI_INI ? 'Hari ini · ' : ''}${namaHari(tgl)}</h3>
                        <p class="text-sm font-semibold text-rose-600">${formatRupiah(rows.reduce((s, p) => s + p.jumlah, 0))}</p>
                    </div>
                    <div class="divide-y divide-dashed divide-slate-200 rounded-2xl border border-slate-200 bg-white">
                        ${rows.map(baris).join('')}
                    </div>
                </section>`).join(''));
        }

        function baris(p) {
            items[p.id] = p;
            const k = kategori[p.kategori_pengeluaran_id];
            return `
            <div class="flex items-center gap-3 py-2 pl-4 pr-2">
                ${p.foto_url
                    ? `<a href="${esc(p.foto_url)}" target="_blank" rel="noopener" class="h-11 w-11 shrink-0 overflow-hidden rounded-lg border border-slate-200" title="Lihat nota"><img src="${esc(p.foto_url)}" class="h-full w-full object-cover" alt="Nota"></a>`
                    : `<span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg" style="background:${k?.warna ?? '#cbd5e1'}1a">${dot(k)}</span>`}
                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium text-slate-800">${esc(p.keterangan || k?.nama || '-')}</p>
                    <p class="truncate text-xs text-slate-400">${esc(k?.nama ?? '-')} · ${esc(p.user?.name ?? '-')}</p>
                </div>
                <p class="shrink-0 text-right font-semibold text-slate-800">${formatRupiah(p.jumlah, false)}</p>
                <div class="flex shrink-0">
                    <button type="button" onclick="formPengeluaran(${p.id})" aria-label="Ubah" class="h-11 w-10 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700"><i class="fa-solid fa-pen text-xs"></i></button>
                    <button type="button" onclick="hapusPengeluaran(${p.id})" aria-label="Hapus" class="h-11 w-10 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-trash text-xs"></i></button>
                </div>
            </div>`;
        }

        function renderKategori(list) {
            kategori = Object.fromEntries(list.map(k => [k.id, k]));
            $('#listKategori').html(list.length ? list.map(k => `
                <li class="flex items-center gap-3 py-1">
                    ${dot(k)}
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-800">${esc(k.nama)}</p>
                        <p class="text-xs text-slate-400">${k.pengeluaran_count ? `dipakai ${k.pengeluaran_count}×` : 'belum dipakai'}</p>
                    </div>
                    <button type="button" onclick="renameKategori(${k.id})" aria-label="Ubah nama" class="h-11 w-10 rounded-lg text-slate-400 hover:bg-slate-100"><i class="fa-solid fa-pen text-xs"></i></button>
                    <button type="button" onclick="hapusData({url: BASE + '/kategori/${k.id}', name: esc(kategori[${k.id}].nama), reload: loadData})" aria-label="Hapus"
                        class="h-11 w-10 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-trash text-xs"></i></button>
                </li>`).join('') : '<li class="py-6 text-center text-sm text-slate-400">Belum ada kategori.</li>');

            $('#pilihKategori').html(list.map(k => `
                <label class="cursor-pointer">
                    <input type="radio" name="kategori_pengeluaran_id" value="${k.id}" class="peer sr-only">
                    <span class="flex h-11 items-center gap-2 rounded-full border border-slate-200 px-4 text-sm text-slate-600 peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:font-semibold peer-checked:text-rose-700">
                        ${dot(k)}${esc(k.nama)}
                    </span>
                </label>`).join(''));
        }

        function loadData() {
            $('#labelBulan').text(namaBulan(state.bulan));
            $('#bulan').val(state.bulan);
            $('#btnNext').prop('disabled', state.bulan >= HARI_INI.slice(0, 7));
            $('#jurnal').html('<div class="h-40 animate-pulse rounded-2xl bg-slate-100"></div>');

            ajaxRequest({
                url: "{{ route('pengeluaran.data') }}", data: state,
                success(res) {
                    items = {};
                    renderKategori(res.kategori);
                    renderRingkasan(res);
                    renderJurnal(res.items);
                },
                error: () => $('#jurnal').html('<p class="py-10 text-center text-sm text-red-500">Gagal memuat data. Coba muat ulang.</p>'),
            });
        }

        // ---- form pengeluaran ----
        const form = document.getElementById('formPengeluaran');

        function setNota(src, label) {
            $('#notaPreview').toggleClass('hidden', !src).attr('src', src || '');
            $('#notaIcon').toggleClass('hidden', !!src);
            $('#notaLabel').text(label);
        }

        function formPengeluaran(id = null) {
            const p = id ? items[id] : null;
            form.reset();
            $(form).find('.err').text('');
            $('#modalTitle').text(p ? 'Ubah Pengeluaran' : 'Catat Pengeluaran');
            form.id.value = p?.id ?? '';
            form.jumlah.value = p ? formatRupiah(p.jumlah, false) : '';
            form.tanggal.value = p?.tanggal ?? HARI_INI;
            form.keterangan.value = p?.keterangan ?? '';
            const kat = p?.kategori_pengeluaran_id ?? (state.kategori || '');
            $(form).find(`[name=kategori_pengeluaran_id][value="${kat}"]`).prop('checked', true);
            p?.foto_url ? setNota(p.foto_url, 'Ganti foto nota') : setNota(null, 'Pilih / foto nota');
            showModal('modalPengeluaran');
        }

        $(form.jumlah).on('input', function () { this.value = this.value ? formatRupiah(getNominal(this.value), false) : ''; });
        $(form.foto_nota).on('change', function () {
            const f = this.files[0];
            if (f) setNota(URL.createObjectURL(f), f.name);
        });

        // Upload file: FormData + processData/contentType false (ajaxRequest tidak mendukung), CSRF dari $.ajaxSetup
        $(form).on('submit', function (e) {
            e.preventDefault();
            const id = this.id.value;
            const fd = new FormData(this);
            fd.delete('id');
            fd.set('jumlah', getNominal(this.jumlah.value));
            if (!this.foto_nota.files.length) fd.delete('foto_nota');
            if (id) fd.append('_method', 'PUT');

            $.ajax({
                url: id ? `${BASE}/${id}` : "{{ route('pengeluaran.store') }}",
                type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
                success(res) {
                    App.loading.hide();
                    closeModal('modalPengeluaran');
                    showToast(res.message);
                    loadData();
                },
                error: (xhr) => formError(xhr, this),
            });
        });

        function hapusPengeluaran(id) {
            const p = items[id];
            hapusData({ url: `${BASE}/${id}`, name: `${esc(p.keterangan || kategori[p.kategori_pengeluaran_id]?.nama || 'pengeluaran')} (${formatRupiah(p.jumlah)})`, reload: loadData });
        }

        // ---- kategori ----
        $('#formKategori').on('submit', function (e) {
            e.preventDefault();
            ajaxRequest({
                url: "{{ route('pengeluaran.kategori.store') }}", type: 'POST', data: $(this).serialize(),
                success: (res) => {
                    App.loading.hide();
                    this.reset();
                    $(this).find('.err').text('');
                    showToast(res.message);
                    loadData();
                },
                error: (xhr) => formError(xhr, this),
            });
        });

        async function renameKategori(id) {
            const { value } = await Swal.fire({
                title: 'Ubah nama kategori', input: 'text', inputValue: kategori[id].nama,
                showCancelButton: true, confirmButtonText: 'Simpan', cancelButtonText: 'Batal', confirmButtonColor: '#e11d48',
            });
            if (!value) return;
            ajaxRequest({
                url: `${BASE}/kategori/${id}`, type: 'POST', data: { _method: 'PUT', nama: value },
                success(res) { showToast(res.message); loadData(); },
                error: (xhr) => Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.errors?.nama?.[0] ?? xhr.responseJSON?.message }),
            });
        }

        loadData();
    </script>
@endpush
