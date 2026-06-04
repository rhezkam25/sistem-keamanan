<?php

namespace App\Http\Controllers;

use App\Exports\AbsensiExport;
use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiAdminController extends Controller
{
    private function checkAccess(): void
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->canViewAbsensi()) {
            abort(403, 'Anda tidak memiliki akses ke data absensi.');
        }
    }

    public function index(Request $request)
    {
        $this->checkAccess();

        $dari    = $request->input('dari');
        $sampai  = $request->input('sampai');
        $userId  = $request->input('user_id');

        $satpamList = User::where('role', 'satpam')->orderBy('name')->get();

        $absensi = Absensi::with('user')
            ->whereHas('user', fn($q) => $q->where('role', 'satpam'))
            ->when($userId,  fn($q) => $q->where('user_id', $userId))
            ->when($dari,    fn($q) => $q->whereDate('tanggal', '>=', $dari))
            ->when($sampai,  fn($q) => $q->whereDate('tanggal', '<=', $sampai))
            ->orderByDesc('tanggal')
            ->paginate(20)
            ->withQueryString();

        return view('absensi.admin.index', compact('absensi', 'satpamList', 'dari', 'sampai', 'userId'));
    }

    public function show(User $user)
    {
        $this->checkAccess();
        abort_if($user->role !== 'satpam', 404);

        $bulan = max(1, min(12, request()->integer('bulan', now()->month)));
        $tahun = max(2000, min(now()->year + 1, request()->integer('tahun', now()->year)));

        $absensi = Absensi::where('user_id', $user->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderByDesc('tanggal')
            ->paginate(31)
            ->withQueryString();

        $totalHadir = Absensi::where('user_id', $user->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->where('status', 'hadir')
            ->count();

        $rataRataMenit = Absensi::where('user_id', $user->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->where('status', 'hadir')
            ->avg('durasi_menit');

        return view('absensi.admin.show', compact('user', 'absensi', 'bulan', 'tahun', 'totalHadir', 'rataRataMenit'));
    }

    public function export(Request $request)
    {
        $this->checkAccess();

        $request->validate([
            'format'  => 'required|in:xlsx,csv',
            'dari'    => 'nullable|date',
            'sampai'  => 'nullable|date|after_or_equal:dari',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $export   = new AbsensiExport($request->dari, $request->sampai, $request->user_id);
        $filename = 'absensi_' . now()->format('Ymd_His');

        if ($request->format === 'csv') {
            return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, $filename . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
