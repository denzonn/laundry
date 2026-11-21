@extends('layouts.app')

@section('title', 'Profil - ' . config('app.name'))
@section('page_title', 'Profil Saya')
@section('page_description', 'Data diri & keamanan akun')

@php
    $user = auth()->user();
    $input = 'h-11 w-full rounded-xl border-slate-200 text-base md:text-sm focus:border-primary focus:ring-primary';
@endphp

@section('content')
        {{-- BANNER + AVATAR --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="h-28 md:h-36 bg-[radial-gradient(circle_at_20%_20%,#fca5a5,transparent_50%),radial-gradient(circle_at_80%_0%,#fcd34d,transparent_45%)] bg-primary"></div>

            <div class="flex flex-col items-center px-5 pb-6 md:flex-row md:items-end md:gap-5">
                <label class="relative -mt-14 cursor-pointer">
                    <div class="h-28 w-28 overflow-hidden rounded-full border-4 border-white bg-slate-100 shadow-lg">
                        <img id="fotoPreview" src="{{ $user->foto_url }}" class="{{ $user->foto_url ? '' : 'hidden' }} h-full w-full object-cover" alt="">
                        <div id="fotoIcon" class="{{ $user->foto_url ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-4xl font-bold text-slate-400">
                            {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                    <span class="absolute bottom-1 right-1 flex h-9 w-9 items-center justify-center rounded-full bg-slate-800 text-white shadow">
                        <i class="fa-solid fa-camera text-sm"></i>
                    </span>
                    <input type="file" name="foto" form="profilForm" accept="image/*" class="sr-only" id="fotoInput">
                </label>

                <div class="mt-3 text-center md:mb-1 md:text-left">
                    <h2 class="text-xl font-bold text-slate-800">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-500">
                        <i class="fa-solid {{ $user->isOwner() ? 'fa-crown text-amber-500' : 'fa-cash-register text-violet-500' }} mr-1"></i>
                        {{ ucfirst($user->role) }} · bergabung {{ $user->created_at->translatedFormat('M Y') }}
                    </p>
                    @error('foto') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            {{-- DATA DIRI --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="font-semibold text-slate-800"><i class="fa-regular fa-id-card mr-2 text-primary"></i>Data Diri</h3>

                <form id="profilForm" action="{{ route('profil.update') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                        <input name="name" value="{{ old('name', $user->name) }}" class="{{ $input }}">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                        <input name="email" type="email" value="{{ old('email', $user->email) }}" class="{{ $input }}">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">No HP</label>
                        <input name="no_hp" type="tel" value="{{ old('no_hp', $user->no_hp) }}" class="{{ $input }}">
                        @error('no_hp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="h-11 w-full rounded-xl bg-primary font-semibold text-white hover:bg-primary_hover">Simpan Profil</button>
                </form>
            </section>

            {{-- KEAMANAN --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h3 class="font-semibold text-slate-800"><i class="fa-solid fa-shield-halved mr-2 text-emerald-600"></i>Ganti Password</h3>

                <form action="{{ route('profil.password') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Password Lama</label>
                        <input name="current_password" type="password" autocomplete="current-password" class="{{ $input }}">
                        @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Password Baru</label>
                        <input name="password" id="newPass" type="password" autocomplete="new-password" class="{{ $input }}">
                        <div class="mt-2 flex gap-1">
                            @for ($i = 0; $i < 4; $i++) <span class="strength h-1.5 flex-1 rounded-full bg-slate-200"></span> @endfor
                        </div>
                        <p id="strengthText" class="mt-1 text-xs text-slate-400">Minimal 8 karakter</p>
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Ulangi Password Baru</label>
                        <input name="password_confirmation" type="password" autocomplete="new-password" class="{{ $input }}">
                    </div>
                    <button type="submit" class="h-11 w-full rounded-xl bg-slate-800 font-semibold text-white hover:bg-slate-900">Ganti Password</button>
                </form>
            </section>
        </div>
@endsection

@push('addon-script')
    <script>
        $('#fotoInput').on('change', function () {
            if (!this.files[0]) return;
            $('#fotoPreview').attr('src', URL.createObjectURL(this.files[0])).removeClass('hidden');
            $('#fotoIcon').removeClass('flex').addClass('hidden');
        });

        $('#newPass').on('input', function () {
            const v = this.value;
            const score = [v.length >= 8, /[A-Z]/.test(v) && /[a-z]/.test(v), /\d/.test(v), /[^A-Za-z0-9]/.test(v)].filter(Boolean).length;
            const colors = ['bg-red-500', 'bg-amber-500', 'bg-lime-500', 'bg-emerald-500'];
            $('.strength').each((i, el) => el.className = `strength h-1.5 flex-1 rounded-full ${i < score ? colors[score - 1] : 'bg-slate-200'}`);
            $('#strengthText').text(v ? ['Sangat lemah', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'][score] : 'Minimal 8 karakter');
        });
    </script>
@endpush
