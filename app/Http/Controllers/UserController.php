<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        return view('user.index');
    }

    public function data(Request $request)
    {
        $users = User::query()
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%")
                ->orWhere('no_hp', 'like', "%$s%")))
            ->when($request->role, fn ($q, $role) => $q->where('role', $role))
            ->orderByDesc('aktif')->orderBy('name')
            ->paginate(12);

        return response()->json($users->toArray() + [
            'stats' => [
                'total' => User::count(),
                'owner' => User::where('role', 'owner')->count(),
                'kasir' => User::where('role', 'kasir')->count(),
                'kurir' => User::where('role', 'kurir')->count(),
                'nonaktif' => User::where('aktif', false)->count(),
            ],
        ]);
    }

    public function store(UserRequest $request)
    {
        User::create($request->validated());

        return response()->json(['message' => 'User berhasil ditambahkan.']);
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($user->is($request->user())) {
            unset($data['role']); // cegah owner menurunkan role dirinya sendiri lalu terkunci
        }

        $user->update($data);

        return response()->json(['message' => 'User berhasil diperbarui.']);
    }

    public function toggle(Request $request, User $user)
    {
        abort_if($user->is($request->user()), 422, 'Tidak bisa menonaktifkan akun sendiri.');

        $user->update(['aktif' => ! $user->aktif]);

        if (! $user->aktif && config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }

        return response()->json(['message' => $user->aktif ? 'User diaktifkan.' : 'User dinonaktifkan.']);
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->is($request->user()), 422, 'Tidak bisa menghapus akun sendiri.');

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.']);
    }
}
