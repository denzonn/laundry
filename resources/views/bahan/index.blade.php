@extends('layouts.app')

@section('title', 'Stok Bahan - ' . config('app.name'))
@section('page_title', 'Stok Bahan')
@section('page_description', 'Deterjen, pewangi, plastik & perlengkapan gudang')

@section('content')
    {{-- HERO GUDANG --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-700 to-cyan-800 p-5 text-white md:p-8">
        <i class="fa-solid fa-boxes-stacked absolute -bottom-6 -right-4 text-[120px] text-white/10 md:text-[160px]"></i>
        <div class="relative flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-widest text-teal-200">Gudang</p>
                <h2 class="mt-1 text-2xl font-bold">Rak Bahan Laundry</h2>
                <p class="mt-1 max-w-md text-sm text-teal-100">Catat bahan masuk & pemakaian. Tabung merah berarti stok sudah di bawah batas minimum.</p>
            </div>
            <div class="flex gap-3">
                <div class="min-w-[96px] rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                    <p class="text-2xl font-bold" id="jmlBahan">-</p>
                    <p class="text-xs text-teal-100">jenis bahan</p>
                </div>
                <div class="min-w-[96px] rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                    <p class="text-2xl font-bold text-amber-300" id="jmlMenipis">-</p>
                    <p class="text-xs text-teal-100">menipis</p>
                </div>
                <button type="button" onclick="formBahan()"
                    class="hidden h-auto items-center gap-2 rounded-2xl bg-white px-5 text-sm font-semibold text-teal-800 hover:bg-teal-50 md:flex">
                    <i class="fa-solid fa-plus"></i> Bahan
                </button>
            </div>
        </div>
    </div>

    {{-- RAK --}}
    <div id="rak" class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3"></div>

    {{-- FAB HP --}}
    <button type="button" onclick="formBahan()" aria-label="Tambah bahan"
        class="fixed bottom-20 right-5 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-teal-700 text-white shadow-lg shadow-teal-700/30 md:hidden">
        <i class="fa-solid fa-plus text-xl"></i>
    </button>

    {{-- MODAL MUTASI --}}
    <div id="modalMutasi" class="modal-overlay hidden opacity-0 fixed inset-0 z-50 bg-black/40 p-4 transition-opacity duration-300">
        <div class="modal-content w-full max-w-md scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <form id="formMutasi">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="min-w-0">
                        <h3 class="truncate font-semibold text-slate-800" id="mutasiNama"></h3>
                        <p class="text-xs text-slate-400">Stok sekarang <b id="mutasiStok"></b></p>
                    </div>
                    <button type="button" onclick="closeModal('modalMutasi')" class="h-11 w-11 shrink-0 rounded-xl hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div class="space-y-4 px-5 py-5">
                    <div class="grid grid-cols-3 gap-1 rounded-xl bg-slate-100 p-1">
                        @foreach (\App\Models\Bahan::JENIS as $val => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="jenis" value="{{ $val }}" class="peer sr-only">
                                <span class="flex h-11 items-center justify-center rounded-lg text-sm font-medium text-slate-500 peer-checked:bg-white peer-checked:text-teal-700 peer-checked:shadow-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700" id="labelJumlah">Jumlah</label>
                        <div class="relative">
                            <input name="jumlah" type="number" step="0.01" min="0" inputmode="decimal" autocomplete="off" placeholder="0"
                                class="h-14 w-full rounded-xl border-slate-200 pr-20 text-2xl font-bold text-teal-800 focus:border-teal-500 focus:ring-teal-500">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-400" id="mutasiSatuan"></span>
                        </div>
                        <p class="err mt-1 text-xs text-red-600" data-for="jumlah"></p>
                        <p class="mt-2 text-sm text-slate-500" id="mutasiPreview"></p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Keterangan <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input name="keterangan" maxlength="255" autocomplete="off" placeholder="mis. beli di toko X, dipakai minggu ini"
                            class="h-11 w-full rounded-xl border-slate-200 text-base focus:border-teal-500 focus:ring-teal-500 md:text-sm">
                        <p class="err mt-1 text-xs text-red-600" data-for="keterangan"></p>
                    </div>
                </div>

                <div class="flex gap-2 border-t border-slate-100 px-5 py-4">
                    <button type="button" onclick="closeModal('modalMutasi')" class="h-11 flex-1 rounded-xl border border-slate-200 font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="h-11 flex-1 rounded-xl bg-teal-700 font-semibold text-white hover:bg-teal-800">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL BAHAN --}}
    <div id="modalBahan" class="modal-overlay hidden opacity-0 fixed inset-0 z-50 bg-black/40 p-4 transition-opacity duration-300">
        <div class="modal-content w-full max-w-md scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <form id="formBahan">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h3 class="font-semibold text-slate-800" id="bahanTitle">Tambah Bahan</h3>
                    <button type="button" onclick="closeModal('modalBahan')" class="h-11 w-11 rounded-xl hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div class="space-y-4 px-5 py-5">
                    <input type="hidden" name="id">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama bahan</label>
                        <input name="nama" maxlength="50" autocomplete="off" placeholder="mis. Deterjen cair"
                            class="h-11 w-full rounded-xl border-slate-200 text-base focus:border-teal-500 focus:ring-teal-500 md:text-sm">
                        <p class="err mt-1 text-xs text-red-600" data-for="nama"></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Satuan</label>
                        <input name="satuan" maxlength="20" list="daftarSatuan" autocomplete="off" placeholder="liter / kg / pcs / pak"
                            class="h-11 w-full rounded-xl border-slate-200 text-base focus:border-teal-500 focus:ring-teal-500 md:text-sm">
                        <datalist id="daftarSatuan">
                            @foreach (\App\Models\Bahan::SATUAN as $s)
                                <option value="{{ $s }}">
                            @endforeach
                        </datalist>
                        <p class="err mt-1 text-xs text-red-600" data-for="satuan"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Stok minimum</label>
                            <input name="stok_minimum" type="number" step="0.01" min="0" inputmode="decimal" placeholder="0"
                                class="h-11 w-full rounded-xl border-slate-200 text-base focus:border-teal-500 focus:ring-teal-500 md:text-sm">
                            <p class="err mt-1 text-xs text-red-600" data-for="stok_minimum"></p>
                        </div>
                        <div id="wrapStokAwal">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Stok awal</label>
                            <input name="stok_awal" type="number" step="0.01" min="0" inputmode="decimal" placeholder="0"
                                class="h-11 w-full rounded-xl border-slate-200 text-base focus:border-teal-500 focus:ring-teal-500 md:text-sm">
                            <p class="err mt-1 text-xs text-red-600" data-for="stok_awal"></p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400">Bahan dianggap menipis bila stok ≤ stok minimum. Ubah stok lewat tombol Masuk / Pakai / Stok opname agar tercatat di riwayat.</p>
                </div>

                <div class="flex gap-2 border-t border-slate-100 px-5 py-4">
                    <button type="button" onclick="closeModal('modalBahan')" class="h-11 flex-1 rounded-xl border border-slate-200 font-medium text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="h-11 flex-1 rounded-xl bg-teal-700 font-semibold text-white hover:bg-teal-800">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL RIWAYAT --}}
    <div id="modalRiwayat" class="modal-overlay hidden opacity-0 fixed inset-0 z-50 bg-black/40 p-4 transition-opacity duration-300">
        <div class="modal-content w-full max-w-md scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div class="min-w-0">
                    <h3 class="truncate font-semibold text-slate-800" id="riwayatNama"></h3>
                    <p class="text-xs text-slate-400">30 catatan terakhir</p>
                </div>
                <button type="button" onclick="closeModal('modalRiwayat')" class="h-11 w-11 shrink-0 rounded-xl hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="max-h-[65vh] overflow-y-auto px-5 py-4" id="riwayatList"></div>
        </div>
    </div>
@endsection

@push('addon-script')
    <script>
        const BASE = "{{ url('bahan') }}";
        const JENIS = @json(\App\Models\Bahan::JENIS);
        let bahan = {};
        let aktif = null; // bahan yang sedang dimutasi

        const angka = (v) => Number(v).toLocaleString('id-ID', { maximumFractionDigits: 2 });

        function kartu(b) {
            bahan[b.id] = b;
            // skala tabung: muat stok & 2x minimum, garis putus = batas minimum
            const skala = Math.max(b.stok, b.stok_minimum * 2, 1);
            const isi = Math.min(100, b.stok / skala * 100);
            const batas = b.stok_minimum / skala * 100;
            const low = b.menipis;

            return `
            <div class="flex gap-4 rounded-2xl border-2 bg-white p-4 ${low ? 'border-red-200' : 'border-transparent shadow-sm'}">
                <div class="relative h-32 w-11 shrink-0 overflow-hidden rounded-full border-2 ${low ? 'border-red-200 bg-red-50' : 'border-teal-100 bg-teal-50'}">
                    <div class="absolute inset-x-0 bottom-0 transition-all duration-500 ${low ? 'bg-gradient-to-t from-red-500 to-red-400' : 'bg-gradient-to-t from-teal-600 to-cyan-400'}" style="height:${isi}%"></div>
                    ${b.stok_minimum > 0 ? `<div class="absolute inset-x-0 border-t-2 border-dashed border-slate-500/60" style="bottom:${batas}%"></div>` : ''}
                </div>

                <div class="flex min-w-0 flex-1 flex-col">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-800">${esc(b.nama)}</p>
                            ${low ? '<span class="mt-0.5 inline-flex items-center gap-1 rounded-md bg-red-100 px-1.5 py-0.5 text-[11px] font-semibold text-red-700"><i class="fa-solid fa-triangle-exclamation"></i>Menipis</span>' : ''}
                        </div>
                        <div class="-mr-2 -mt-2 flex shrink-0">
                            <button type="button" onclick="lihatRiwayat(${b.id})" aria-label="Riwayat" class="h-11 w-9 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-teal-700"><i class="fa-solid fa-clock-rotate-left"></i></button>
                            <button type="button" onclick="formBahan(${b.id})" aria-label="Ubah" class="h-11 w-9 rounded-xl text-slate-400 hover:bg-slate-100"><i class="fa-solid fa-pen text-sm"></i></button>
                            <button type="button" onclick="hapusData({url: BASE + '/${b.id}', name: esc(bahan[${b.id}].nama), reload: loadData})" aria-label="Hapus"
                                class="h-11 w-9 rounded-xl text-slate-400 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-trash-can text-sm"></i></button>
                        </div>
                    </div>

                    <p class="mt-auto pt-2"><span class="text-3xl font-bold ${low ? 'text-red-600' : 'text-slate-800'}">${angka(b.stok)}</span>
                        <span class="text-sm text-slate-400">${esc(b.satuan)}</span></p>
                    <p class="text-xs text-slate-400">minimum ${angka(b.stok_minimum)} ${esc(b.satuan)}</p>

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <button type="button" onclick="formMutasi(${b.id}, 'masuk')" class="h-11 rounded-xl bg-teal-50 text-sm font-semibold text-teal-700 hover:bg-teal-100"><i class="fa-solid fa-plus mr-1"></i>Masuk</button>
                        <button type="button" onclick="formMutasi(${b.id}, 'keluar')" class="h-11 rounded-xl bg-slate-100 text-sm font-semibold text-slate-700 hover:bg-slate-200"><i class="fa-solid fa-minus mr-1"></i>Pakai</button>
                    </div>
                </div>
            </div>`;
        }

        function loadData() {
            ajaxRequest({
                url: "{{ route('bahan.data') }}",
                success(res) {
                    bahan = {};
                    $('#jmlBahan').text(res.length);
                    $('#jmlMenipis').text(res.filter((b) => b.menipis).length);
                    $('#rak').html(res.length ? res.map(kartu).join('') : `
                        <div class="col-span-full rounded-2xl border-2 border-dashed border-teal-200 py-14 text-center">
                            <i class="fa-solid fa-jug-detergent text-5xl text-teal-200"></i>
                            <p class="mt-3 font-medium text-slate-600">Rak gudang masih kosong</p>
                            <p class="text-sm text-slate-400">Tambahkan deterjen, pewangi, atau plastik yang biasa dipakai.</p>
                        </div>`);
                },
            });
        }

        // ===== MUTASI =====
        function formMutasi(id, jenis) {
            aktif = bahan[id];
            const f = $('#formMutasi')[0];
            f.reset();
            $(f).find('.err').text('');
            $(f).find(`[name=jenis][value=${jenis}]`).prop('checked', true);
            $('#mutasiNama').text(aktif.nama);
            $('#mutasiStok').text(`${angka(aktif.stok)} ${aktif.satuan}`);
            $('#mutasiSatuan').text(aktif.satuan);
            previewMutasi();
            showModal('modalMutasi');
        }

        function previewMutasi() {
            const jenis = $('#formMutasi [name=jenis]:checked').val();
            const n = parseFloat($('#formMutasi [name=jumlah]').val()) || 0;
            $('#labelJumlah').text(jenis === 'penyesuaian' ? 'Stok hasil hitung fisik' : (jenis === 'masuk' ? 'Jumlah masuk' : 'Jumlah dipakai'));
            const hasil = jenis === 'masuk' ? aktif.stok + n : (jenis === 'keluar' ? aktif.stok - n : n);
            $('#mutasiPreview').html(`Stok menjadi <b class="${hasil < 0 ? 'text-red-600' : 'text-teal-700'}">${angka(hasil)} ${esc(aktif.satuan)}</b>`);
        }

        $('#formMutasi').on('input change', previewMutasi);

        $('#formMutasi').on('submit', function (e) {
            e.preventDefault();
            ajaxRequest({
                url: `${BASE}/${aktif.id}/mutasi`, type: 'POST', data: $(this).serialize(),
                success: (res) => {
                    App.loading.hide();
                    closeModal('modalMutasi');
                    showToast(res.message);
                    loadData();
                },
                error: (xhr) => formError(xhr, this),
            });
        });

        // ===== BAHAN =====
        function formBahan(id = null) {
            const b = id ? bahan[id] : null;
            const f = $('#formBahan')[0];
            f.reset();
            $(f).find('.err').text('');
            $('#bahanTitle').text(b ? 'Ubah Bahan' : 'Tambah Bahan');
            $('#wrapStokAwal').toggleClass('hidden', !!b);
            if (b) {
                f.elements.id.value = b.id;
                f.nama.value = b.nama;
                f.satuan.value = b.satuan;
                f.stok_minimum.value = b.stok_minimum;
            } else {
                f.elements.id.value = '';
            }
            showModal('modalBahan');
        }

        $('#formBahan').on('submit', function (e) {
            e.preventDefault();
            const id = this.elements.id.value;
            ajaxRequest({
                url: id ? `${BASE}/${id}` : BASE, type: 'POST',
                data: $(this).serialize() + (id ? '&_method=PUT' : ''),
                success: (res) => {
                    App.loading.hide();
                    closeModal('modalBahan');
                    showToast(res.message);
                    loadData();
                },
                error: (xhr) => formError(xhr, this),
            });
        });

        // ===== RIWAYAT =====
        function lihatRiwayat(id) {
            const b = bahan[id];
            $('#riwayatNama').text(`Riwayat ${b.nama}`);
            $('#riwayatList').html('<p class="py-8 text-center text-sm text-slate-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Memuat...</p>');
            showModal('modalRiwayat');
            ajaxRequest({
                url: `${BASE}/${id}/riwayat`,
                success(res) {
                    const gaya = {
                        masuk: ['fa-arrow-down', 'bg-teal-100 text-teal-700', '+'],
                        keluar: ['fa-arrow-up', 'bg-slate-100 text-slate-600', '−'],
                        penyesuaian: ['fa-scale-balanced', 'bg-amber-100 text-amber-700', '='],
                    };
                    $('#riwayatList').html(res.length ? `<ol class="relative space-y-4 border-l-2 border-slate-100 pl-5">${res.map((m) => {
                        const [ikon, warna, tanda] = gaya[m.jenis];
                        return `
                        <li class="relative">
                            <span class="absolute -left-[33px] flex h-6 w-6 items-center justify-center rounded-full text-[10px] ${warna}"><i class="fa-solid ${ikon}"></i></span>
                            <div class="flex items-baseline justify-between gap-2">
                                <p class="text-sm font-semibold text-slate-800">${esc(JENIS[m.jenis])} ${tanda}${angka(m.jumlah)} ${esc(b.satuan)}</p>
                                <p class="shrink-0 text-xs text-slate-400">sisa ${angka(m.stok_setelah)}</p>
                            </div>
                            <p class="text-xs text-slate-400">${formatTanggalIndo(m.created_at)} · ${esc(m.user?.name ?? '-')}</p>
                            ${m.keterangan ? `<p class="mt-0.5 text-xs text-slate-500">${esc(m.keterangan)}</p>` : ''}
                        </li>`;
                    }).join('')}</ol>` : '<p class="py-8 text-center text-sm text-slate-400"><i class="fa-solid fa-box-open mr-2"></i>Belum ada mutasi stok.</p>');
                },
            });
        }

        loadData();
    </script>
@endpush
