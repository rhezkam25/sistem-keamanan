<?php

namespace App\Http\Controllers;

use App\Models\Kunjungan;
use App\Models\Tamu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = match ($user->role) {
            'admin' => $this->statsAdmin(),
            'pejabat' => $this->statsPejabat($user),
            'staff' => $this->statsStaff($user),
            'satpam' => $this->statsSatpam(),
            default => [],
        };

        $kunjunganHariIni = Kunjungan::whereDate('waktu_scan', today())
            ->with(['tamu', 'petugas'])
            ->latest('waktu_scan')
            ->limit(10)
            ->get();

        return view('dashboard', compact('stats', 'kunjunganHariIni'));
    }

    private function statsAdmin(): array
    {
        return [
            'total_tamu' => Tamu::count(),
            'menunggu' => Tamu::where('status', 'menunggu')->count(),
            'disetujui' => Tamu::where('status', 'disetujui')->count(),
            'kunjungan_hari_ini' => Kunjungan::whereDate('waktu_scan', today())->where('jenis', 'masuk')->count(),
            'total_user' => User::count(),
        ];
    }

    private function statsPejabat(User $user): array
    {
        return [
            'total_tamu' => Tamu::where('pejabat_id', $user->id)->count(),
            'menunggu' => Tamu::where('pejabat_id', $user->id)->where('status', 'menunggu')->count(),
            'disetujui' => Tamu::where('pejabat_id', $user->id)->where('status', 'disetujui')->count(),
            'total_staff' => $user->staf()->count(),
        ];
    }

    private function statsStaff(User $user): array
    {
        return [
            'total_tamu' => Tamu::where('didaftarkan_oleh', $user->id)->count(),
            'menunggu' => Tamu::where('didaftarkan_oleh', $user->id)->where('status', 'menunggu')->count(),
            'disetujui' => Tamu::where('didaftarkan_oleh', $user->id)->where('status', 'disetujui')->count(),
            'ditolak' => Tamu::where('didaftarkan_oleh', $user->id)->where('status', 'ditolak')->count(),
        ];
    }

    private function statsSatpam(): array
    {
        return [
            'kunjungan_hari_ini' => Kunjungan::whereDate('waktu_scan', today())->where('jenis', 'masuk')->count(),
            'tamu_masuk' => Kunjungan::whereDate('waktu_scan', today())->where('jenis', 'masuk')->count(),
            'tamu_keluar' => Kunjungan::whereDate('waktu_scan', today())->where('jenis', 'keluar')->count(),
            'tamu_disetujui' => Tamu::where('status', 'disetujui')->count(),
        ];
    }

    public function laporan(Request $request)
    {
        $query = Tamu::with(['pendaftar', 'pejabat', 'kunjungan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }

        $tamu = $query->latest()->paginate(20);

        return view('laporan.index', compact('tamu'));
    }
}
