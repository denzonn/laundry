@extends('layouts.app')

@section('title', 'Order Baru - ' . config('app.name'))
@section('page_title', 'Order Baru')
@section('page_description', 'Catat cucian masuk')
@section('no_bottom_nav', true)

@php
    $money = 'h-11 w-full rounded-xl border-slate-200 pl-10 text-right text-base font-semibold md:text-sm';
@endphp

@section('content')
    <div id="orderPage" class="grid gap-5 pb-24 md:grid-cols-5 md:pb-0">

        {{-- ================= KIRI ================= --}}
        <div class="space-y-5 md:col-span-3">

            {{-- 1. PELANGGAN --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-4 md:p-5">
                <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-xs text-white">1</span> Pelanggan
                </h3>

                <div id="pelangganTerpilih" class="hidden items-center gap-3 rounded-xl bg-red-50 p-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary font-bold text-white" id="pInisial"></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold text-slate-800" id="pNama"></p>
                        <p class="text-sm text-slate-500" id="pHp"></p>
                        <p class="truncate text-xs text-amber-600" id="pCatatan"></p>
                    </div>
                    <button type="button" onclick="gantiPelanggan()" class="h-10 rounded-lg px-3 text-sm font-medium text-primary hover:bg-red-100">Ganti</button>
                </div>

                <div id="pelangganCari">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="cariPelanggan" type="search" autocomplete="off" placeholder="Ketik nama atau no HP..."
                            class="h-12 w-full rounded-xl border-slate-200 pl-11 text-base focus:border-primary focus:ring-primary md:text-sm">
                        <div id="hasilPelanggan" class="absolute inset-x-0 top-full z-20 mt-1 hidden max-h-72 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl"></div>
                    </div>
                    <p class="err mt-1 text-xs text-red-600" data-for="pelanggan_id"></p>

                    <button type="button" onclick="$('#formPelangganBaru').toggleClass('hidden grid')" class="mt-2 h-10 text-sm font-medium text-primary">
                        <i class="fa-solid fa-user-plus mr-1"></i> Pelanggan baru
                    </button>

                    <form id="formPelangganBaru" class="mt-2 hidden gap-3 rounded-xl bg-slate-50 p-3 sm:grid-cols-5">
                        <div class="sm:col-span-2">
                            <input name="nama" placeholder="Nama" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                            <p class="err mt-1 text-xs text-red-600" data-for="nama"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <input name="no_hp" type="tel" placeholder="No HP" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                            <p class="err mt-1 text-xs text-red-600" data-for="no_hp"></p>
                        </div>
                        <button type="submit" class="h-11 rounded-xl bg-slate-800 text-sm font-semibold text-white">Simpan</button>
                    </form>
                </div>
            </section>

            {{-- 2. LAYANAN --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-4 md:p-5">
                <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-xs text-white">2</span> Pilih Layanan
                </h3>

                @forelse ($layanan->groupBy('kategori') as $kategori => $list)
                    <p class="mb-2 mt-4 text-xs font-semibold uppercase text-slate-400 first-of-type:mt-0">{{ $kategori }}</p>
                    <div class="grid grid-cols-2 gap-2 lg:grid-cols-3">
                        @foreach ($list as $l)
                            <button type="button" onclick="tambahItem({{ $l->id }})" data-tile="{{ $l->id }}"
                                class="relative rounded-xl border-2 border-slate-100 bg-slate-50 p-3 text-left transition active:scale-95 hover:border-primary/40">
                                <p class="text-sm font-semibold leading-tight text-slate-800">{{ $l->nama }}</p>
                                <p class="mt-1 text-xs text-slate-500">Rp {{ number_format($l->harga, 0, ',', '.') }}/{{ $l->satuan }}</p>
                                <span class="tile-qty absolute -right-2 -top-2 hidden h-6 min-w-[24px] items-center justify-center rounded-full bg-primary px-1.5 text-[11px] font-bold text-white"></span>
                            </button>
                        @endforeach
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-400">Belum ada layanan aktif. Owner perlu menambahkan di menu Layanan.</p>
                @endforelse
            </section>

            {{-- 3. KERANJANG --}}
            <section class="rounded-2xl border border-slate-200 bg-white">
                <h3 class="flex items-center gap-2 border-b border-slate-100 p-4 text-sm font-semibold uppercase tracking-wider text-slate-500 md:px-5">
                    <i class="fa-solid fa-basket-shopping text-primary"></i> Cucian
                </h3>
                <div id="keranjang" class="divide-y divide-slate-100"></div>
                <p class="err px-5 pb-3 text-xs text-red-600" data-for="items"></p>
            </section>

            {{-- 4. DETAIL --}}
            <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 md:p-5">
                @if ($parfum->isNotEmpty())
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700"><i class="fa-solid fa-spray-can-sparkles mr-1 text-pink-500"></i> Parfum</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($parfum as $p)
                                <label class="cursor-pointer">
                                    <input type="radio" name="parfum_id" value="{{ $p->id }}" class="peer sr-only">
                                    <span class="flex h-10 items-center rounded-full border border-slate-200 px-4 text-sm text-slate-600 peer-checked:border-pink-500 peer-checked:bg-pink-50 peer-checked:text-pink-700">{{ $p->nama }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Estimasi Selesai</label>
                        <input id="estimasi" type="datetime-local" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                        <p class="mt-1 text-xs text-slate-400" id="estimasiHint">Otomatis dari layanan terlama</p>
                        <p class="err mt-1 text-xs text-red-600" data-for="estimasi_selesai"></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan</label>
                        <input id="catatan" placeholder="mis. ada noda di kemeja putih" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                    </div>
                </div>
            </section>
        </div>

        {{-- ================= KANAN: RINGKASAN ================= --}}
        <aside class="md:col-span-2">
            <div class="space-y-4 rounded-2xl bg-slate-900 p-5 text-white md:sticky md:top-24">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Ringkasan</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-slate-400">Subtotal</span><span id="subtotal" class="font-semibold">Rp. 0</span></div>
                    <div class="grid grid-cols-2 items-center gap-3">
                        <label class="text-slate-400">Diskon</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">Rp</span>
                            <input id="diskon" inputmode="numeric" placeholder="0" class="money {{ $money }} border-slate-700 bg-slate-800 text-white">
                        </div>
                    </div>
                    <p class="err text-right text-xs text-red-400" data-for="diskon"></p>
                    <div class="grid grid-cols-2 items-center gap-3">
                        <label class="text-slate-400">Antar/Jemput</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">Rp</span>
                            <input id="biayaAntar" inputmode="numeric" placeholder="0" class="money {{ $money }} border-slate-700 bg-slate-800 text-white">
                        </div>
                    </div>
                    @if ($toko->poin_aktif)
                        {{-- tampil bila pelanggan punya poin; batas & potongan final dihitung ulang di server --}}
                        <div id="rowPoin" class="hidden rounded-xl bg-amber-400/10 p-3 ring-1 ring-amber-400/30">
                            <div class="flex items-center justify-between gap-3">
                                <label for="poinDitukar" class="text-amber-200">
                                    <i class="fa-solid fa-star mr-1"></i> Tukar poin
                                    <span class="block text-xs text-slate-400">Saldo <b id="poinSaldo">0</b> · maks <b id="poinMaks">0</b></span>
                                </label>
                                <input id="poinDitukar" inputmode="numeric" placeholder="0"
                                    class="h-11 w-24 rounded-xl border-slate-700 bg-slate-800 text-right text-base font-semibold text-white md:text-sm">
                            </div>
                            <div class="mt-1 flex items-center justify-between text-xs">
                                <button type="button" id="poinMaksBtn" class="h-9 font-semibold text-amber-300">Pakai maksimal</button>
                                <span id="potonganPoin" class="font-semibold text-amber-200">- Rp. 0</span>
                            </div>
                        </div>
                        <p class="err text-right text-xs text-red-400" data-for="poin_ditukar"></p>
                    @endif
                </div>

                <div class="flex items-end justify-between border-t border-dashed border-slate-700 pt-4">
                    <span class="text-slate-400">Total</span>
                    <span id="total" class="text-3xl font-bold">Rp. 0</span>
                </div>

                {{-- PEMBAYARAN --}}
                <div class="space-y-3 border-t border-slate-800 pt-4">
                    <div class="grid grid-cols-3 gap-1 rounded-xl bg-slate-800 p-1">
                        @foreach (['nanti' => 'Bayar Nanti', 'dp' => 'DP', 'lunas' => 'Lunas'] as $val => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="modeBayar" value="{{ $val }}" class="peer sr-only" @checked($val === 'nanti')>
                                <span class="flex h-10 items-center justify-center rounded-lg text-sm font-medium text-slate-400 peer-checked:bg-white peer-checked:text-slate-900">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div id="panelBayar" class="hidden space-y-3">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($metode as $m)
                                <label class="cursor-pointer">
                                    <input type="radio" name="metode" value="{{ $m->id }}" class="peer sr-only" @checked($loop->first)>
                                    <span class="flex h-10 items-center rounded-lg border border-slate-700 px-3 text-sm text-slate-300 peer-checked:border-emerald-400 peer-checked:bg-emerald-400/10 peer-checked:text-emerald-300">{{ $m->nama }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="err text-xs text-red-400" data-for="metode_pembayaran_id"></p>

                        <div id="rowDp" class="hidden">
                            <label class="mb-1 block text-xs text-slate-400">Jumlah DP</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">Rp</span>
                                <input id="jumlahDp" inputmode="numeric" class="money {{ $money }} border-slate-700 bg-slate-800 text-white">
                            </div>
                        </div>
                        <div id="rowDiterima" class="hidden">
                            <label class="mb-1 block text-xs text-slate-400">Uang diterima (untuk hitung kembalian)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">Rp</span>
                                <input id="diterima" inputmode="numeric" class="money {{ $money }} border-slate-700 bg-slate-800 text-white">
                            </div>
                            <p class="mt-2 flex justify-between text-sm"><span class="text-slate-400">Kembalian</span><span id="kembalian" class="font-semibold text-emerald-300">Rp. 0</span></p>
                        </div>
                        <p class="err text-xs text-red-400" data-for="bayar"></p>
                    </div>
                </div>

                <button type="button" onclick="simpan()" class="hidden h-12 w-full rounded-xl bg-primary font-semibold text-white hover:bg-primary_hover md:block">
                    <i class="fa-solid fa-check mr-2"></i> Simpan Order
                </button>
            </div>
        </aside>
    </div>

    {{-- BAR BAWAH (HP) --}}
    <div class="fixed inset-x-0 bottom-0 z-30 flex items-center gap-3 border-t border-slate-200 bg-white p-3 md:hidden" style="padding-bottom: max(.75rem, env(safe-area-inset-bottom))">
        <div class="flex-1">
            <p class="text-xs text-slate-400"><span id="jumlahItem">0</span> layanan</p>
            <p class="text-lg font-bold text-slate-800" id="totalBar">Rp. 0</p>
        </div>
        <button type="button" onclick="simpan()" class="h-12 rounded-xl bg-primary px-6 font-semibold text-white">Simpan</button>
    </div>
@endsection

@push('addon-script')
    <script>
        const LAYANAN = Object.fromEntries(@json($layanan).map(l => [l.id, l]));
        const keranjang = new Map(); // layanan_id → qty
        let pelanggan = null, estimasiManual = false;
        const NILAI_POIN = {{ $toko->poin_aktif ? $toko->nilai_poin : 0 }}; // 0 = program poin nonaktif

        const money = (sel) => getNominal($(sel).val());
        const stepOf = (l) => l.kategori === 'kiloan' || l.satuan === 'm²' ? 0.5 : 1;
        const fmtQty = (q) => String(+q.toFixed(2)).replace('.', ',');

        // ---------- PELANGGAN ----------
        function pilihPelanggan(p) {
            pelanggan = p;
            $('#pInisial').text(p.nama[0].toUpperCase());
            $('#pNama').text(p.nama);
            $('#pHp').text(formatHp(p.no_hp));
            $('#pCatatan').text(p.catatan ? '⚠ ' + p.catatan : '');
            $('#pelangganTerpilih').removeClass('hidden').addClass('flex');
            $('#pelangganCari').addClass('hidden');
            $('#hasilPelanggan').addClass('hidden');
            $('.err[data-for=pelanggan_id]').text('');
            $('#poinDitukar').val('');
            render(false);
        }

        function gantiPelanggan() {
            pelanggan = null;
            $('#pelangganTerpilih').addClass('hidden').removeClass('flex');
            $('#pelangganCari').removeClass('hidden');
            $('#cariPelanggan').val('').trigger('focus');
            render(false);
        }

        let hasil = [];
        debounceSearch('#cariPelanggan', function () {
            const q = $('#cariPelanggan').val().trim();
            if (!q) return $('#hasilPelanggan').addClass('hidden');
            ajaxRequest({
                url: "{{ route('pelanggan.data') }}", data: { search: q },
                success(res) {
                    hasil = res.data.slice(0, 8);
                    $('#hasilPelanggan').removeClass('hidden').html(hasil.length ? hasil.map((p, i) => `
                        <button type="button" onclick="pilihPelanggan(hasil[${i}])" class="flex w-full items-center gap-3 border-b border-slate-100 px-4 py-3 text-left hover:bg-slate-50">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-600">${esc(p.nama[0].toUpperCase())}</span>
                            <span><span class="block text-sm font-medium text-slate-800">${esc(p.nama)}</span><span class="text-xs text-slate-400">${formatHp(p.no_hp)}</span></span>
                        </button>`).join('') : `<p class="px-4 py-4 text-sm text-slate-400">Tidak ditemukan. Tambah sebagai pelanggan baru.</p>`);
                },
            });
        }, 300);

        $('#formPelangganBaru').on('submit', function (e) {
            e.preventDefault();
            ajaxRequest({
                url: "{{ route('pelanggan.store') }}", type: 'POST', data: $(this).serialize(),
                success: (res) => {
                    App.loading.hide();
                    this.reset();
                    $(this).addClass('hidden').removeClass('grid').find('.err').text('');
                    showToast(res.message);
                    pilihPelanggan(res.data);
                },
                error: (xhr) => formError(xhr, this),
            });
        });

        // ---------- KERANJANG ----------
        function tambahItem(id) {
            const l = LAYANAN[id];
            keranjang.set(id, keranjang.has(id) ? +(keranjang.get(id) + stepOf(l)).toFixed(2) : 1);
            render();
        }

        function ubahQty(id, delta) {
            const q = +((keranjang.get(id) + delta).toFixed(2));
            q <= 0 ? keranjang.delete(id) : keranjang.set(id, q);
            render();
        }

        function setQty(id, val) {
            const q = parseFloat(String(val).replace(',', '.'));
            if (q > 0) keranjang.set(id, q);
            render(false);
        }

        function render(redrawCart = true) {
            let subtotal = 0, maxJam = 0;

            $('[data-tile]').each(function () {
                const q = keranjang.get(+this.dataset.tile);
                $(this).toggleClass('border-primary bg-red-50', !!q).find('.tile-qty').toggleClass('hidden', !q).toggleClass('flex', !!q).text(q ? fmtQty(q) : '');
            });

            const rows = [...keranjang].map(([id, qty]) => {
                const l = LAYANAN[id], sub = Math.round(l.harga * qty);
                subtotal += sub;
                maxJam = Math.max(maxJam, l.durasi_jam);
                return `
                <div class="flex items-center gap-3 px-4 py-3 md:px-5">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-800">${esc(l.nama)}</p>
                        <p class="text-xs text-slate-400">${formatRupiah(l.harga)} / ${esc(l.satuan)}</p>
                    </div>
                    <div class="flex items-center rounded-xl border border-slate-200">
                        <button type="button" onclick="ubahQty(${id}, -${stepOf(l)})" class="h-10 w-10 text-slate-500"><i class="fa-solid fa-minus text-xs"></i></button>
                        <input value="${fmtQty(qty)}" inputmode="decimal" onchange="setQty(${id}, this.value)"
                            class="h-10 w-14 border-0 p-0 text-center text-base font-semibold focus:ring-0 md:text-sm">
                        <button type="button" onclick="ubahQty(${id}, ${stepOf(l)})" class="h-10 w-10 text-slate-500"><i class="fa-solid fa-plus text-xs"></i></button>
                    </div>
                    <p class="w-24 text-right text-sm font-semibold text-slate-800">${formatRupiah(sub)}</p>
                </div>`;
            });

            if (redrawCart) {
                $('#keranjang').html(rows.length ? rows.join('') : `
                    <div class="flex flex-col items-center py-10 text-center">
                        <i class="fa-solid fa-shirt text-4xl text-slate-200"></i>
                        <p class="mt-2 text-sm text-slate-400">Tap layanan di atas untuk menambah cucian</p>
                    </div>`);
            }

            const setelahDiskon = Math.max(0, subtotal - money('#diskon'));
            const saldoPoin = pelanggan?.poin || 0;
            const maksPoin = NILAI_POIN ? Math.min(saldoPoin, Math.floor(setelahDiskon / NILAI_POIN)) : 0;
            const poin = Math.min(toNumber(String($('#poinDitukar').val() ?? '').replace(/\D/g, '')) || 0, maksPoin);
            if (NILAI_POIN) {
                $('#poinDitukar').val(poin || '');
                $('#rowPoin').toggleClass('hidden', saldoPoin < 1);
                $('#poinSaldo').text(saldoPoin);
                $('#poinMaks').text(maksPoin);
                $('#potonganPoin').text('- ' + formatRupiah(poin * NILAI_POIN));
            }
            const total = setelahDiskon - poin * NILAI_POIN + money('#biayaAntar');
            render.poin = poin;
            $('#subtotal').text(formatRupiah(subtotal));
            $('#total, #totalBar').text(formatRupiah(total));
            $('#jumlahItem').text(keranjang.size);
            $('#kembalian').text(formatRupiah(Math.max(0, money('#diterima') - total)));

            if (!estimasiManual && maxJam) {
                const d = new Date(Date.now() + maxJam * 3600e3), pad = (n) => String(n).padStart(2, '0');
                $('#estimasi').val(`${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`);
            }
            render.total = total;
        }

        // ---------- INPUT ----------
        $('.money').on('input', function () {
            this.value = this.value ? formatRupiah(getNominal(this.value), false) : '';
            render(false);
        });

        $('#poinDitukar').on('input', () => render(false));
        $('#poinMaksBtn').on('click', () => { $('#poinDitukar').val(999999); render(false); });

        $('#estimasi').on('input', function () {
            estimasiManual = true;
            $('#estimasiHint').text('Diatur manual');
        });

        $('[name=modeBayar]').on('change', function () {
            $('#panelBayar').toggleClass('hidden', this.value === 'nanti');
            $('#rowDp').toggleClass('hidden', this.value !== 'dp');
            $('#rowDiterima').toggleClass('hidden', this.value !== 'lunas');
        });

        $(document).on('click', (e) => {
            if (!$(e.target).closest('#pelangganCari').length) $('#hasilPelanggan').addClass('hidden');
        });

        // ---------- SIMPAN ----------
        function simpan() {
            const mode = $('[name=modeBayar]:checked').val();
            const data = {
                pelanggan_id: pelanggan?.id,
                parfum_id: $('[name=parfum_id]:checked').val() ?? '',
                items: [...keranjang].map(([layanan_id, qty]) => ({ layanan_id, qty })),
                diskon: money('#diskon'),
                poin_ditukar: render.poin || 0,
                biaya_antar: money('#biayaAntar'),
                catatan: $('#catatan').val(),
                bayar: mode === 'lunas' ? render.total : (mode === 'dp' ? money('#jumlahDp') : 0),
                metode_pembayaran_id: mode === 'nanti' ? '' : $('[name=metode]:checked').val(),
            };
            if (estimasiManual) data.estimasi_selesai = $('#estimasi').val();

            if (!data.pelanggan_id) {
                $('.err[data-for=pelanggan_id]').text('Pilih atau tambah pelanggan dulu.');
                return $('#cariPelanggan')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            App.loading.show('Menyimpan order...');
            ajaxRequest({
                url: "{{ route('order.store') }}", type: 'POST', data,
                success: (res) => window.location = res.redirect,
                error(xhr) {
                    formError(xhr, '#orderPage');
                    if (xhr.status === 422) showToast(Object.values(xhr.responseJSON.errors)[0][0], 'error');
                },
            });
        }

        render();
    </script>
@endpush
