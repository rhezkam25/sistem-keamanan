<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordRequestController extends Controller
{
    public function index(): View
    {
        $requests = PasswordResetRequest::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.password-requests.index', compact('requests'));
    }

    public function selesai(PasswordResetRequest $request): RedirectResponse
    {
        $request->update(['status' => 'selesai']);

        return back()->with('success', "Request password untuk {$request->user->name} ditandai selesai.");
    }
}
