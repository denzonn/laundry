@php
    $jumlahNotifikasi = 1;
@endphp

<div class="relative hidden md:block">

    <div class="flex flex-row gap-3 items-center">
        <div class="relative hidden md:block">

            <button id="notificationButton"
                class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition">

                <i class="fa-regular fa-bell text-lg text-slate-600"></i>

                @if ($jumlahNotifikasi > 0)
                    <span
                        class="absolute -top-1 -right-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white shadow">
                        1
                    </span>
                @endif

            </button>

            <div id="notificationDropdown"
                class="hidden absolute right-0 mt-3 w-[380px] rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden z-50">

                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">

                    <div>

                        <h3 class="text-sm font-semibold text-slate-800">
                            Notifikasi
                        </h3>

                        <p class="text-xs text-slate-400">
                            1 notifikasi baru
                        </p>

                    </div>

                    @if ($jumlahNotifikasi)
                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-semibold text-red-600">
                            Baru
                        </span>
                    @endif

                </div>

                <div class="max-h-[420px] overflow-y-auto">

                    {{-- @forelse($notifikasi as $item)
                        <a href="{{ route('notifikasi', ['id' => $item->id]) }}"
                            class="flex gap-3 border-b border-slate-100 px-5 py-4 transition
                        {{ $item->status == 0 ? 'bg-blue-50/60 hover:bg-blue-50' : 'bg-white hover:bg-slate-50' }}">

                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                        {{ $item->status == 0 ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-400' }}">

                                <i class="fa-regular fa-bell"></i>

                            </div>

                            <div class="flex-1 min-w-0">

                                <div class="flex items-start justify-between gap-2">

                                    <p
                                        class="text-sm leading-6
                        {{ $item->status == 0 ? 'font-semibold text-slate-800' : 'font-normal text-slate-500' }}">

                                        {{ $item->pesan }}

                                    </p>

                                    @if ($item->status == 0)
                                        <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-blue-600"
                                            title="Belum dibaca">
                                        </span>
                                    @endif

                                </div>

                                <div class="mt-2 flex items-center gap-2">

                                    <i
                                        class="fa-regular fa-clock text-[11px]
                        {{ $item->status == 0 ? 'text-blue-400' : 'text-slate-400' }}">
                                    </i>

                                    <span
                                        class="text-xs
                        {{ $item->status == 0 ? 'text-blue-500' : 'text-slate-400' }}">

                                        {{ $item->created_at->diffForHumans() }}

                                    </span>

                                    @if ($item->status == 0)
                                        <span class="text-[10px] font-medium text-blue-500">
                                            • Baru
                                        </span>
                                    @endif

                                </div>

                            </div>

                        </a>

                    @empty

                        <div class="flex flex-col items-center justify-center py-12">

                            <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">

                                <i class="fa-regular fa-bell-slash text-2xl text-slate-400"></i>

                            </div>

                            <h4 class="text-sm font-semibold text-slate-700">
                                Tidak ada notifikasi
                            </h4>

                            <p class="mt-1 text-xs text-slate-400">
                                Belum ada notifikasi untuk Anda.
                            </p>

                        </div>
                    @endforelse --}}

                </div>

            </div>

        </div>

        <button id="profileButton" class="flex items-center gap-3 rounded-xl px-2 py-2 hover:bg-slate-100 transition">

            <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100">

                @if (auth()->user()->foto)
                    <img src="{{ asset(auth()->user()->foto) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center">

                        <i class="fa-solid fa-user text-slate-500"></i>

                    </div>
                @endif

            </div>

            <div class="hidden md:block text-left">

                <h4 class="text-sm font-semibold text-slate-700">
                    Denson Patibang
                </h4>

                <p class="text-xs text-slate-400">
                    Superadmin
                </p>

            </div>

            <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>

        </button>
    </div>

    <div id="profileDropdown"
        class="hidden absolute right-0 mt-3 w-56 bg-white border border-slate-200 rounded-2xl shadow-lg overflow-hidden">

        <form action="{{ route('logout') }}" method="POST">

            @csrf

            <button class="w-full text-left px-5 py-3 text-red-600 hover:bg-red-50 flex items-center gap-3">

                <i class="fa-solid fa-right-from-bracket w-5"></i>

                Logout

            </button>

        </form>

    </div>

</div>

<script>
    const btn = document.getElementById('profileButton');
    const menu = document.getElementById('profileDropdown');

    btn.onclick = () => {

        menu.classList.toggle('hidden');

    }

    document.addEventListener('click', function(e) {

        if (!btn.contains(e.target) && !menu.contains(e.target))
            menu.classList.add('hidden');

    });

    const notificationBtn = document.getElementById('notificationButton');
    const notificationMenu = document.getElementById('notificationDropdown');

    notificationBtn.onclick = () => {
        notificationMenu.classList.toggle('hidden');
    };

    document.addEventListener('click', function(e) {

        if (
            !notificationBtn.contains(e.target) &&
            !notificationMenu.contains(e.target)
        ) {
            notificationMenu.classList.add('hidden');
        }

    });
</script>
