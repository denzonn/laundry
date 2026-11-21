@extends('layouts.app')

@section('title', 'Metode Bayar - ' . config('app.name'))
@section('page_title', 'Metode Pembayaran')
@section('page_description', 'Cara pelanggan membayar')

@section('content')
    <div id="wallet" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"></div>
@endsection

@push('addon-script')
    <script>
        let metode = {};
        const BASE = "{{ url('metode-pembayaran') }}";

        // tampilan kartu ditebak dari nama
        function gaya(nama) {
            const n = nama.toLowerCase();
            if (/tunai|cash/.test(n)) return ['fa-money-bills', 'from-emerald-500 to-green-700'];
            if (/qris|qr/.test(n)) return ['fa-qrcode', 'from-fuchsia-500 to-purple-700'];
            if (/transfer|bank|bca|bri|bni|mandiri/.test(n)) return ['fa-building-columns', 'from-sky-500 to-blue-700'];
            if (/ovo|dana|gopay|shopee|wallet/.test(n)) return ['fa-mobile-screen-button', 'from-orange-400 to-rose-600'];
            return ['fa-credit-card', 'from-slate-500 to-slate-700'];
        }

        function kartu(m) {
            metode[m.id] = m;
            const [icon, grad] = gaya(m.nama);
            return `
            <div class="relative h-44 overflow-hidden rounded-3xl bg-gradient-to-br ${grad} p-5 text-white shadow-lg transition ${m.aktif ? '' : 'grayscale opacity-50'}">
                <i class="fa-solid ${icon} absolute -bottom-4 -right-2 text-8xl text-white/15"></i>
                <div class="flex items-start justify-between">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/20"><i class="fa-solid ${icon} text-lg"></i></span>
                    <button type="button" onclick="toggleMetode(${m.id})" class="relative h-7 w-12 rounded-full ${m.aktif ? 'bg-white/90' : 'bg-white/30'}" title="${m.aktif ? 'Nonaktifkan' : 'Aktifkan'}">
                        <span class="absolute top-1 h-5 w-5 rounded-full shadow transition-all ${m.aktif ? 'left-6 bg-emerald-500' : 'left-1 bg-white'}"></span>
                    </button>
                </div>
                <div class="absolute inset-x-5 bottom-5 flex items-end justify-between">
                    <div>
                        <p class="text-[11px] uppercase tracking-widest text-white/70">${m.aktif ? 'Aktif' : 'Nonaktif'}</p>
                        <p class="text-xl font-bold">${esc(m.nama)}</p>
                    </div>
                    <div class="flex gap-1">
                        <button type="button" onclick="namaMetode(${m.id})" class="h-10 w-10 rounded-xl bg-white/15 hover:bg-white/25"><i class="fa-solid fa-pen text-sm"></i></button>
                        <button type="button" onclick="hapusData({url: BASE + '/${m.id}', name: esc(metode[${m.id}].nama), reload: loadData})"
                            class="h-10 w-10 rounded-xl bg-white/15 hover:bg-white/25"><i class="fa-regular fa-trash-can text-sm"></i></button>
                    </div>
                </div>
            </div>`;
        }

        const kartuTambah = `
            <button type="button" onclick="namaMetode()" class="flex h-44 flex-col items-center justify-center gap-2 rounded-3xl border-2 border-dashed border-slate-300 text-slate-400 hover:border-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-plus text-2xl"></i>
                <span class="text-sm font-medium">Tambah metode</span>
            </button>`;

        function loadData() {
            ajaxRequest({
                url: "{{ route('metode-pembayaran.data') }}",
                before: () => $('#wallet').html('<div class="h-44 animate-pulse rounded-3xl bg-slate-200"></div>'.repeat(3)),
                success: (res) => $('#wallet').html(res.map(kartu).join('') + kartuTambah),
            });
        }

        async function namaMetode(id = null) {
            const { value } = await Swal.fire({
                title: id ? 'Ubah nama metode' : 'Metode pembayaran baru', input: 'text', inputValue: id ? metode[id].nama : '',
                inputPlaceholder: 'mis. Transfer BCA, OVO', showCancelButton: true, confirmButtonText: 'Simpan', cancelButtonText: 'Batal',
            });
            if (!value) return;
            ajaxRequest({
                url: id ? `${BASE}/${id}` : BASE, type: 'POST', data: id ? { _method: 'PUT', nama: value } : { nama: value },
                success(res) { showToast(res.message); loadData(); },
                error: (xhr) => Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message }),
            });
        }

        function toggleMetode(id) {
            ajaxRequest({
                url: `${BASE}/${id}/toggle`, type: 'POST', data: { _method: 'PATCH' },
                success: loadData,
            });
        }

        loadData();
    </script>
@endpush
