@extends('layouts.auth')

@section('title', 'Login - ' . config('app.name'))

@php $toko = \App\Models\Pengaturan::toko(); @endphp

@section('content')
    <div class="grid min-h-[100dvh] bg-background lg:grid-cols-2">

        {{-- HERO: strip ringkas di HP, panel penuh di desktop --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-primary_dark via-primary to-sky-700 px-6 pb-24 pt-12 text-white lg:flex lg:flex-col lg:justify-center lg:px-16 lg:py-16 xl:px-24">
            {{-- gelembung sabun --}}
            <span class="absolute -right-10 -top-10 h-40 w-40 rounded-full border border-white/20 bg-white/5 lg:h-72 lg:w-72"></span>
            <span class="absolute right-16 top-24 h-10 w-10 rounded-full border border-white/25 bg-white/10 lg:right-40 lg:top-48 lg:h-16 lg:w-16"></span>
            <span class="absolute -bottom-20 -left-16 h-56 w-56 rounded-full bg-sky-400/20 blur-3xl lg:h-96 lg:w-96"></span>
            <span class="absolute bottom-24 right-1/3 hidden h-6 w-6 rounded-full border border-white/30 lg:block"></span>

            <div class="relative mx-auto max-w-md lg:mx-0 lg:max-w-lg">
                <div class="flex items-center gap-3">
                    <img src="{{ $toko->logo ? asset('storage/' . $toko->logo) : asset('icons/icon-192.png') }}"
                        class="h-12 w-12 rounded-2xl bg-white object-contain p-1 shadow-lg lg:h-16 lg:w-16" alt="">
                    <div>
                        <p class="text-lg font-bold leading-tight lg:text-2xl">{{ $toko->nama_toko }}</p>
                        <p class="text-xs text-sky-100/80 lg:text-sm">Laundry Management System</p>
                    </div>
                </div>

                <h1 class="mt-8 text-3xl font-extrabold leading-tight lg:mt-14 lg:text-5xl xl:text-6xl">
                    Cucian rapi,<br><span class="text-sky-300">kasir</span> santai.
                </h1>
                <p class="mt-3 max-w-md text-sm leading-relaxed text-sky-100/80 lg:mt-6 lg:text-lg">
                    Catat order, pantau status cucian, terima pembayaran, dan kirim nota WhatsApp langsung dari HP.
                </p>

                <div class="mt-10 hidden grid-cols-2 gap-3 lg:grid">
                    @foreach ([['fa-receipt', 'Order & status'], ['fa-brands fa-whatsapp', 'Nota WhatsApp'], ['fa-motorcycle', 'Antar jemput'], ['fa-chart-line', 'Laporan harian']] as [$ikon, $teks])
                        <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-4 py-3 backdrop-blur">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-sky-200">
                                <i class="{{ str_contains($ikon, 'fa-brands') ? $ikon : 'fa-solid ' . $ikon }}"></i>
                            </span>
                            <span class="text-sm font-medium">{{ $teks }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- FORM --}}
        <div class="relative -mt-16 flex justify-center px-4 pb-10 lg:mt-0 lg:items-center lg:px-10">
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-xl shadow-primary/10 ring-1 ring-slate-100 sm:p-8 lg:shadow-none lg:ring-0">
                <span class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                    <span class="h-1.5 w-1.5 rounded-full bg-primary"></span> Masuk ke akun
                </span>
                <h2 class="mt-3 text-2xl font-bold text-slate-800">Selamat datang 👋</h2>
                <p class="mt-1 text-sm text-slate-500">Masukkan email dan password Anda.</p>

                <form action="{{ route('login') }}" method="POST" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                        <div class="relative">
                            <i class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" inputmode="email"
                                placeholder="nama@email.com"
                                class="h-12 w-full rounded-xl border-slate-200 bg-slate-50 pl-11 text-base focus:border-primary focus:bg-white focus:ring-primary/20 @error('email') border-red-500 @enderror">
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"
                                class="h-12 w-full rounded-xl border-slate-200 bg-slate-50 px-11 text-base focus:border-primary focus:bg-white focus:ring-primary/20 @error('password') border-red-500 @enderror">
                            <button type="button" onclick="togglePassword()" aria-label="Tampilkan password"
                                class="absolute right-1 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 hover:text-primary">
                                <i id="eyeIcon" class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex h-11 cursor-pointer items-center gap-3 text-sm text-slate-600">
                        <input type="checkbox" name="remember" class="h-5 w-5 rounded border-slate-300 text-primary focus:ring-primary/30">
                        Ingat saya di perangkat ini
                    </label>

                    <button type="submit"
                        class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-primary font-semibold text-white shadow-lg shadow-primary/25 transition hover:bg-primary_hover active:scale-[0.98]">
                        Masuk <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                @if (Route::has('lacak'))
                    <a href="{{ route('lacak') }}" class="mt-6 flex h-11 items-center justify-center gap-2 rounded-xl border border-dashed border-slate-200 text-sm text-slate-500 hover:border-primary hover:text-primary">
                        <i class="fa-solid fa-magnifying-glass-location"></i> Lacak cucian tanpa login
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('addon-script')
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !show);
            icon.classList.toggle('fa-eye-slash', show);
        }
    </script>
@endpush
