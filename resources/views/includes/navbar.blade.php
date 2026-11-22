@php $user = auth()->user(); @endphp

<div class="relative">

    <button id="profileButton" class="flex h-11 items-center gap-3 rounded-xl px-1.5 transition hover:bg-slate-100 md:px-2" aria-label="Menu akun">

        <div class="h-9 w-9 overflow-hidden rounded-full bg-primary/10 ring-2 ring-primary/20">
            @if ($user->foto_url)
                <img src="{{ $user->foto_url }}" class="w-full h-full object-cover" alt="">
            @else
                <div class="flex h-full w-full items-center justify-center text-sm font-semibold text-primary">
                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
            @endif
        </div>

        <div class="hidden md:block text-left">
            <h4 class="text-sm font-semibold text-slate-700">{{ $user->name }}</h4>
            <p class="text-xs text-slate-400">{{ ucfirst($user->role ?? '') }}</p>
        </div>

        <i class="fa-solid fa-chevron-down hidden text-xs text-slate-400 md:inline"></i>

    </button>

    <div id="profileDropdown"
        class="hidden absolute right-0 mt-3 w-56 bg-white border border-slate-200 rounded-2xl shadow-lg overflow-hidden z-50">
        <div class="md:hidden px-5 py-3 border-b border-slate-100">
            <h4 class="text-sm font-semibold text-slate-700">{{ $user->name }}</h4>
            <p class="text-xs text-slate-400">{{ ucfirst($user->role ?? '') }}</p>
        </div>
        <a href="{{ route('profil') }}" class="h-11 px-5 text-slate-600 hover:bg-slate-50 flex items-center gap-3">
            <i class="fa-solid fa-user-pen w-5"></i>
            Profil Saya
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="w-full h-11 text-left px-5 text-red-600 hover:bg-red-50 flex items-center gap-3">
                <i class="fa-solid fa-right-from-bracket w-5"></i>
                Logout
            </button>
        </form>
    </div>

</div>

<script>
    const profileBtn = document.getElementById('profileButton');
    const profileMenu = document.getElementById('profileDropdown');

    profileBtn.onclick = () => profileMenu.classList.toggle('hidden');

    document.addEventListener('click', (e) => {
        if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) profileMenu.classList.add('hidden');
    });
</script>
