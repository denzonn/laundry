@extends('layouts.app')

@section('title', 'Pengaturan Toko - ' . config('app.name'))
@section('page_title', 'Pengaturan Toko')
@section('page_description', 'Identitas toko, struk, pesan WhatsApp, dan poin member')

@php
    $input = 'h-11 w-full rounded-xl border-slate-200 text-base md:text-sm focus:border-teal-500 focus:ring-teal-500';
    $placeholders = ['{nama}', '{kode}', '{total}', '{sisa}', '{estimasi}', '{link}'];
@endphp

@section('content')
    <form action="{{ route('pengaturan.update') }}" method="POST" enctype="multipart/form-data"
        class="grid gap-6 lg:grid-cols-5 pb-24 lg:pb-0">
        @csrf
        @method('PUT')

        {{-- KIRI: FORM --}}
        <div class="space-y-6 lg:col-span-3">

            {{-- Identitas --}}
            <section class="rounded-2xl border border-slate-200 bg-white">
                <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-50 text-teal-600"><i class="fa-solid fa-store"></i></span>
                    <h3 class="font-semibold text-slate-800">Identitas Toko</h3>
                </div>

                <div class="space-y-4 p-5">
                    <div class="flex items-center gap-4">
                        <label class="group relative flex h-20 w-20 shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 hover:border-teal-500">
                            <img id="logoPreview" src="{{ $toko->logo ? asset('storage/' . $toko->logo) : '' }}"
                                class="{{ $toko->logo ? '' : 'hidden' }} h-full w-full object-contain" alt="">
                            <i id="logoIcon" class="{{ $toko->logo ? 'hidden' : '' }} fa-regular fa-image text-2xl text-slate-400"></i>
                            <input type="file" name="logo" accept="image/*" class="sr-only" id="logoInput">
                        </label>
                        <div class="text-sm">
                            <p class="font-medium text-slate-700">Logo toko</p>
                            <p class="text-slate-400">PNG/JPG, maks 1 MB. Tap kotak untuk ganti.</p>
                            @error('logo') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Toko</label>
                        <input name="nama_toko" data-preview="nama_toko" value="{{ old('nama_toko', $toko->nama_toko) }}" class="{{ $input }}">
                        @error('nama_toko') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">No HP / WhatsApp</label>
                            <input name="no_hp" type="tel" data-preview="no_hp" value="{{ old('no_hp', $toko->no_hp) }}" class="{{ $input }}">
                            @error('no_hp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Alamat</label>
                            <input name="alamat" data-preview="alamat" value="{{ old('alamat', $toko->alamat) }}" class="{{ $input }}">
                            @error('alamat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- Struk --}}
            <section class="rounded-2xl border border-slate-200 bg-white">
                <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><i class="fa-solid fa-receipt"></i></span>
                    <h3 class="font-semibold text-slate-800">Struk</h3>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Lebar Kertas Printer</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach (['58' => 'Printer kecil / bluetooth', '80' => 'Printer kasir standar'] as $mm => $desc)
                                <label class="cursor-pointer">
                                    <input type="radio" name="ukuran_struk" value="{{ $mm }}" class="peer sr-only"
                                        @checked(old('ukuran_struk', $toko->ukuran_struk) == $mm)>
                                    <div class="rounded-xl border-2 border-slate-200 p-3 peer-checked:border-sky-600 peer-checked:bg-sky-50">
                                        <p class="font-semibold text-slate-800">{{ $mm }} mm</p>
                                        <p class="text-xs text-slate-500">{{ $desc }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Pesan Kaki Struk</label>
                        <input name="footer_struk" data-preview="footer_struk" value="{{ old('footer_struk', $toko->footer_struk) }}" class="{{ $input }}">
                        @error('footer_struk') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- WhatsApp --}}
            <section class="rounded-2xl border border-slate-200 bg-white">
                <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-50 text-green-600"><i class="fa-brands fa-whatsapp text-lg"></i></span>
                    <div>
                        <h3 class="font-semibold text-slate-800">Template WhatsApp</h3>
                        <p class="text-xs text-slate-400">Tap kode di bawah untuk menyisipkan ke template</p>
                    </div>
                </div>

                <div class="space-y-4 p-5">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($placeholders as $ph)
                            <button type="button" data-ph="{{ $ph }}"
                                class="ph-btn h-9 rounded-lg bg-green-50 px-3 font-mono text-xs font-semibold text-green-700 hover:bg-green-100">{{ $ph }}</button>
                        @endforeach
                    </div>

                    @foreach (['template_wa_masuk' => 'Saat order masuk', 'template_wa_selesai' => 'Saat laundry selesai'] as $field => $label)
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
                            <textarea name="{{ $field }}" rows="4" data-wa="{{ $field }}"
                                class="wa-input w-full rounded-xl border-slate-200 text-base md:text-sm focus:border-green-500 focus:ring-green-500">{{ old($field, $toko->$field) }}</textarea>
                            @error($field) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endforeach

                    @php $waAuto = \App\Jobs\KirimWhatsapp::aktif(); @endphp
                    <p class="flex items-start gap-2 rounded-xl bg-slate-50 px-3 py-2.5 text-xs text-slate-500">
                        <i class="fa-solid fa-robot mt-0.5 {{ $waAuto ? 'text-green-600' : 'text-slate-400' }}"></i>
                        <span>Kirim otomatis:
                            <b class="{{ $waAuto ? 'text-green-700' : 'text-slate-700' }}">{{ $waAuto ? 'Aktif' : 'Nonaktif' }}</b>
                            (atur <code>WA_AUTO_SEND</code> &amp; <code>WA_FONNTE_TOKEN</code> di .env)</span>
                    </p>
                </div>
            </section>

            {{-- Member & Poin --}}
            <section class="rounded-2xl border border-slate-200 bg-white">
                <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-500"><i class="fa-solid fa-star"></i></span>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-slate-800">Member & Poin</h3>
                        <p class="text-xs text-slate-400">Pelanggan dapat poin saat cucian diambil, lalu bisa ditukar jadi potongan di order berikutnya</p>
                    </div>
                    <label class="relative inline-flex h-11 shrink-0 cursor-pointer items-center">
                        <input type="hidden" name="poin_aktif" value="0">
                        <input type="checkbox" name="poin_aktif" value="1" id="poinAktif" class="peer sr-only" @checked(old('poin_aktif', $toko->poin_aktif))>
                        <span class="h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-amber-500"></span>
                        <span class="absolute left-1 top-1/2 h-5 w-5 -translate-y-1/2 rounded-full bg-white shadow transition-all peer-checked:left-6"></span>
                    </label>
                </div>

                <div class="space-y-4 p-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Belanja per 1 poin</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">Rp</span>
                                <input name="rupiah_per_poin" type="number" min="1000" inputmode="numeric" value="{{ old('rupiah_per_poin', $toko->rupiah_per_poin) }}" class="poin-input {{ $input }} pl-10">
                            </div>
                            @error('rupiah_per_poin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nilai tukar 1 poin</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">Rp</span>
                                <input name="nilai_poin" type="number" min="1" inputmode="numeric" value="{{ old('nilai_poin', $toko->nilai_poin) }}" class="poin-input {{ $input }} pl-10">
                            </div>
                            @error('nilai_poin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p id="poinContoh" class="flex items-start gap-2 rounded-xl bg-amber-50 px-3 py-2.5 text-xs text-amber-800">
                        <i class="fa-solid fa-lightbulb mt-0.5"></i><span></span>
                    </p>
                </div>
            </section>
        </div>

        {{-- KANAN: PREVIEW --}}
        <aside class="lg:col-span-2">
            <div class="space-y-6 lg:sticky lg:top-24">
                <div class="rounded-2xl bg-slate-800 p-5">
                    <p class="mb-4 text-center text-xs uppercase tracking-widest text-slate-400">Preview Struk</p>
                    <div id="struk" class="mx-auto bg-white px-3 py-4 font-mono text-[11px] leading-relaxed text-slate-800 shadow-xl transition-all duration-300"
                        style="width: {{ $toko->ukuran_struk == '80' ? 300 : 220 }}px; clip-path: polygon(0 0,100% 0,100% calc(100% - 6px),95% 100%,90% calc(100% - 6px),85% 100%,80% calc(100% - 6px),75% 100%,70% calc(100% - 6px),65% 100%,60% calc(100% - 6px),55% 100%,50% calc(100% - 6px),45% 100%,40% calc(100% - 6px),35% 100%,30% calc(100% - 6px),25% 100%,20% calc(100% - 6px),15% 100%,10% calc(100% - 6px),5% 100%,0 calc(100% - 6px));">
                        <div class="text-center">
                            <p class="text-sm font-bold uppercase" data-show="nama_toko"></p>
                            <p data-show="alamat"></p>
                            <p data-show="no_hp"></p>
                        </div>
                        <div class="my-2 border-t border-dashed border-slate-400"></div>
                        <p>LND-{{ now()->format('ymd') }}-0001</p>
                        <p>{{ now()->format('d/m/Y H:i') }}</p>
                        <p>Pelanggan: Budi</p>
                        <div class="my-2 border-t border-dashed border-slate-400"></div>
                        <div class="flex justify-between"><span>Cuci Setrika 3kg</span><span>21.000</span></div>
                        <div class="flex justify-between"><span>Bed Cover 1pcs</span><span>25.000</span></div>
                        <div class="my-2 border-t border-dashed border-slate-400"></div>
                        <div class="flex justify-between font-bold"><span>TOTAL</span><span>46.000</span></div>
                        <div class="my-2 border-t border-dashed border-slate-400"></div>
                        <p class="text-center" data-show="footer_struk"></p>
                    </div>
                </div>

                <div class="rounded-2xl bg-[#e5ddd5] p-4">
                    <p class="mb-3 text-center text-xs font-medium uppercase tracking-widest text-slate-500">Preview WhatsApp</p>
                    <div class="ml-auto max-w-[85%] whitespace-pre-line rounded-xl rounded-tr-none bg-[#dcf8c6] px-3 py-2 text-sm text-slate-800 shadow-sm" id="waPreview"></div>
                </div>
            </div>
        </aside>

        {{-- SIMPAN: sticky di HP --}}
        <div class="fixed inset-x-0 bottom-16 z-20 border-t border-slate-200 bg-white/90 p-3 backdrop-blur lg:static lg:col-span-5 lg:border-0 lg:bg-transparent lg:p-0">
            <button type="submit" class="h-12 w-full rounded-xl bg-teal-600 font-semibold text-white hover:bg-teal-700 lg:ml-auto lg:block lg:w-56">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
@endsection

@push('addon-script')
    <script>
        const contoh = {
            '{nama}': 'Budi', '{kode}': 'LND-{{ now()->format('ymd') }}-0001', '{total}': 'Rp 46.000',
            '{sisa}': 'Rp 0', '{estimasi}': '{{ now()->addDays(2)->translatedFormat('d M Y H:i') }}', '{link}': '{{ url('/lacak') }}/LND-...',
        };
        let lastWa = $('[data-wa]').first()[0];

        function syncStruk() {
            $('[data-preview]').each(function () {
                $(`[data-show=${this.dataset.preview}]`).text(this.value);
            });
        }

        function syncWa(el) {
            lastWa = el;
            let text = el.value;
            for (const [k, v] of Object.entries(contoh)) text = text.replaceAll(k, v);
            $('#waPreview').text(text || '(template kosong)');
        }

        $('[data-preview]').on('input', syncStruk);
        $('.wa-input').on('input focus', function () { syncWa(this); });

        $('[name=ukuran_struk]').on('change', function () {
            $('#struk').css('width', this.value === '80' ? 300 : 220);
        });

        // sisipkan placeholder di posisi kursor textarea terakhir
        $('.ph-btn').on('click', function () {
            const ta = lastWa, ph = this.dataset.ph, pos = ta.selectionStart ?? ta.value.length;
            ta.value = ta.value.slice(0, pos) + ph + ta.value.slice(ta.selectionEnd ?? pos);
            ta.focus();
            ta.setSelectionRange(pos + ph.length, pos + ph.length);
            syncWa(ta);
        });

        $('#logoInput').on('change', function () {
            if (!this.files[0]) return;
            $('#logoPreview').attr('src', URL.createObjectURL(this.files[0])).removeClass('hidden');
            $('#logoIcon').addClass('hidden');
        });

        function syncPoin() {
            const per = +$('[name=rupiah_per_poin]').val() || 0, nilai = +$('[name=nilai_poin]').val() || 0;
            const dapat = per ? Math.floor(100000 / per) : 0;
            $('#poinContoh span').text(($('#poinAktif').is(':checked') ? '' : '(Nonaktif) ') +
                `Contoh: order ${formatRupiah(100000)} → dapat ${dapat} poin. ${dapat} poin bisa ditukar potongan ${formatRupiah(dapat * nilai)} (${per ? (dapat * nilai / 1000).toLocaleString('id-ID') : 0}% kembali).`);
        }
        $('.poin-input, #poinAktif').on('input change', syncPoin);

        syncStruk();
        syncWa(lastWa);
        syncPoin();
    </script>
@endpush
