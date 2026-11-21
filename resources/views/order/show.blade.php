@extends('layouts.app')

@section('title', $order->kode . ' - ' . config('app.name'))
@section('page_title', 'Detail Order')
@section('page_description', $order->kode)

@use('App\Models\Order')
@use('App\Models\Pengiriman')

@php
    $rp = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $ikon = ['antrian' => 'fa-hourglass-start', 'dicuci' => 'fa-soap', 'disetrika' => 'fa-shirt', 'siap_diambil' => 'fa-box-open', 'diambil' => 'fa-circle-check'];
    $posisi = array_search($order->status, Order::ALUR, true);
    $batal = $order->status === 'batal';
    $berikut = $order->statusBerikutnya();
    $waUtama = in_array($order->status, ['siap_diambil', 'diambil'], true) ? 'selesai' : 'masuk';
    $waLain = $waUtama === 'masuk' ? 'selesai' : 'masuk';
    $waLabel = ['masuk' => 'Nota order masuk', 'selesai' => 'Laundry selesai'];

    // timeline gabungan status + pembayaran, terbaru di atas
    $timeline = $order->logs->map(fn($l) => ['waktu' => $l->created_at, 'jenis' => 'status', 'item' => $l])
        ->concat($order->pembayaran->map(fn($p) => ['waktu' => $p->created_at, 'jenis' => 'bayar', 'item' => $p]))
        ->sortByDesc('waktu');
@endphp

@section('content')
    <a href="{{ route('order') }}" class="mb-4 inline-flex h-10 items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800">
        <i class="fa-solid fa-arrow-left"></i> Daftar Order
    </a>

    @if (request('baru'))
        <div class="mb-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 p-5 text-white shadow-lg shadow-emerald-500/20">
            <p class="font-semibold"><i class="fa-solid fa-circle-check mr-2"></i>Order tersimpan. Berikan nota ke pelanggan:</p>
            <div class="mt-4 grid grid-cols-2 gap-3">
                <a href="{{ route('order.struk', [$order, 'print' => 1]) }}"
                    class="flex h-12 items-center justify-center gap-2 rounded-xl bg-white text-sm font-semibold text-emerald-700"><i class="fa-solid fa-print"></i> Cetak Struk</a>
                <a href="{{ $order->waUrl('masuk') }}" target="_blank"
                    class="flex h-12 items-center justify-center gap-2 rounded-xl bg-emerald-900/30 text-sm font-semibold text-white ring-1 ring-white/50"><i class="fa-brands fa-whatsapp text-lg"></i> Kirim WA</a>
            </div>
        </div>
    @endif

    {{-- HEADER + STEPPER --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div>
                <p class="font-mono text-sm font-semibold text-slate-400">{{ $order->kode }}</p>
                <h2 class="text-2xl font-bold text-slate-800">{{ $order->pelanggan->nama }}</h2>
                <p class="text-sm text-slate-500">
                    {{ preg_replace('/^62/', '0', $order->pelanggan->no_hp) }}
                    <a href="https://wa.me/{{ $order->pelanggan->no_hp }}" target="_blank" class="ml-2 text-green-600"><i class="fa-brands fa-whatsapp"></i> Chat</a>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="rounded-xl px-3 py-1.5 text-sm font-semibold {{ Order::WARNA[$order->status] }}">{{ Order::LABEL[$order->status] }}</span>
                <span class="rounded-xl px-3 py-1.5 text-sm font-semibold {{ Order::WARNA[$order->status_bayar] }}">
                    {{ ['belum' => 'Belum Bayar', 'dp' => 'DP', 'lunas' => 'Lunas'][$order->status_bayar] }}
                </span>
            </div>
        </div>

        {{-- NOTA: struk & WA --}}
        <div class="mt-4 grid grid-cols-2 gap-2 md:flex md:flex-wrap md:items-center">
            <a href="{{ route('order.struk', $order) }}"
                class="flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <i class="fa-solid fa-print"></i> Cetak Struk
            </a>
            <a href="{{ $order->waUrl($waUtama) }}" target="_blank" title="{{ $waLabel[$waUtama] }}"
                class="flex h-11 items-center justify-center gap-2 rounded-xl bg-green-600 px-4 text-sm font-semibold text-white hover:bg-green-700">
                <i class="fa-brands fa-whatsapp text-lg"></i> Kirim WA
            </a>
            <a href="{{ $order->waUrl($waLain) }}" target="_blank"
                class="col-span-2 flex h-11 items-center justify-center text-xs font-medium text-green-700 hover:underline md:col-span-1 md:px-2">
                atau kirim: {{ $waLabel[$waLain] }}
            </a>
        </div>

        @if ($batal)
            <div class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">
                <i class="fa-solid fa-ban mr-2"></i><b>Order dibatalkan.</b> Alasan: {{ $order->alasan_batal }}
            </div>
        @else
            <ol class="mt-6 grid grid-cols-5">
                @foreach (Order::ALUR as $i => $s)
                    @php $done = $i <= $posisi; @endphp
                    <li class="relative flex flex-col items-center text-center">
                        @if ($i > 0)
                            <span class="absolute right-1/2 top-5 h-1 w-full -translate-y-1/2 {{ $done ? 'bg-primary' : 'bg-slate-200' }}"></span>
                        @endif
                        <span class="relative flex h-10 w-10 items-center justify-center rounded-full border-4 border-white
                            {{ $i === $posisi ? 'bg-primary text-white ring-4 ring-primary/20' : ($done ? 'bg-primary text-white' : 'bg-slate-200 text-slate-400') }}">
                            <i class="fa-solid {{ $ikon[$s] }} text-sm"></i>
                        </span>
                        <span class="mt-2 text-[11px] font-medium leading-tight md:text-xs {{ $done ? 'text-slate-800' : 'text-slate-400' }}">{{ Order::LABEL[$s] }}</span>
                    </li>
                @endforeach
            </ol>
        @endif

        {{-- AKSI --}}
        @unless ($batal)
            <div class="mt-6 flex flex-col gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:flex-wrap">
                @foreach ($berikut as $s)
                    @php $kunci = $s === 'diambil' && $order->status_bayar !== 'lunas'; @endphp
                    <button type="button" onclick="ubahStatus('{{ $s }}')" @disabled($kunci)
                        class="flex h-12 items-center justify-center gap-2 rounded-xl px-5 text-sm font-semibold
                            {{ $loop->first ? 'bg-slate-800 text-white hover:bg-slate-900' : 'border border-slate-200 text-slate-700 hover:bg-slate-50' }}
                            disabled:cursor-not-allowed disabled:opacity-40"
                        title="{{ $kunci ? 'Lunasi dulu sebelum diambil' : '' }}">
                        <i class="fa-solid {{ $ikon[$s] }}"></i> Tandai {{ Order::LABEL[$s] }}
                    </button>
                @endforeach

                @if ($order->sisa > 0)
                    <button type="button" onclick="showModal('modalBayar')"
                        class="flex h-12 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white hover:bg-emerald-700">
                        <i class="fa-solid fa-money-bill-wave"></i> Terima Pembayaran
                    </button>
                @endif

                @if (auth()->user()->isOwner() && $order->status !== 'diambil')
                    <button type="button" onclick="showModal('modalBatal')"
                        class="flex h-12 items-center justify-center gap-2 rounded-xl px-5 text-sm font-medium text-red-600 hover:bg-red-50 sm:ml-auto">
                        <i class="fa-solid fa-ban"></i> Batalkan
                    </button>
                @endif
            </div>
        @endunless
    </div>

    <div class="mt-6 grid gap-6 md:grid-cols-5">
        {{-- NOTA --}}
        <section class="rounded-2xl border border-slate-200 bg-white md:col-span-3">
            <h3 class="border-b border-slate-100 px-5 py-4 font-semibold text-slate-800"><i class="fa-solid fa-file-invoice mr-2 text-primary"></i>Rincian</h3>

            <div class="divide-y divide-slate-100">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between gap-3 px-5 py-3">
                        <div>
                            <p class="font-medium text-slate-800">{{ $item->nama_layanan }}</p>
                            <p class="text-xs text-slate-400">{{ rtrim(rtrim(number_format($item->qty, 2, ',', '.'), '0'), ',') }} {{ $item->satuan }} × {{ $rp($item->harga) }}</p>
                        </div>
                        <p class="font-semibold text-slate-800">{{ $rp($item->subtotal) }}</p>
                    </div>
                @endforeach
            </div>

            <dl class="space-y-2 border-t border-dashed border-slate-200 bg-slate-50/60 px-5 py-4 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd>{{ $rp($order->subtotal) }}</dd></div>
                @if ($order->diskon)
                    <div class="flex justify-between"><dt class="text-slate-500">Diskon</dt><dd class="text-red-600">- {{ $rp($order->diskon) }}</dd></div>
                @endif
                @if ($order->potongan_poin)
                    <div class="flex justify-between"><dt class="text-slate-500"><i class="fa-solid fa-star mr-1 text-amber-500"></i>Tukar {{ $order->poin_ditukar }} poin</dt><dd class="text-red-600">- {{ $rp($order->potongan_poin) }}</dd></div>
                @endif
                @if ($order->biaya_antar)
                    <div class="flex justify-between"><dt class="text-slate-500">Antar/Jemput</dt><dd>{{ $rp($order->biaya_antar) }}</dd></div>
                @endif
                <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-bold"><dt>Total</dt><dd>{{ $rp($order->total) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Dibayar</dt><dd class="text-emerald-600">{{ $rp($order->dibayar) }}</dd></div>
                <div class="flex justify-between text-base font-bold {{ $order->sisa ? 'text-red-600' : 'text-emerald-600' }}">
                    <dt>Sisa</dt><dd>{{ $order->sisa ? $rp($order->sisa) : 'LUNAS' }}</dd>
                </div>
            </dl>

            <dl class="grid grid-cols-2 gap-4 border-t border-slate-100 px-5 py-4 text-sm">
                <div><dt class="text-xs text-slate-400">Masuk</dt><dd class="font-medium">{{ $order->tanggal_masuk->translatedFormat('d M Y H:i') }}</dd></div>
                <div>
                    <dt class="text-xs text-slate-400">Estimasi selesai</dt>
                    <dd class="font-medium {{ !in_array($order->status, ['siap_diambil', 'diambil', 'batal']) && $order->estimasi_selesai->isPast() ? 'text-red-600' : '' }}">
                        {{ $order->estimasi_selesai->translatedFormat('d M Y H:i') }}
                    </dd>
                </div>
                <div><dt class="text-xs text-slate-400">Parfum</dt><dd class="font-medium">{{ $order->parfum->nama ?? '-' }}</dd></div>
                <div><dt class="text-xs text-slate-400">Kasir</dt><dd class="font-medium">{{ $order->user->name }}</dd></div>
                @if ($order->catatan)
                    <div class="col-span-2 rounded-xl bg-amber-50 p-3 text-amber-800"><i class="fa-solid fa-note-sticky mr-1"></i> {{ $order->catatan }}</div>
                @endif
            </dl>
        </section>

        {{-- TIMELINE --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 md:col-span-2">
            <h3 class="font-semibold text-slate-800"><i class="fa-solid fa-clock-rotate-left mr-2 text-slate-400"></i>Riwayat</h3>
            <ol class="relative mt-4 space-y-5 border-l-2 border-slate-100 pl-6">
                @foreach ($timeline as $t)
                    <li class="relative">
                        @if ($t['jenis'] === 'bayar')
                            <span class="absolute -left-[35px] flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600"><i class="fa-solid fa-money-bill text-[11px]"></i></span>
                            <p class="text-sm font-semibold text-emerald-700">+ {{ $rp($t['item']->jumlah) }} <span class="font-normal text-slate-500">via {{ $t['item']->metode->nama }}</span></p>
                        @else
                            <span class="absolute -left-[35px] flex h-7 w-7 items-center justify-center rounded-full {{ Order::WARNA[$t['item']->status] ?? 'bg-slate-100' }}">
                                <i class="fa-solid {{ $ikon[$t['item']->status] ?? 'fa-ban' }} text-[11px]"></i>
                            </span>
                            <p class="text-sm font-semibold text-slate-800">{{ Order::LABEL[$t['item']->status] ?? $t['item']->status }}</p>
                        @endif
                        @if ($t['item']->catatan)
                            <p class="text-sm text-slate-500">{{ $t['item']->catatan }}</p>
                        @endif
                        <p class="text-xs text-slate-400">{{ $t['waktu']->translatedFormat('d M H:i') }} · {{ $t['item']->user->name }}</p>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>

    {{-- ANTAR / JEMPUT --}}
    @php
        $jadwalDefault = ($order->estimasi_selesai->isFuture() ? $order->estimasi_selesai : now()->addHour())->format('Y-m-d\TH:i');
        $ikonJenis = ['jemput' => 'fa-house-chimney', 'antar' => 'fa-truck-fast'];
    @endphp
    <section class="mt-6 rounded-2xl border border-sky-100 bg-gradient-to-br from-sky-50 to-white p-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="font-semibold text-slate-800"><i class="fa-solid fa-motorcycle mr-2 text-sky-600"></i>Antar / Jemput</h3>
                <p class="text-xs text-slate-500">Ongkos yang ditagih tetap dari "Antar/Jemput" di rincian order</p>
            </div>
            @unless ($batal)
                <button type="button"
                    onclick="bukaPengiriman({{ json_encode(['url' => route('pengiriman.store', $order), 'method' => 'POST', 'jenis' => 'antar', 'alamat' => $order->pelanggan->alamat, 'jadwal' => $jadwalDefault, 'biaya' => $order->biaya_antar ?: null]) }})"
                    class="flex h-11 shrink-0 items-center gap-2 rounded-xl bg-sky-600 px-4 text-sm font-semibold text-white hover:bg-sky-700">
                    <i class="fa-solid fa-plus"></i> Jadwalkan
                </button>
            @endunless
        </div>

        <div class="mt-4 space-y-3">
            @forelse ($order->pengiriman as $p)
                @php $aktif = isset(Pengiriman::ALUR[$p->status]); @endphp
                <div class="flex flex-col gap-3 rounded-xl bg-white p-4 ring-1 ring-slate-200 sm:flex-row sm:items-center {{ $aktif ? '' : 'opacity-70' }}">
                    <div class="flex min-w-0 flex-1 items-start gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-600"><i class="fa-solid {{ $ikonJenis[$p->jenis] }}"></i></span>
                        <div class="min-w-0">
                            <p class="flex flex-wrap items-center gap-2 font-semibold text-slate-800">
                                {{ ucfirst($p->jenis) }} · {{ $p->jadwal->translatedFormat('D, d M H:i') }}
                                <span class="rounded-lg px-2 py-0.5 text-[11px] font-semibold {{ Pengiriman::WARNA[$p->status] }}">{{ Pengiriman::LABEL[$p->status] }}</span>
                            </p>
                            <p class="text-sm text-slate-500">{{ $p->alamat }}</p>
                            <p class="text-xs text-slate-400">
                                <i class="fa-solid fa-user mr-1"></i>{{ $p->kurir->name ?? 'Kurir belum ditentukan' }}
                                @if ($p->biaya) · Ongkos {{ $rp($p->biaya) }} @endif
                                @if ($p->selesai_at) · Selesai {{ $p->selesai_at->translatedFormat('d M H:i') }} @endif
                            </p>
                            @if ($p->catatan)
                                <p class="text-xs text-amber-700"><i class="fa-solid fa-note-sticky mr-1"></i>{{ $p->catatan }}</p>
                            @endif
                        </div>
                    </div>
                    @if ($aktif)
                        <div class="grid grid-cols-2 gap-2 sm:flex">
                            <button type="button"
                                onclick="bukaPengiriman({{ json_encode(['url' => route('pengiriman.update', $p), 'method' => 'PUT', 'judul' => 'Ubah Jadwal '.ucfirst($p->jenis), 'alamat' => $p->alamat, 'jadwal' => $p->jadwal->format('Y-m-d\TH:i'), 'kurir_id' => $p->kurir_id, 'biaya' => $p->biaya ?: null, 'catatan' => $p->catatan]) }})"
                                class="h-11 rounded-xl border border-slate-200 px-4 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                <i class="fa-solid fa-pen mr-1"></i> Ubah
                            </button>
                            <button type="button" onclick="statusPengiriman({{ $p->id }}, 'batal')" class="h-11 rounded-xl px-4 text-sm font-medium text-red-600 hover:bg-red-50">
                                <i class="fa-solid fa-ban mr-1"></i> Batal
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="flex items-center gap-3 rounded-xl border-2 border-dashed border-sky-200 p-4 text-sm text-slate-500">
                    <i class="fa-solid fa-map-location-dot text-2xl text-sky-300"></i>
                    Belum ada jadwal. Jadwalkan bila pelanggan minta cucian dijemput atau diantar.
                </div>
            @endforelse
        </div>
    </section>

    @include('pengiriman._form')

    {{-- MODAL BAYAR --}}
    <div id="modalBayar" class="modal-overlay hidden opacity-0 fixed inset-0 z-50 bg-black/40 p-4 transition-opacity duration-300">
        <div class="modal-content w-full max-w-md scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <form id="formBayar">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="font-semibold text-slate-800">Terima Pembayaran</h3>
                    <p class="text-sm text-slate-500">Sisa tagihan <b class="text-red-600">{{ $rp($order->sisa) }}</b></p>
                </div>
                <div class="space-y-4 px-6 py-5">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($metode as $m)
                            <label class="cursor-pointer">
                                <input type="radio" name="metode_pembayaran_id" value="{{ $m->id }}" class="peer sr-only" @checked($loop->first)>
                                <span class="flex h-10 items-center rounded-lg border border-slate-200 px-3 text-sm text-slate-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700">{{ $m->nama }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="err text-xs text-red-600" data-for="metode_pembayaran_id"></p>
                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <label class="text-sm font-medium text-slate-700">Jumlah</label>
                            <button type="button" onclick="$('#jumlah').val(formatRupiah({{ $order->sisa }}, false))" class="text-xs font-semibold text-emerald-600">Bayar pas</button>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">Rp</span>
                            <input id="jumlah" inputmode="numeric" value="{{ number_format($order->sisa, 0, ',', '.') }}"
                                class="h-12 w-full rounded-xl border-slate-200 pl-10 text-lg font-bold">
                        </div>
                        <p class="err mt-1 text-xs text-red-600" data-for="jumlah"></p>
                    </div>
                    <input name="catatan" placeholder="Catatan (opsional)" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                </div>
                <div class="flex gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" onclick="closeModal('modalBayar')" class="h-11 flex-1 rounded-xl border border-slate-200 text-sm font-medium text-slate-600">Batal</button>
                    <button type="submit" class="h-11 flex-1 rounded-xl bg-emerald-600 text-sm font-semibold text-white">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL BATAL (owner saja) --}}
    @if (auth()->user()->isOwner())
    <div id="modalBatal" class="modal-overlay hidden opacity-0 fixed inset-0 z-50 bg-black/40 p-4 transition-opacity duration-300">
        <div class="modal-content w-full max-w-md scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <form id="formBatal">
                <div class="px-6 py-5">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600"><i class="fa-solid fa-ban text-lg"></i></span>
                    <h3 class="mt-3 font-semibold text-slate-800">Batalkan order {{ $order->kode }}?</h3>
                    @if ($order->dibayar)
                        <p class="mt-1 text-sm text-amber-700">Sudah dibayar {{ $rp($order->dibayar) }}. Kembalikan uang ke pelanggan secara manual & catat di alasan.</p>
                    @endif
                    <textarea name="catatan" rows="3" placeholder="Alasan pembatalan (wajib)" class="mt-4 w-full rounded-xl border-slate-200 text-base md:text-sm"></textarea>
                    <p class="err mt-1 text-xs text-red-600" data-for="catatan"></p>
                    <p class="err mt-1 text-xs text-red-600" data-for="status"></p>
                </div>
                <div class="flex gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" onclick="closeModal('modalBatal')" class="h-11 flex-1 rounded-xl border border-slate-200 text-sm font-medium text-slate-600">Kembali</button>
                    <button type="submit" class="h-11 flex-1 rounded-xl bg-red-600 text-sm font-semibold text-white">Ya, Batalkan</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endsection

@push('addon-script')
    <script>
        const URL_ORDER = "{{ url('order/' . $order->id) }}";
        const errPertama = (xhr) => Object.values(xhr.responseJSON?.errors ?? {})[0]?.[0] ?? xhr.responseJSON?.message ?? 'Terjadi kesalahan.';

        function ubahStatus(status) {
            App.loading.show();
            ajaxRequest({
                url: `${URL_ORDER}/status`, type: 'POST', data: { _method: 'PATCH', status },
                success: () => location.reload(),
                error: (xhr) => { App.loading.hide(); Swal.fire({ icon: 'error', title: 'Gagal', text: errPertama(xhr) }); },
            });
        }

        $('#jumlah').on('input', function () { this.value = this.value ? formatRupiah(getNominal(this.value), false) : ''; });

        $('#formBayar').on('submit', function (e) {
            e.preventDefault();
            ajaxRequest({
                url: `${URL_ORDER}/bayar`, type: 'POST',
                data: { jumlah: getNominal($('#jumlah').val()), metode_pembayaran_id: $(this).find('[name=metode_pembayaran_id]:checked').val(), catatan: this.catatan.value },
                success: () => location.reload(),
                error: (xhr) => formError(xhr, this),
            });
        });

        $('#formBatal').on('submit', function (e) {
            e.preventDefault();
            ajaxRequest({
                url: `${URL_ORDER}/status`, type: 'POST', data: { _method: 'PATCH', status: 'batal', catatan: this.catatan.value },
                success: () => location.reload(),
                error: (xhr) => formError(xhr, this),
            });
        });
    </script>
@endpush
