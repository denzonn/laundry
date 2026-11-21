@extends('layouts.app')

@section('title', 'Parfum - ' . config('app.name'))
@section('page_title', 'Pilihan Parfum')
@section('page_description', 'Aroma yang bisa dipilih pelanggan')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-pink-50 via-white to-fuchsia-50 p-6 md:p-10">
            <i class="fa-solid fa-spray-can-sparkles absolute -right-6 -top-6 rotate-12 text-[140px] text-pink-100"></i>

            <div class="relative">
                <h2 class="text-2xl font-bold text-slate-800">Rak Aroma</h2>
                <p class="mt-1 text-sm text-slate-500">Tap aroma untuk mengaktifkan / menonaktifkan. Aroma nonaktif tidak muncul di form order.</p>

                <form id="formParfum" class="mt-6 flex gap-2">
                    <div class="flex-1">
                        <input name="nama" placeholder="Tambah aroma, mis. Vanilla" autocomplete="off"
                            class="h-12 w-full rounded-2xl border-pink-200 bg-white text-base focus:border-pink-500 focus:ring-pink-500 md:text-sm">
                        <p class="err mt-1 text-xs text-red-600" data-for="nama"></p>
                    </div>
                    <button type="submit" class="h-12 shrink-0 rounded-2xl bg-pink-600 px-5 font-semibold text-white hover:bg-pink-700">
                        <i class="fa-solid fa-plus"></i><span class="ml-2 hidden sm:inline">Tambah</span>
                    </button>
                </form>

                <div id="chips" class="mt-8 flex flex-wrap gap-3"></div>
            </div>
        </div>
    </div>
@endsection

@push('addon-script')
    <script>
        let parfum = {};
        const BASE = "{{ url('parfum') }}";

        function chip(p) {
            parfum[p.id] = p;
            return `
            <div class="flex h-12 items-center rounded-full border-2 pl-1 pr-1 transition
                ${p.aktif ? 'border-pink-200 bg-white shadow-sm' : 'border-dashed border-slate-200 bg-slate-50'}">
                <button type="button" onclick="toggleParfum(${p.id})" class="flex h-10 items-center gap-2 rounded-full pl-3 pr-2 text-sm font-medium
                    ${p.aktif ? 'text-pink-700' : 'text-slate-400 line-through'}">
                    <i class="fa-solid fa-droplet ${p.aktif ? 'text-pink-400' : 'text-slate-300'}"></i>${esc(p.nama)}
                </button>
                <button type="button" onclick="renameParfum(${p.id})" class="h-10 w-9 rounded-full text-xs text-slate-400 hover:bg-slate-100"><i class="fa-solid fa-pen"></i></button>
                <button type="button" onclick="hapusData({url: BASE + '/${p.id}', name: esc(parfum[${p.id}].nama), reload: loadData})"
                    class="h-10 w-9 rounded-full text-xs text-slate-400 hover:bg-red-50 hover:text-red-600"><i class="fa-solid fa-xmark"></i></button>
            </div>`;
        }

        function loadData() {
            ajaxRequest({
                url: "{{ route('parfum.data') }}",
                success(res) {
                    $('#chips').html(res.length ? res.map(chip).join('') :
                        '<p class="w-full py-8 text-center text-sm text-slate-400"><i class="fa-solid fa-wind mr-2"></i>Rak masih kosong, tambahkan aroma pertama.</p>');
                },
            });
        }

        $('#formParfum').on('submit', function (e) {
            e.preventDefault();
            ajaxRequest({
                url: "{{ route('parfum.store') }}", type: 'POST', data: $(this).serialize(),
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

        function toggleParfum(id) {
            ajaxRequest({
                url: `${BASE}/${id}/toggle`, type: 'POST', data: { _method: 'PATCH' },
                success: loadData,
            });
        }

        async function renameParfum(id) {
            const { value } = await Swal.fire({
                title: 'Ubah nama aroma', input: 'text', inputValue: parfum[id].nama,
                showCancelButton: true, confirmButtonText: 'Simpan', cancelButtonText: 'Batal', confirmButtonColor: '#db2777',
            });
            if (!value) return;
            ajaxRequest({
                url: `${BASE}/${id}`, type: 'POST', data: { _method: 'PUT', nama: value },
                success(res) { showToast(res.message); loadData(); },
                error: (xhr) => Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message }),
            });
        }

        loadData();
    </script>
@endpush
