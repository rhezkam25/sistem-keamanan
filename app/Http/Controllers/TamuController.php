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

        $query = Tamu::with(['pendaftar', 'pejabat'])->select('tamu.*');

        if ($user->isStaff()) {
            $query->where('tamu.didaftarkan_oleh', $user->id);
        } elseif ($user->isPejabat()) {
            $query->where(function ($q) use ($user) {
                $q->where('tamu.pejabat_id', $user->id)
                  ->orWhere('tamu.didaftarkan_oleh', $user->id);
            });
        }

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('tamu.nama', 'like', "%{$search}%")
                  ->orWhere('tamu.nomor_id', 'like', "%{$search}%");
            });
        }

        if ($status = request('status')) {
            $query->where('tamu.status', $status);
        }

        $dariTanggal   = request('dari_tanggal');
        $sampaiTanggal = request('sampai_tanggal');

        if ($dariTanggal && $sampaiTanggal) {
            $dari   = \Carbon\Carbon::parse($dariTanggal)->startOfDay();
            $sampai = \Carbon\Carbon::parse($sampaiTanggal)->endOfDay();

            if ($dari->diffInDays($sampai) > 90) {
                $sampai        = $dari->copy()->addDays(90)->endOfDay();
                $sampaiTanggal = $sampai->format('Y-m-d');
            }

            $query->whereBetween('tamu.created_at', [$dari, $sampai]);
        } elseif ($dariTanggal) {
            $query->whereDate('tamu.created_at', '>=', $dariTanggal);
        } elseif ($sampaiTanggal) {
            $query->whereDate('tamu.created_at', '<=', $sampaiTanggal);
        }

        $sort      = in_array(request('sort'), ['nama', 'nomor_id', 'created_at', 'pejabat']) ? request('sort') : 'created_at';
        $direction = request('direction') === 'asc' ? 'asc' : 'desc';
        $perPage   = in_array((int) request('per_page'), [10, 20, 50]) ? (int) request('per_page') : 20;

        if ($sort === 'pejabat') {
            $query->join('users as pejabat_user', 'tamu.pejabat_id', '=', 'pejabat_user.id')
                  ->orderBy('pejabat_user.name', $direction);
        } else {
            $query->orderBy("tamu.{$sort}", $direction);
        }

        $tamu = $query->paginate($perPage)->withQueryString();

        return view('tamu.index', compact('tamu', 'sort', 'direction', 'perPage'));
    }

    public function create()
    {
        $user = Auth::user();
        $pejabatList = collect();

        if ($user->isAdmin()) {
            $pejabatList = User::active()->where('role', 'pejabat')->get();
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
            ? User::active()->where('role', 'pejabat')->get()
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
