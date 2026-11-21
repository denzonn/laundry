<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    @include('includes.style')
    @stack('addon-style')

    <!-- Favicon / App Icon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
</head>

<body class="bg-slate-50 font-poppins overflow-x-hidden print:bg-white [print-color-adjust:exact]">

    <div class="flex min-h-screen">

        <aside id="sidebar"
            class="fixed top-0 left-0
                   w-[280px]
                   h-screen
                   bg-white
                   border-r border-slate-200
                   z-50

                   transition-all duration-300

                   -translate-x-full
                   lg:translate-x-0 print:hidden">

            @include('includes.sidebar')

        </aside>

        <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black/40 z-40 lg:hidden">
        </div>

        <div class="flex-1 min-w-0 lg:ml-[280px] print:ml-0 flex flex-col min-h-screen">

            <header
                class="sticky top-0 z-30
                    h-[72px]
                    bg-white
                    border-b border-slate-200
                    px-4 lg:px-8
                    flex items-center justify-between print:hidden">

                <div class="flex items-center gap-4">

                    <button id="openSidebar" class="lg:hidden w-10 h-10 rounded-xl hover:bg-slate-100">

                        <i class="fa-solid fa-bars"></i>

                    </button>

                    <div>

                        <h1 class="text-xl font-semibold text-slate-800">
                            @yield('page_title')
                        </h1>

                        <p class="hidden md:block text-sm text-slate-400">
                            @yield('page_description')
                        </p>

                    </div>

                </div>

                @include('includes.navbar')

            </header>

            <main class="flex-1 min-w-0 p-4 md:p-6 lg:p-8 overflow-x-clip {{ View::hasSection('no_bottom_nav') ? '' : 'pb-24' }} lg:pb-8 print:p-0"> 

                @yield('content')

            </main>

            <footer
                class="bg-white/60 backdrop-blur-md
                border-t border-slate-100
                px-8 py-4 print:hidden">

                <div class="flex items-center justify-between">

                    <div>

                        <h5 class="text-sm font-semibold text-slate-700">
                            {{ config('app.name') }}
                        </h5>

                        <p class="text-xs text-slate-400">
                            © {{ date('Y') }} All rights reserved.
                        </p>

                    </div>

                    <div class="text-right">

                        <p class="text-sm text-slate-500">
                            {{ config('app.name') }}
                        </p>

                        <p class="text-xs text-slate-400">
                            Version 1.0.0
                        </p>

                    </div>

                </div>

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
    @stack('addon-script')

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        document.getElementById('openSidebar')?.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    </script>

</body>

</html>
