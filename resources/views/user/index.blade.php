@extends('layouts.app')

@section('title', 'User - ' . config('app.name'))
@section('page_title', 'Tim Laundry')
@section('page_description', 'Kelola akun owner, kasir & kurir')

@section('content')
    {{-- HERO --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 p-5 md:p-6 text-white">
        <i class="fa-solid fa-people-group absolute -right-4 -bottom-6 text-[120px] text-white/10"></i>

        <div class="relative flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm text-violet-100">Anggota tim</p>
                <h2 class="text-3xl font-bold" id="statTotal">-</h2>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 md:flex md:gap-3">
                <div class="rounded-xl bg-white/15 px-4 py-2 backdrop-blur">
                    <p class="text-[11px] uppercase tracking-wider text-violet-100">Owner</p>
                    <p class="text-lg font-semibold" id="statOwner">-</p>
                </div>
                <div class="rounded-xl bg-white/15 px-4 py-2 backdrop-blur">
                    <p class="text-[11px] uppercase tracking-wider text-violet-100">Kasir</p>
                    <p class="text-lg font-semibold" id="statKasir">-</p>
                </div>
                <div class="rounded-xl bg-white/15 px-4 py-2 backdrop-blur">
                    <p class="text-[11px] uppercase tracking-wider text-violet-100">Kurir</p>
                    <p class="text-lg font-semibold" id="statKurir">-</p>
                </div>
                <div class="rounded-xl bg-white/15 px-4 py-2 backdrop-blur">
                    <p class="text-[11px] uppercase tracking-wider text-violet-100">Nonaktif</p>
                    <p class="text-lg font-semibold" id="statNonaktif">-</p>
                </div>
            </div>
        </div>
    </div>

    {{-- TOOLBAR --}}
    <div class="mt-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex gap-1 rounded-xl bg-slate-100 p-1" id="roleTabs">
            <button type="button" data-role="" class="role-tab h-10 flex-1 md:flex-none rounded-lg px-4 text-sm font-medium bg-white text-violet-700 shadow-sm">Semua</button>
            <button type="button" data-role="owner" class="role-tab h-10 flex-1 md:flex-none rounded-lg px-4 text-sm font-medium text-slate-500">Owner</button>
            <button type="button" data-role="kasir" class="role-tab h-10 flex-1 md:flex-none rounded-lg px-4 text-sm font-medium text-slate-500">Kasir</button>
            <button type="button" data-role="kurir" class="role-tab h-10 flex-1 md:flex-none rounded-lg px-4 text-sm font-medium text-slate-500">Kurir</button>
        </div>

        <div class="flex gap-2">
            <div class="relative flex-1 md:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input id="search" type="search" placeholder="Cari nama, email, HP..."
                    class="h-11 w-full rounded-xl border-slate-200 pl-11 text-base md:text-sm focus:border-violet-500 focus:ring-violet-500">
            </div>
            <button type="button" onclick="formUser()"
                class="hidden md:flex h-11 items-center gap-2 rounded-xl bg-violet-600 px-5 text-sm font-semibold text-white hover:bg-violet-700">
                <i class="fa-solid fa-user-plus"></i> Tambah
            </button>
        </div>
    </div>

    {{-- GRID --}}
    <div id="userGrid" class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3"></div>

    <div class="mt-6 flex flex-col items-center gap-3 md:flex-row md:justify-between">
        <p id="info" class="text-sm text-slate-500"></p>
        <div id="pagination" class="flex flex-wrap gap-2"></div>
    </div>

    {{-- FAB mobile --}}
    <button type="button" onclick="formUser()"
        class="md:hidden fixed bottom-20 right-5 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-violet-600 text-white shadow-lg shadow-violet-600/30">
        <i class="fa-solid fa-user-plus text-lg"></i>
    </button>

    {{-- MODAL FORM --}}
    <div id="modalUser" class="modal-overlay hidden opacity-0 fixed inset-0 z-50 bg-black/40 p-4 transition-opacity duration-300">
        <div class="modal-content w-full max-w-lg scale-95 -translate-y-6 rounded-2xl bg-white shadow-2xl transition-all duration-300">
            <form id="formUser">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="font-semibold text-slate-800" id="modalTitle">Tambah User</h3>
                    <button type="button" onclick="closeModal('modalUser')" class="h-11 w-11 rounded-xl hover:bg-slate-100">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="space-y-4 px-6 py-5">
                    <input type="hidden" name="id">

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Pilih Role</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="role" value="kasir" class="peer sr-only" checked>
                                <div class="rounded-xl border-2 border-slate-200 p-3 peer-checked:border-violet-600 peer-checked:bg-violet-50">
                                    <i class="fa-solid fa-cash-register text-violet-600"></i>
                                    <p class="mt-1 text-sm font-semibold text-slate-800">Kasir</p>
                                    <p class="text-xs text-slate-500">Order & pembayaran</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="role" value="owner" class="peer sr-only">
                                <div class="rounded-xl border-2 border-slate-200 p-3 peer-checked:border-violet-600 peer-checked:bg-violet-50">
                                    <i class="fa-solid fa-crown text-amber-500"></i>
                                    <p class="mt-1 text-sm font-semibold text-slate-800">Owner</p>
                                    <p class="text-xs text-slate-500">Akses penuh</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="role" value="kurir" class="peer sr-only">
                                <div class="rounded-xl border-2 border-slate-200 p-3 peer-checked:border-violet-600 peer-checked:bg-violet-50">
                                    <i class="fa-solid fa-motorcycle text-sky-600"></i>
                                    <p class="mt-1 text-sm font-semibold text-slate-800">Kurir</p>
                                    <p class="text-xs text-slate-500">Antar & jemput</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                        <input name="name" type="text" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                        <p class="err mt-1 text-xs text-red-600" data-for="name"></p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                            <input name="email" type="email" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                            <p class="err mt-1 text-xs text-red-600" data-for="email"></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">No HP</label>
                            <input name="no_hp" type="tel" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                            <p class="err mt-1 text-xs text-red-600" data-for="no_hp"></p>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            Password <span id="passHint" class="font-normal text-slate-400"></span>
                        </label>
                        <input name="password" type="password" autocomplete="new-password" class="h-11 w-full rounded-xl border-slate-200 text-base md:text-sm">
                        <p class="err mt-1 text-xs text-red-600" data-for="password"></p>
                    </div>
                </div>

                <div class="flex gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" onclick="closeModal('modalUser')" class="h-11 flex-1 rounded-xl border border-slate-200 text-sm font-medium text-slate-600">Batal</button>
                    <button type="submit" class="h-11 flex-1 rounded-xl bg-violet-600 text-sm font-semibold text-white hover:bg-violet-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('addon-script')
    <script>
        const ME = {{ auth()->id() }};
        let currentRole = '';
        let users = {};

        const avatarColor = (name) => ['bg-rose-100 text-rose-600', 'bg-sky-100 text-sky-600', 'bg-emerald-100 text-emerald-600',
            'bg-amber-100 text-amber-600', 'bg-violet-100 text-violet-600'][name.length % 5];
        const initials = (name) => name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();

        function cardSkeleton() {
            return `<div class="animate-pulse rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex gap-4"><div class="h-14 w-14 rounded-2xl bg-slate-200"></div>
                <div class="flex-1 space-y-2"><div class="h-4 w-2/3 rounded bg-slate-200"></div><div class="h-3 w-1/2 rounded bg-slate-200"></div></div></div></div>`;
        }

        function userCard(u) {
            users[u.id] = u;
            const avatar = u.foto_url ?
                `<img src="${u.foto_url}" class="h-14 w-14 rounded-2xl object-cover" alt="">` :
                `<div class="flex h-14 w-14 items-center justify-center rounded-2xl text-lg font-bold ${avatarColor(u.name)}">${esc(initials(u.name))}</div>`;
            const role = {
                owner: `<span class="inline-flex items-center gap-1 rounded-lg bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700"><i class="fa-solid fa-crown"></i> Owner</span>`,
                kasir: `<span class="inline-flex items-center gap-1 rounded-lg bg-violet-100 px-2 py-0.5 text-[11px] font-semibold text-violet-700"><i class="fa-solid fa-cash-register"></i> Kasir</span>`,
                kurir: `<span class="inline-flex items-center gap-1 rounded-lg bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-700"><i class="fa-solid fa-motorcycle"></i> Kurir</span>`,
            }[u.role];
            const isMe = u.id === ME;

            return `
            <div class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:shadow-md ${u.aktif ? '' : 'opacity-60'}">
                <div class="flex items-start gap-4">
                    ${avatar}
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="truncate font-semibold text-slate-800">${esc(u.name)}</h3>
                            ${isMe ? '<span class="rounded bg-slate-100 px-1.5 text-[10px] font-semibold text-slate-500">ANDA</span>' : ''}
                        </div>
                        <div class="mt-1">${role}</div>
                    </div>
                    ${isMe ? '' : `
                    <button type="button" onclick="toggleUser(${u.id})" title="${u.aktif ? 'Nonaktifkan' : 'Aktifkan'}"
                        class="relative h-7 w-12 shrink-0 rounded-full transition ${u.aktif ? 'bg-emerald-500' : 'bg-slate-300'}">
                        <span class="absolute top-1 h-5 w-5 rounded-full bg-white shadow transition-all ${u.aktif ? 'left-6' : 'left-1'}"></span>
                    </button>`}
                </div>

                <div class="mt-4 space-y-1.5 text-sm text-slate-500">
                    <p class="truncate"><i class="fa-regular fa-envelope w-5"></i> ${esc(u.email)}</p>
                    <p><i class="fa-solid fa-phone w-5"></i> ${esc(u.no_hp || '-')}</p>
                </div>

                <div class="mt-4 flex gap-2 border-t border-slate-100 pt-4">
                    <button type="button" onclick="formUser(${u.id})" class="h-10 flex-1 rounded-xl bg-slate-100 text-sm font-medium text-slate-700 hover:bg-slate-200">
                        <i class="fa-solid fa-pen mr-1"></i> Edit
                    </button>
                    ${isMe ? '' : `
                    <button type="button" onclick="hapusUser(${u.id})" class="h-10 w-11 rounded-xl text-red-600 hover:bg-red-50">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>`}
                </div>
            </div>`;
        }

        function loadData(page = 1) {
            ajaxRequest({
                url: "{{ route('user.data') }}",
                data: { page, search: $('#search').val(), role: currentRole },
                before: () => $('#userGrid').html(cardSkeleton().repeat(6)),
                success(res) {
                    $('#statTotal').text(res.stats.total);
                    $('#statOwner').text(res.stats.owner);
                    $('#statKasir').text(res.stats.kasir);
                    $('#statKurir').text(res.stats.kurir);
                    $('#statNonaktif').text(res.stats.nonaktif);

                    $('#userGrid').html(res.data.length ? res.data.map(userCard).join('') : `
                        <div class="col-span-full flex flex-col items-center rounded-2xl border-2 border-dashed border-slate-200 py-16 text-center">
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-violet-50"><i class="fa-solid fa-user-slash text-3xl text-violet-300"></i></div>
                            <h3 class="mt-4 font-semibold text-slate-700">Tidak ada anggota ditemukan</h3>
                            <p class="mt-1 text-sm text-slate-400">Coba kata kunci lain atau tambah anggota baru.</p>
                        </div>`);
                    renderInfo('#info', res);
                    renderPagination(res, 'loadData', '#pagination');
                },
                error: () => $('#userGrid').html('<p class="col-span-full py-14 text-center text-red-500">Gagal mengambil data.</p>'),
            });
        }

        function formUser(id = null) {
            const f = $('#formUser')[0];
            f.reset();
            $('.err').text('');
            const u = id ? users[id] : null;

            $('#modalTitle').text(u ? 'Edit User' : 'Tambah User');
            $('#passHint').text(u ? '(kosongkan bila tidak diganti)' : '');
            f.id.value = u?.id ?? '';
            if (u) {
                f.name.value = u.name;
                f.email.value = u.email;
                f.no_hp.value = u.no_hp ?? '';
                $(f).find(`[name=role][value=${u.role}]`).prop('checked', true);
            }
            // role sendiri tidak bisa diubah (server juga mengabaikannya), tapi tetap terkirim
            $(f).find('[name=role]').closest('.grid').toggleClass('pointer-events-none opacity-50', u?.id === ME);
            showModal('modalUser');
        }

        $('#formUser').on('submit', function (e) {
            e.preventDefault();
            const id = this.id.value;
            $('.err').text('');

            ajaxRequest({
                url: id ? `{{ url('user') }}/${id}` : "{{ route('user.store') }}",
                type: 'POST',
                data: $(this).serialize() + (id ? '&_method=PUT' : ''),
                success(res) {
                    App.loading.hide();
                    closeModal('modalUser');
                    showToast(res.message);
                    loadData();
                },
                error: (xhr) => formError(xhr, this),
            });
        });

        function toggleUser(id) {
            ajaxRequest({
                url: `{{ url('user') }}/${id}/toggle`,
                type: 'POST',
                data: { _method: 'PATCH' },
                success(res) {
                    showToast(res.message);
                    loadData();
                },
            });
        }

        function hapusUser(id) {
            hapusData({ url: `{{ url('user') }}/${id}`, name: esc(users[id].name), reload: loadData });
        }

        $('.role-tab').on('click', function () {
            $('.role-tab').removeClass('bg-white text-violet-700 shadow-sm').addClass('text-slate-500');
            $(this).addClass('bg-white text-violet-700 shadow-sm').removeClass('text-slate-500');
            currentRole = $(this).data('role');
            loadData();
        });

        debounceSearch('#search', () => loadData());
        loadData();
    </script>
@endpush
