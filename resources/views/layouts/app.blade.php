<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    @include('includes.style')
    @stack('addon-style')

    @include('includes.pwa')
</head>

<body class="bg-background font-poppins text-slate-800 antialiased print:bg-white [print-color-adjust:exact]">

    <div class="flex min-h-screen">

        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-50 w-[280px] max-w-[85vw] -translate-x-full bg-primary_dark text-white transition-transform duration-300 lg:translate-x-0 print:hidden">
            @include('includes.sidebar')
        </aside>

        <div id="sidebarOverlay" class="fixed inset-0 z-40 hidden bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

        <div class="flex min-h-screen min-w-0 flex-1 flex-col lg:ml-[280px] print:ml-0">

            <header
                class="sticky top-0 z-30 flex h-16 items-center justify-between gap-3 border-b border-slate-200/70 bg-white/85 px-4 backdrop-blur-md lg:h-[72px] lg:px-8 print:hidden">

                <div class="flex min-w-0 items-center gap-3">
                    <button id="openSidebar" type="button" aria-label="Buka menu"
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100 lg:hidden">
                        <i class="fa-solid fa-bars-staggered"></i>
                    </button>

                    <div class="min-w-0">
                        <h1 class="truncate text-lg font-semibold text-slate-800 lg:text-xl">@yield('page_title')</h1>
                        <p class="hidden truncate text-sm text-slate-400 md:block">@yield('page_description')</p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    @if (!auth()->user()->isKurir() && Route::has('order.create') && !request()->routeIs('order.create'))
                        <a href="{{ route('order.create') }}"
                            class="hidden h-11 items-center gap-2 rounded-xl bg-primary px-4 text-sm font-semibold text-white shadow-sm shadow-primary/30 hover:bg-primary_hover lg:flex">
                            <i class="fa-solid fa-plus"></i> Order Baru
                        </a>
                    @endif
                    @include('includes.navbar')
                </div>

            </header>

            <main class="flex-1 min-w-0 overflow-x-clip p-4 md:p-6 lg:p-8 {{ View::hasSection('no_bottom_nav') ? '' : 'pb-28' }} lg:pb-8 print:p-0">
                @yield('content')
            </main>

            <footer class="hidden border-t border-slate-200/70 px-8 py-4 text-xs text-slate-400 lg:flex lg:items-center lg:justify-between print:hidden">
                <span>© {{ date('Y') }} {{ \App\Models\Pengaturan::toko()->nama_toko }}</span>
                <span>v1.0.0</span>
            </footer>

        </div>

    </div>

    @unless (View::hasSection('no_bottom_nav'))
        @include('includes.bottom-nav')
    @endunless

    @include('includes.script')
    <script>
        App.submitLoading();
    </script>
    {{--
        Helper global (ajaxRequest, debounceSearch, dst.) ada di bundle Vite yang dimuat sebagai <script type="module">
        → baru jalan SETELAH HTML selesai di-parse. Script halaman ditahan di <template> lalu dijalankan berurutan
        dari module di bawah (module jalan sesuai urutan dokumen, jadi app.js pasti sudah siap). Tetap script klasik,
        jadi function di dalamnya tetap global untuk onclick.
    --}}
    <template id="pageScripts">@stack('addon-script')</template>
    <script type="module">
        for (const node of [...document.getElementById('pageScripts').content.childNodes]) {
            if (node.nodeName !== 'SCRIPT') continue;
            const s = document.createElement('script');
            s.textContent = node.textContent;
            document.body.appendChild(s);
        }
    </script>

    <script>
        (() => {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const buka = () => { sidebar.classList.remove('-translate-x-full'); sidebar.classList.add('shadow-2xl'); overlay.classList.remove('hidden'); document.body.classList.add('overflow-hidden', 'lg:overflow-auto'); };
            const tutup = () => { sidebar.classList.add('-translate-x-full'); sidebar.classList.remove('shadow-2xl'); overlay.classList.add('hidden'); document.body.classList.remove('overflow-hidden', 'lg:overflow-auto'); };

            document.querySelectorAll('[data-open-sidebar], #openSidebar').forEach((b) => b.addEventListener('click', buka));
            document.querySelectorAll('[data-close-sidebar]').forEach((b) => b.addEventListener('click', tutup));
            overlay.addEventListener('click', tutup);
            document.addEventListener('keydown', (e) => e.key === 'Escape' && tutup());
        })();
    </script>

</body>

</html>
