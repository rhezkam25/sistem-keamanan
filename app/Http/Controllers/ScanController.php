<?php

namespace App\Http\Controllers;

use App\Models\Kunjungan;
use App\Models\Tamu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    public function index()
    {
        $kunjunganTerbaru = Kunjungan::with(['tamu', 'petugas'])
            ->whereDate('waktu_scan', today())
            ->latest('waktu_scan')
            ->limit(20)
            ->get();

        return view('scan.index', compact('kunjunganTerbaru'));
    }

    public function proses(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
        ]);

        $token = strtoupper(trim($request->qr_token));
        $tamu = Tamu::where('qr_token', $token)->first();

        if (!$tamu) {
            return back()->with('error', 'QR Code tidak valid atau tidak ditemukan.');
        }

        if (!$tamu->disetujui()) {
            return back()->with('error', 'Tamu ini belum mendapatkan persetujuan kunjungan.');
        }

        $sudahMasuk = $tamu->kunjunganMasuk()->exists();
        $sudahKeluar = $tamu->kunjunganKeluar()->exists();

        // QR hangus: sekali check-out, QR tidak dapat digunakan lagi untuk alasan apapun
        if ($sudahKeluar) {
            return back()->with([
                'qr_hangus'      => true,
                'qr_hangus_nama' => $tamu->nama,
                'error'          => "QR Code ini sudah tidak aktif. Kunjungan {$tamu->nama} telah selesai dan tidak dapat digunakan kembali.",
            ]);
        }

        // Cek batas waktu 48 jam sejak check-in
        if ($sudahMasuk && !$sudahKeluar) {
            $kunjunganMasuk = $tamu->kunjunganMasuk;
            if ($kunjunganMasuk && now()->diffInHours($kunjunganMasuk->waktu_scan) > 48) {
                return back()->with('error', "Sesi kunjungan {$tamu->nama} telah kedaluwarsa (lebih dari 48 jam sejak check-in). Hubungi admin untuk penanganan lebih lanjut.");
            }
        }

        $jenis = $sudahMasuk ? 'keluar' : 'masuk';

        Kunjungan::create([
            'tamu_id'    => $tamu->id,
            'discan_oleh' => Auth::id(),
            'jenis'      => $jenis,
            'waktu_scan' => now(),
        ]);

        $isCheckOut = $jenis === 'keluar';

        $pesan = $isCheckOut
            ? "Tamu {$tamu->nama} berhasil CHECK-OUT. QR Code telah dinonaktifkan."
            : "Tamu {$tamu->nama} berhasil CHECK-IN.";

        return back()->with([
            'scan_success'    => true,
            'scan_tamu_nama'  => $tamu->nama,
            'scan_tamu_tujuan' => $tamu->tujuan_kunjungan,
            'scan_jenis'      => $jenis,
            'scan_qr_hangus'  => $isCheckOut,
            'success'         => $pesan,
        ]);
    }
}
