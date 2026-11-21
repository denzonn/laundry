@php
    $role = auth()->user()->role ?? '';
@endphp

<div class="flex flex-col h-full">

    {{-- LOGO --}}
    <div class="h-[72px]
           flex items-center
           px-6
           border-b
           border-slate-200">

        <div class="flex items-center gap-3">

            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white">

                <img src="{{ asset('images/logo.png') }}" alt="">

            </div>

            <div>

                <h2 class="font-semibold text-slate-800">
                    HRIS
                </h2>

                <p class="text-xs text-slate-400">
                    Acas Variasi
                </p>

            </div>

        </div>

    </div>

    {{-- MENU --}}
    <div class="flex-1 overflow-y-auto px-4 py-6">

        {{-- DASHBOARD --}}
        @if (auth()->user()->otoritas_id == 1)
            <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                Dashboard
            </p>

            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        {{ request()->routeIs('dashboard') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                <i class="fa-solid fa-house w-5 text-center"></i>

                <span class="font-medium">
                    Dashboard
                </span>

            </a>

            <div class="mt-8">

                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Master
                </p>

                <div class="space-y-1">

                    <a href="{{ route('cabang') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('cabang*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-building w-5 text-center"></i>

                        Cabang

                    </a>

                    <a href="{{ route('divisi') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('divisi*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-building-columns w-5 text-center"></i>

                        Divisi

                    </a>

                    <a href="{{ route('jabatan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('jabatan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-briefcase w-5 text-center"></i>

                        Jabatan

                    </a>

                    <a href="{{ route('jobdesk') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('jobdesk*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-list-check w-5 text-center"></i>

                        Jobdesk

                    </a>

                    <a href="{{ route('user') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('user*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-user w-5 text-center"></i>

                        User / Pegawai

                    </a>

                    <a href="{{ route('product') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('product*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-box w-5 text-center"></i>

                        Produk Booking

                    </a>

                </div>

            </div>

            <div class="mt-8">

                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Absensi
                </p>

                <div class="space-y-1">

                    <a href="{{ route('pengaturan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('pengaturan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-gear w-5 text-center"></i>

                        Pengaturan

                    </a>

                    <a href="{{ route('libur') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('libur*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar w-5 text-center"></i>

                        Hari Libur

                    </a>

                    <a href="{{ route('lembur') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('lembur*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-clock w-5 text-center"></i>

                        Perintah Lembur

                    </a>

                    <a href="{{ route('absensi_karyawan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                    {{ request()->routeIs('absensi_karyawan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar w-5 text-center"></i>

                        Input Absensi

                    </a>

                    <a href="{{ route('izin_karyawan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                    {{ request()->routeIs('izin_karyawan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar-xmark w-5 text-center"></i>

                        Izin, Sakit, Cuti

                    </a>

                    <a href="{{ route('rekap_absensi') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('rekap_absensi*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-clipboard-check w-5 text-center"></i>

                        Rekap Absensi

                    </a>

                </div>

            </div>

            <div class="mt-8">

                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Karyawan
                </p>

                <div class="space-y-1">

                    <a href="{{ route('payroll') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('payroll*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-wallet w-5 text-center"></i>

                        Payroll
                        </p>

                    </a>
                </div>
            </div>

            <div class="mt-8">

                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Rekruitmen
                </p>

                <div class="space-y-1">
                    <a href="{{ route('rekruitmen.lowongan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('rekruitmen.lowongan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-briefcase w-5 text-center"></i>

                        Lowongan Kerja

                    </a>
                    <a href="{{ route('rekruitmen.pelamar') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('rekruitmen.pelamar*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-users w-5 text-center"></i>

                        Data Pelamar

                    </a>
                    <a href="{{ route('rekruitmen.landing-page') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('rekruitmen.landing-page*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-sliders w-5 text-center"></i>

                        Pengaturan Landing Page

                    </a>

                </div>

            </div>

            <div class="mt-8">

                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Kendaraan & Booking
                </p>

                <div class="space-y-1">

                    <a href="{{ route('kendaraan.booking') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('kendaraan.booking*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-car w-5 text-center"></i>

                        Data Booking Service

                    </a>

                    <a href="{{ route('kendaraan.pemasangan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('kendaraan.pemasangan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-screwdriver-wrench w-5 text-center"></i>

                        Cek & Pemasangan Kendaraan

                    </a>

                </div>

            </div>
        @elseif (auth()->user()->otoritas_id == 2)
            <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                Dashboard
            </p>

            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        {{ request()->routeIs('dashboard') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                <i class="fa-solid fa-house w-5 text-center"></i>

                <span class="font-medium">
                    Dashboard
                </span>

            </a>

            <div class="mt-8">

                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Master
                </p>

                <div class="space-y-1">

                    <a href="{{ route('divisi') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('divisi*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-building-columns w-5 text-center"></i>

                        Divisi

                    </a>

                    <a href="{{ route('jabatan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('jabatan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-briefcase w-5 text-center"></i>

                        Jabatan

                    </a>

                    <a href="{{ route('jobdesk') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('jobdesk*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-list-check w-5 text-center"></i>

                        Jobdesk

                    </a>

                    <a href="{{ route('user') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('user*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-user w-5 text-center"></i>

                        User / Pegawai

                    </a>

                </div>

            </div>

            <div class="mt-8">

                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Absensi
                </p>

                <div class="space-y-1">

                    <a href="{{ route('pengaturan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('pengaturan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-gear w-5 text-center"></i>

                        Pengaturan

                    </a>

                    <a href="{{ route('libur') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('libur*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar w-5 text-center"></i>

                        Hari Libur

                    </a>

                    <a href="{{ route('lembur') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('lembur*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-clock w-5 text-center"></i>

                        Perintah Lembur

                    </a>

                    <a href="{{ route('absensi_karyawan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                    {{ request()->routeIs('absensi_karyawan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar w-5 text-center"></i>

                        Input Absensi

                    </a>

                    <a href="{{ route('izin_karyawan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                    {{ request()->routeIs('izin_karyawan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar-xmark w-5 text-center"></i>

                        Izin, Sakit, Cuti

                    </a>

                    <a href="{{ route('rekap_absensi') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('rekap_absensi*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-clipboard-check w-5 text-center"></i>

                        Rekap Absensi

                    </a>

                </div>

            </div>

            <div class="mt-8">

                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Karyawan
                </p>

                <div class="space-y-1">

                    <a href="{{ route('payroll') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('payroll*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-wallet w-5 text-center"></i>

                        Payroll

                    </a>
                </div>
            </div>

            <div class="mt-8">

                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Rekruitmen
                </p>

                <div class="space-y-1">
                    <a href="{{ route('rekruitmen.lowongan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('rekruitmen.lowongan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-briefcase w-5 text-center"></i>

                        Lowongan Kerja

                    </a>
                    <a href="{{ route('rekruitmen.pelamar') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('rekruitmen.pelamar*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-users w-5 text-center"></i>

                        Data Pelamar

                    </a>
                    <a href="{{ route('rekruitmen.landing-page') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('rekruitmen.landing-page*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-sliders w-5 text-center"></i>

                        Pengaturan Landing Page

                    </a>

                </div>

            </div>
        @elseif (auth()->user()->otoritas_id == 3)
            <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                Dashboard
            </p>

            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        {{ request()->routeIs('dashboard') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                <i class="fa-solid fa-house w-5 text-center"></i>

                <span class="font-medium">
                    Dashboard
                </span>

            </a>

            <div class="mt-8">
                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Absensi
                </p>

                <div class="space-y-1">

                    <a href="{{ route('lembur') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('lembur*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-clock w-5 text-center"></i>

                        Perintah Lembur

                    </a>

                    <a href="{{ route('absensi_karyawan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('absensi_karyawan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar w-5 text-center"></i>

                        Input Absensi

                    </a>

                    <a href="{{ route('izin_karyawan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('izin_karyawan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar-xmark w-5 text-center"></i>

                        Izin, Sakit, Cuti

                    </a>


                    <a href="{{ route('rekap_absensi') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('rekap_absensi*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-clipboard-check w-5 text-center"></i>

                        Rekap Absensi

                    </a>

                </div>

            </div>

            <div class="mt-8">

                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Karyawan
                </p>

                <div class="space-y-1">

                    <a href="{{ route('jobdesk') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('jobdesk*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-list-check w-5 text-center"></i>

                        Jobdesk

                    </a>

                    <a href="{{ route('slip_gaji') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('slip_gaji*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-wallet w-5 text-center"></i>

                        Slip Gaji
                    </a>
                </div>
            </div>


            <div class="mt-8">

                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Kendaraan & Booking
                </p>

                <div class="space-y-1">

                    <a href="{{ route('kendaraan.booking') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('kendaraan.booking*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-car w-5 text-center"></i>

                        Data Booking Service

                    </a>

                    <a href="{{ route('kendaraan.pemasangan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('kendaraan.pemasangan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-screwdriver-wrench w-5 text-center"></i>

                        Cek & Pemasangan Kendaraan

                    </a>

                </div>

            </div>
        @elseif (auth()->user()->otoritas_id == 4)
            <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                Dashboard
            </p>

            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->routeIs('dashboard') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                <i class="fa-solid fa-house w-5 text-center"></i>

                <span class="font-medium">
                    Dashboard
                </span>

            </a>

            <div class="mt-8">
                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Absensi
                </p>

                <div class="space-y-1">

                    <a href="{{ route('lembur') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                    {{ request()->routeIs('lembur*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-clock w-5 text-center"></i>

                        Perintah Lembur

                    </a>

                    <a href="{{ route('absensi_karyawan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                    {{ request()->routeIs('absensi_karyawan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar w-5 text-center"></i>

                        Input Absensi

                    </a>

                    <a href="{{ route('izin_karyawan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                    {{ request()->routeIs('izin_karyawan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar-xmark w-5 text-center"></i>

                        Izin, Sakit, Cuti

                    </a>


                    <a href="{{ route('rekap_absensi') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                    {{ request()->routeIs('rekap_absensi*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-clipboard-check w-5 text-center"></i>

                        Rekap Absensi

                    </a>

                </div>

            </div>

            <div class="mt-8">

                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Karyawan
                </p>

                <div class="space-y-1">

                    <a href="{{ route('jobdesk') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('jobdesk*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-list-check w-5 text-center"></i>

                        Jobdesk

                    </a>

                    <a href="{{ route('slip_gaji') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('slip_gaji*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-wallet w-5 text-center"></i>

                        Slip Gaji
                    </a>
                </div>
            </div>
        @elseif (auth()->user()->otoritas_id == 5)
            <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                Dashboard
            </p>

            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                    {{ request()->routeIs('dashboard') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                <i class="fa-solid fa-house w-5 text-center"></i>

                <span class="font-medium">
                    Dashboard
                </span>

            </a>

            <div class="mt-8">
                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Absensi
                </p>

                <div class="space-y-1">

                    <a href="{{ route('lembur') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('lembur*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-clock w-5 text-center"></i>

                        Perintah Lembur

                    </a>

                    <a href="{{ route('absensi_karyawan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->routeIs('absensi_karyawan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar w-5 text-center"></i>

                        Absensi

                    </a>

                    <a href="{{ route('izin_karyawan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->routeIs('izin_karyawan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar-xmark w-5 text-center"></i>

                        Izin, Sakit, Cuti
                    </a>
                </div>
            </div>

            <div class="mt-8">
                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Karyawan
                </p>

                <div class="space-y-1">

                    <a href="{{ route('jobdesk') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        {{ request()->routeIs('jobdesk*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-list-check w-5 text-center"></i>
                        Jobdesk
                    </a>

                    <a href="{{ route('slip_gaji') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('slip_gaji*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-wallet w-5 text-center"></i>
                        Slip Gaji
                    </a>
                </div>
            </div>
        @elseif (auth()->user()->otoritas_id == 6)
            <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                Dashboard
            </p>

            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                    {{ request()->routeIs('dashboard') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                <i class="fa-solid fa-house w-5 text-center"></i>

                <span class="font-medium">
                    Dashboard
                </span>

            </a>

            <div class="mt-8">
                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Absensi
                </p>

                <div class="space-y-1">

                    <a href="{{ route('lembur') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('lembur*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-clock w-5 text-center"></i>

                        Perintah Lembur

                    </a>

                    <a href="{{ route('absensi_karyawan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->routeIs('absensi_karyawan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar w-5 text-center"></i>

                        Absensi

                    </a>

                    <a href="{{ route('izin_karyawan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->routeIs('izin_karyawan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar-xmark w-5 text-center"></i>

                        Izin, Sakit, Cuti
                    </a>
                </div>
            </div>

            <div class="mt-8">

                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Kendaraan & Booking
                </p>

                <div class="space-y-1">

                    <a href="{{ route('kendaraan.booking') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('kendaraan.booking*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-car w-5 text-center"></i>

                        Data Booking Service

                    </a>

                    <a href="{{ route('kendaraan.pemasangan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('kendaraan.pemasangan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-screwdriver-wrench w-5 text-center"></i>

                        Cek & Pemasangan Kendaraan

                    </a>

                </div>

            </div>

            <div class="mt-8">
                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Karyawan
                </p>

                <div class="space-y-1">

                    <a href="{{ route('jobdesk') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        {{ request()->routeIs('jobdesk*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-list-check w-5 text-center"></i>
                        Jobdesk
                    </a>

                    <a href="{{ route('slip_gaji') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('slip_gaji*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-wallet w-5 text-center"></i>
                        Slip Gaji
                    </a>
                </div>
            </div>
        @elseif (auth()->user()->otoritas_id == 7)
            <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                Dashboard
            </p>

            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                    {{ request()->routeIs('dashboard') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                <i class="fa-solid fa-house w-5 text-center"></i>

                <span class="font-medium">
                    Dashboard
                </span>

            </a>

            <div class="mt-8">
                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Absensi
                </p>

                <div class="space-y-1">

                    <a href="{{ route('lembur') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('lembur*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-clock w-5 text-center"></i>

                        Perintah Lembur

                    </a>

                    <a href="{{ route('absensi_karyawan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->routeIs('absensi_karyawan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar w-5 text-center"></i>

                        Absensi

                    </a>

                    <a href="{{ route('izin_karyawan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->routeIs('izin_karyawan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-calendar-xmark w-5 text-center"></i>

                        Izin, Sakit, Cuti
                    </a>
                </div>
            </div>

            <div class="mt-8">

                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Kendaraan & Booking
                </p>

                <div class="space-y-1">
                    <a href="{{ route('kendaraan.pemasangan') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('kendaraan.pemasangan*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-screwdriver-wrench w-5 text-center"></i>

                        Cek & Pemasangan Kendaraan

                    </a>

                </div>

            </div>

            <div class="mt-8">
                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Karyawan
                </p>

                <div class="space-y-1">

                    <a href="{{ route('jobdesk') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        {{ request()->routeIs('jobdesk*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-list-check w-5 text-center"></i>
                        Jobdesk
                    </a>

                    <a href="{{ route('slip_gaji') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('slip_gaji*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-wallet w-5 text-center"></i>
                        Slip Gaji
                    </a>
                </div>
            </div>
        @endif

        @if (auth()->user()->otoritas_id != 1)
            <div class="mt-8">
                <p class="text-xs uppercase tracking-widest text-slate-400 mb-3 px-3">
                    Profile
                </p>

                <div class="space-y-1">

                    <a href="{{ route('profile') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                                {{ request()->routeIs('profile*') ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-100' }}">

                        <i class="fa-solid fa-user w-5 text-center"></i>

                        Profile

                    </a>

                </div>

            </div>
        @endif

    </div>

    {{-- USER --}}
    <div class="border-t border-slate-200 p-4">

        <form action="{{ route('logout') }}" method="POST" class="mt-4">

            @csrf

            <button
                class="w-full rounded-xl py-3
                       border border-red-200
                       text-red-600
                       hover:bg-red-600
                       hover:text-white
                       transition">

                <i class="fa-solid fa-right-from-bracket mr-2"></i>

                Logout

            </button>

        </form>

    </div>

</div>
