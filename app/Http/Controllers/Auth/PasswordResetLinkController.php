<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)
                    ->orWhere('username', $request->email)
                    ->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Akun dengan email atau username tersebut tidak ditemukan.']);
        }

        PasswordResetRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->delete();

        PasswordResetRequest::create(['user_id' => $user->id]);

        return back()->with('status', 'Permintaan reset password berhasil dikirim. Admin akan segera mengganti password Anda.');
    }
}
