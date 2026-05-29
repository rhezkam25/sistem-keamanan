<?php

namespace App\Http\Controllers;

use App\Models\Tamu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Tamu::with(['pendaftar', 'pejabat'])->where('status', 'menunggu');

        if ($user->isPejabat()) {
            $query->where('pejabat_id', $user->id);
        }

        $tamu = $query->latest()->paginate(15);

        return view('approval.index', compact('tamu'));
    }

    public function setujui(Request $request, Tamu $tamu)
    {
        $this->authorizeApproval($tamu);

        $qrToken = Tamu::generateQrToken();
        while (Tamu::where('qr_token', $qrToken)->exists()) {
            $qrToken = Tamu::generateQrToken();
        }

        $tamu->update([
            'status' => 'disetujui',
            'qr_token' => $qrToken,
            'disetujui_pada' => now(),
            'catatan_pejabat' => $request->catatan,
        ]);

        return redirect()->route('approval.index')
            ->with('success', "Tamu {$tamu->nama} berhasil disetujui. QR Code telah digenerate.");
    }

    public function tolak(Request $request, Tamu $tamu)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ]);

        $this->authorizeApproval($tamu);

        $tamu->update([
            'status' => 'ditolak',
            'catatan_pejabat' => $request->catatan,
        ]);

        return redirect()->route('approval.index')
            ->with('info', "Tamu {$tamu->nama} telah ditolak.");
    }

    private function authorizeApproval(Tamu $tamu): void
    {
        $user = Auth::user();

        if ($user->isAdmin()) return;

        if ($user->isPejabat() && $tamu->pejabat_id === $user->id) return;

        abort(403);
    }
}
