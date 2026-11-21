@php $user = auth()->user(); @endphp

<div class="relative">

    <button id="profileButton" class="flex items-center gap-3 rounded-xl px-2 py-2 hover:bg-slate-100 transition">

        <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100">
            @if ($user->foto)
                <img src="{{ asset($user->foto) }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center">
                    <i class="fa-solid fa-user text-slate-500"></i>
                </div>
            @endif
        </div>

        <div class="hidden md:block text-left">
            <h4 class="text-sm font-semibold text-slate-700">{{ $user->name }}</h4>
            <p class="text-xs text-slate-400">{{ ucfirst($user->role ?? '') }}</p>
        </div>

        <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>

    </button>

    <div id="profileDropdown"
        class="hidden absolute right-0 mt-3 w-56 bg-white border border-slate-200 rounded-2xl shadow-lg overflow-hidden z-50">
        <div class="md:hidden px-5 py-3 border-b border-slate-100">
            <h4 class="text-sm font-semibold text-slate-700">{{ $user->name }}</h4>
            <p class="text-xs text-slate-400">{{ ucfirst($user->role ?? '') }}</p>
        </div>
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
