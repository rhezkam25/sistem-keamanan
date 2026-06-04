<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('pejabat')->latest()->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $pejabatList = User::active()->where('role', 'pejabat')->get();
        return view('users.create', compact('pejabatList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50|unique:users',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'jabatan' => 'nullable|string|max:100',
            'role' => 'required|in:admin,pejabat,staff,satpam',
            'pejabat_id' => 'required_if:role,staff|nullable|exists:users,id',
            'password' => ['required', Rules\Password::defaults()],
        ]);

        User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'pejabat_id' => $validated['role'] === 'staff' ? $validated['pejabat_id'] : null,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        $user->load('pejabat', 'staf');
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $pejabatList = User::active()->where('role', 'pejabat')->get();
        return view('users.edit', compact('user', 'pejabatList'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50|unique:users,nip,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'jabatan' => 'nullable|string|max:100',
            'role' => 'required|in:admin,pejabat,staff,satpam',
            'pejabat_id' => 'required_if:role,staff|nullable|exists:users,id',
            'password' => ['nullable', Rules\Password::defaults()],
        ]);

        $data = collect($validated)
            ->except('password')
            ->merge([
                'pejabat_id' => $validated['role'] === 'staff' ? $validated['pejabat_id'] : null,
            ])
            ->toArray();

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        abort(403, 'Penghapusan akun tidak diizinkan.');
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }

    public function toggleAbsensiAccess(User $user)
    {
        abort_if(!$user->isPejabat(), 403, 'Akses absensi hanya dapat diberikan ke pejabat.');

        $user->update(['can_view_absensi' => !$user->can_view_absensi]);
        $status = $user->can_view_absensi ? 'diberikan' : 'dicabut';

        return back()->with('success', "Akses data absensi untuk {$user->name} berhasil {$status}.");
    }
}
