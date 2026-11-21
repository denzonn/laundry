{{--
    Modal jadwal antar/jemput + aksi status. Dipakai di order/show & pengiriman/index (butuh $kurir).
    JS: bukaPengiriman({ url, method: 'POST'|'PUT', judul, jenis, alamat, jadwal, kurir_id, biaya, catatan }), statusPengiriman(id, status)
--}}
<div id="modalPengiriman" class="modal-overlay hidden opacity-0 fixed inset-0 z-50 bg-black/40 p-4 transition-opacity duration-300">
    <div class="modal-content w-full max-w-md scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
        <form id="formPengiriman">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="font-semibold text-slate-800" id="pgJudul">Jadwalkan Antar / Jemput</h3>
                <button type="button" onclick="closeModal('modalPengiriman')" class="h-11 w-11 rounded-xl hover:bg-slate-100"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="max-h-[70vh] space-y-4 overflow-y-auto px-6 py-5">
                <div id="pgJenis" class="grid grid-cols-2 gap-3">
                    @foreach (['jemput' => ['fa-house-chimney', 'Jemput', 'Ambil cucian di rumah'], 'antar' => ['fa-truck-fast', 'Antar', 'Kirim cucian bersih']] as $val => [$ikon, $label, $desc])
                        <label class="cursor-pointer">
                            <input type="radio" name="jenis" value="{{ $val }}" class="peer sr-only">
                            <div class="rounded-xl border-2 border-slate-200 p-3 peer-checked:border-sky-600 peer-checked:bg-sky-50">
                                <i class="fa-solid {{ $ikon }} text-sky-600"></i>
                                <p class="mt-1 text-sm font-semibold text-slate-800">{{ $label }}</p>
                                <p class="text-xs text-slate-500">{{ $desc }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                <p class="err text-xs text-red-600" data-for="jenis"></p>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Alamat</label>
                    <textarea name="alamat" rows="2" class="w-full rounded-xl border-slate-200 text-base md:text-sm"></textarea>
                    <p class="err mt-1 text-xs text-red-600" data-for="alamat"></p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Jadwal</label>
                        <input name="jadwal" type="datetime-local" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                        <p class="err mt-1 text-xs text-red-600" data-for="jadwal"></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kurir</label>
                        <select name="kurir_id" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                            <option value="">Belum ditentukan</option>
                            @foreach ($kurir as $k)
                                <option value="{{ $k->id }}">{{ $k->name }}</option>
                            @endforeach
                        </select>
                        <p class="err mt-1 text-xs text-red-600" data-for="kurir_id"></p>
                    </div>
                </div>
                @if ($kurir->isEmpty())
                    <p class="rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-700"><i class="fa-solid fa-circle-info mr-1"></i> Belum ada user kurir aktif. Owner bisa menambah di menu User.</p>
                @endif
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Ongkos <span class="font-normal text-slate-400">(info)</span></label>
                        <input name="biaya" type="number" min="0" inputmode="numeric" placeholder="0" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                        <p class="err mt-1 text-xs text-red-600" data-for="biaya"></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan</label>
                        <input name="catatan" placeholder="mis. rumah pagar hijau" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" onclick="closeModal('modalPengiriman')" class="h-11 flex-1 rounded-xl border border-slate-200 text-sm font-medium text-slate-600">Batal</button>
                <button type="submit" class="h-11 flex-1 rounded-xl bg-sky-600 text-sm font-semibold text-white">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('addon-script')
    <script>
        let pgReq = {};

        function bukaPengiriman(d) {
            pgReq = d;
            const f = document.getElementById('formPengiriman');
            f.reset();
            $(f).find('.err').text('');
            $('#pgJudul').text(d.judul || 'Jadwalkan Antar / Jemput');
            $('#pgJenis').toggleClass('hidden', d.method === 'PUT');
            $(f).find(`[name=jenis][value=${d.jenis || 'antar'}]`).prop('checked', true);
            f.alamat.value = d.alamat || '';
            f.jadwal.value = d.jadwal || '';
            f.kurir_id.value = d.kurir_id || '';
            f.biaya.value = d.biaya || '';
            f.catatan.value = d.catatan || '';
            showModal('modalPengiriman');
        }

        $('#formPengiriman').on('submit', function (e) {
            e.preventDefault();
            ajaxRequest({
                url: pgReq.url, type: 'POST', data: $(this).serialize() + (pgReq.method === 'PUT' ? '&_method=PUT' : ''),
                success: () => location.reload(),
                error: (xhr) => formError(xhr, this),
            });
        });

        function statusPengiriman(id, status) {
            const teks = { batal: 'Jadwal ini dibatalkan?', dalam_perjalanan: 'Tandai kurir mulai jalan?', selesai: 'Tandai sudah selesai?' }[status];
            Swal.fire({ icon: 'question', title: teks, showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Tidak' }).then((r) => {
                if (!r.isConfirmed) return;
                App.loading.show();
                ajaxRequest({
                    url: `{{ url('pengiriman') }}/${id}/status`, type: 'POST', data: { _method: 'PATCH', status },
                    success: () => location.reload(),
                    error: (xhr) => { App.loading.hide(); Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.errors?.status?.[0] ?? xhr.responseJSON?.message ?? 'Terjadi kesalahan.' }); },
                });
            });
        }
    </script>
@endpush
