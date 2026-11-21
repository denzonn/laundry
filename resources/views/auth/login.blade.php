@extends('layouts.auth')

@section('title', 'Login - Acas HRIS')

@section('content')
    <div class="min-h-screen bg-slate-100">

        <div class="grid min-h-screen lg:grid-cols-2">

            <div class="relative hidden overflow-hidden lg:flex bg-gradient-to-br from-[#620E0E] via-[#8F1111] to-[#B91C1C]">
                <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>

                <div
                    class="absolute top-1/2 left-1/2 h-[500px] w-[500px] -translate-x-1/2 -translate-y-1/2 rounded-full border border-white/10">
                </div>

                <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-white/10 blur-2xl"></div>

                <div class="absolute -top-40 -left-40 h-96 w-96 rounded-full bg-white/10 blur-[120px]">
                </div>

                <div class="absolute bottom-0 right-0 h-[420px] w-[420px] rounded-full bg-red-400/10 blur-[120px]">
                </div>

                <div class="absolute inset-0 opacity-[0.04]"
                    style="background-image: radial-gradient(white 1px, transparent 1px); background-size:24px 24px;">
                </div>

                <div class="relative z-10 flex h-full flex-col justify-center px-20">

                    {{-- Logo --}}
                    <div class="flex items-center gap-5">

                        <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-md">

                            <img src="{{ asset('images/logo.png') }}" class="h-14 w-auto" alt="Logo">

                        </div>

                        <div>

                            <h1 class="text-4xl font-bold tracking-tight text-white">
                                Acas Variasi
                            </h1>

                            <p class="mt-1 text-red-100">
                                Human Resource Information System
                            </p>

                        </div>

                    </div>

                    <div class="mt-10 h-1 w-20 rounded-full bg-white/40"></div>

                    <div class="mt-10">

                        <h2 class="max-w-xl text-6xl font-extrabold leading-tight text-white">

                            Kelola SDM
                            <br>
                            Lebih Mudah.

                        </h2>

                        <p class="mt-7 max-w-lg text-lg leading-8 text-red-100">

                            Platform internal Acas Variasi untuk membantu
                            pengelolaan data karyawan, absensi, cuti,
                            payroll, serta administrasi perusahaan
                            secara cepat dan efisien.

                        </p>

                    </div>

                    <div class="mt-12 grid grid-cols-2 gap-5 max-w-lg">

                        <div
                            class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-5 py-4 backdrop-blur">

                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15">

                                <i class="fa-solid fa-users text-white"></i>

                            </div>

                            <div>

                                <p class="font-semibold text-white">
                                    Data Karyawan
                                </p>

                                <p class="text-sm text-red-100">
                                    Terintegrasi
                                </p>

                            </div>

                        </div>

                        <div
                            class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-5 py-4 backdrop-blur">

                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15">

                                <i class="fa-solid fa-calendar-check text-white"></i>

                            </div>

                            <div>

                                <p class="font-semibold text-white">
                                    Absensi
                                </p>

                                <p class="text-sm text-red-100">
                                    Presensi Digital
                                </p>

                            </div>

                        </div>

                        <div
                            class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-5 py-4 backdrop-blur">

                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15">

                                <i class="fa-solid fa-wallet text-white"></i>

                            </div>

                            <div>

                                <p class="font-semibold text-white">
                                    Payroll
                                </p>

                                <p class="text-sm text-red-100">
                                    Otomatis
                                </p>

                            </div>

                        </div>

                        <div
                            class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-5 py-4 backdrop-blur">

                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15">

                                <i class="fa-solid fa-chart-line text-white"></i>

                            </div>

                            <div>

                                <p class="font-semibold text-white">
                                    Laporan
                                </p>

                                <p class="text-sm text-red-100">
                                    Real-time
                                </p>

                            </div>

                        </div>

                    </div>

                    <div class="mt-16 flex items-center gap-10">

                        <div>

                            <h3 class="text-3xl font-bold text-white">
                                HRIS
                            </h3>

                            <p class="text-red-100">
                                Internal System
                            </p>

                        </div>

                        <div class="h-10 w-px bg-white/20"></div>

                        <div>

                            <h3 class="text-3xl font-bold text-white">
                                24/7
                            </h3>

                            <p class="text-red-100">
                                Accessible
                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <div
                class="relative flex items-center justify-center overflow-hidden bg-gradient-to-br from-slate-100 via-white to-red-50 px-6">

                <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-red-100 blur-3xl opacity-70">
                </div>

                <div class="absolute left-0 bottom-0 h-72 w-72 rounded-full bg-white blur-3xl opacity-80">
                </div>

                <div class="relative w-full max-w-md">
                    <div
                        class="rounded-[32px]
                            border border-white/60
                            bg-white/90
                            p-10
                            shadow-[0_25px_70px_rgba(0,0,0,0.08)]
                            backdrop-blur-xl">

                        <div class="mb-8 flex justify-center lg:hidden">

                            <div
                                class="flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-red-700 to-red-600 shadow-xl">

                                <img src="{{ asset('images/logo.png') }}" class="h-12 w-auto" alt="Logo">

                            </div>

                        </div>

                        <div>

                            <span
                                class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-red-700">

                                ACAS HRIS

                            </span>

                            <h2 class="mt-4 text-3xl font-bold text-slate-800">

                                Selamat Datang 👋

                            </h2>

                            <p class="mt-2 text-slate-500">

                                Silakan login menggunakan akun perusahaan Anda.

                            </p>

                        </div>

                        <form action="{{ route('login') }}" method="POST" class="mt-8 space-y-6">

                            @csrf

                            <div>

                                <label class="mb-2 block text-sm font-semibold text-slate-700">

                                    Email

                                </label>

                                <div class="relative">

                                    <i
                                        class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                    </i>

                                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                        placeholder="Masukkan email"
                                        class="w-full rounded-2xl
                                                border border-slate-200
                                                bg-slate-50
                                                py-3.5
                                                pl-12
                                                pr-4
                                                text-slate-700
                                                placeholder:text-slate-400
                                                transition-all
                                                focus:border-red-600
                                                focus:bg-white
                                                focus:ring-4
                                                focus:ring-red-100
                                                @error('email') border-red-500 @enderror">

                                </div>

                                @error('email')
                                    <p class="mt-2 text-sm text-red-500">

                                        {{ $message }}

                                    </p>
                                @enderror

                            </div>

                            <div>

                                <label class="mb-2 block text-sm font-semibold text-slate-700">

                                    Password

                                </label>

                                <div class="relative">

                                    <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                    </i>

                                    <input id="password" type="password" name="password" required
                                        placeholder="Masukkan password"
                                        class="w-full rounded-2xl
                                                border border-slate-200
                                                bg-slate-50
                                                py-3.5
                                                pl-12
                                                pr-12
                                                transition-all
                                                focus:border-red-600
                                                focus:bg-white
                                                focus:ring-4
                                                focus:ring-red-100
                                                @error('password') border-red-500 @enderror">

                                    <button type="button" onclick="togglePassword()"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-600">

                                        <i id="eyeIcon" class="fa-regular fa-eye">
                                        </i>

                                    </button>

                                </div>

                                @error('password')
                                    <p class="mt-2 text-sm text-red-500">

                                        {{ $message }}

                                    </p>
                                @enderror

                            </div>

                            <div class="flex items-center justify-between">

                                <label class="flex cursor-pointer items-center gap-3">

                                    <input type="checkbox" name="remember"
                                        class="rounded border-slate-300 text-red-600 focus:ring-red-500">

                                    <span class="text-sm text-slate-600">

                                        Ingat saya

                                    </span>

                                </label>

                            </div>

                            <button type="submit"
                                class="group
                                        flex
                                        w-full
                                        items-center
                                        justify-center
                                        gap-2
                                        rounded-2xl
                                        bg-gradient-to-r
                                        from-red-700
                                        to-red-600
                                        py-4
                                        font-semibold
                                        text-white
                                        shadow-lg
                                        shadow-red-700/20
                                        transition-all
                                        duration-300
                                        hover:-translate-y-0.5
                                        hover:shadow-red-700/40">

                                <i
                                    class="fa-solid fa-right-to-bracket transition-transform duration-300 group-hover:translate-x-1">
                                </i>

                                Login

                            </button>

                        </form>

                    </div>

                    <div class="mt-8 text-center">

                        <p class="text-sm text-slate-500">

                            © {{ date('Y') }}
                            <span class="font-semibold text-slate-700">

                                Acas Variasi

                            </span>

                        </p>

                        <p class="mt-1 text-xs text-slate-400">

                            Human Resource Information System

                        </p>

                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('addon-script')
    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (password.type === 'password') {

                password.type = 'text';

                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');

            } else {

                password.type = 'password';

                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
@endpush
