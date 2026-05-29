<?php

namespace App\Http\Controllers;

use App\Models\Tamu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TamuController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Tamu::with(['pendaftar', 'pejabat']);

        if ($user->isStaff()) {
            $query->where('didaftarkan_oleh', $user->id);
        } elseif ($user->isPejabat()) {
            $query->where(function ($q) use ($user) {
                $q->where('pejabat_id', $user->id)
                  ->orWhere('didaftarkan_oleh', $user->id);
            });
        }

        $tamu = $query->latest()->paginate(15);

        return view('tamu.index', compact('tamu'));
    }

    public function create()
    {
        $user = Auth::user();
        $pejabatList = collect();

        if ($user->isAdmin()) {
            $pejabatList = User::where('role', 'pejabat')->get();
        } elseif ($user->isPejabat()) {
            $pejabatList = collect([$user]);
        } elseif ($user->isStaff() && $user->pejabat_id) {
            $pejabatList = collect([$user->pejabat]);
        }

        return view('tamu.create', compact('pejabatList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nomor_id' => 'required|string|max:50',
            'no_hp' => 'required|string|max:20',
            'jenis_kendaraan' => 'nullable|string|max:50',
            'plat_kendaraan' => 'nullable|string|max:20',
            'tujuan_kunjungan' => 'required|string|max:500',
            'pejabat_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();

        Tamu::create([
            ...$validated,
            'didaftarkan_oleh' => $user->id,
            'status' => 'menunggu',
        ]);

        return redirect()->route('tamu.index')
            ->with('success', 'Data tamu berhasil didaftarkan dan menunggu persetujuan.');
    }

    public function show(Tamu $tamu)
    {
        $this->authorizeView($tamu);
        $tamu->load(['pendaftar', 'pejabat', 'kunjungan.petugas']);

        return view('tamu.show', compact('tamu'));
    }

    public function edit(Tamu $tamu)
    {
        $this->authorizeView($tamu);

        if ($tamu->status !== 'menunggu') {
            return redirect()->route('tamu.show', $tamu)
                ->with('error', 'Data tamu yang sudah diproses tidak dapat diedit.');
        }

        $user = Auth::user();
        $pejabatList = $user->isAdmin()
            ? User::where('role', 'pejabat')->get()
            : collect([$tamu->pejabat]);

        return view('tamu.edit', compact('tamu', 'pejabatList'));
    }

    public function update(Request $request, Tamu $tamu)
    {
        $this->authorizeView($tamu);

        if ($tamu->status !== 'menunggu') {
            return redirect()->route('tamu.show', $tamu)
                ->with('error', 'Data tamu yang sudah diproses tidak dapat diedit.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nomor_id' => 'required|string|max:50',
            'no_hp' => 'required|string|max:20',
            'jenis_kendaraan' => 'nullable|string|max:50',
            'plat_kendaraan' => 'nullable|string|max:20',
            'tujuan_kunjungan' => 'required|string|max:500',
            'pejabat_id' => 'required|exists:users,id',
        ]);

        $tamu->update($validated);

        return redirect()->route('tamu.show', $tamu)
            ->with('success', 'Data tamu berhasil diperbarui.');
    }

    public function destroy(Tamu $tamu)
    {
        $this->authorizeView($tamu);

        if ($tamu->status === 'disetujui') {
            return redirect()->route('tamu.index')
                ->with('error', 'Tamu yang sudah disetujui tidak dapat dihapus.');
        }

        $tamu->delete();

        return redirect()->route('tamu.index')
            ->with('success', 'Data tamu berhasil dihapus.');
    }

    public function showQr(Tamu $tamu)
    {
        $this->authorizeView($tamu);

        if (!$tamu->disetujui() || !$tamu->qr_token) {
            return redirect()->route('tamu.show', $tamu)
                ->with('error', 'QR Code hanya tersedia untuk tamu yang sudah disetujui.');
        }

        $qrCode = QrCode::size(300)->generate($tamu->qr_token);

        return view('tamu.qr', compact('tamu', 'qrCode'));
    }

    private function authorizeView(Tamu $tamu): void
    {
        $user = Auth::user();

        if ($user->isAdmin()) return;

        if ($user->isPejabat() && $tamu->pejabat_id === $user->id) return;

        if ($tamu->didaftarkan_oleh === $user->id) return;

        abort(403);
    }
}
