<?php

namespace App\Http\Controllers;

use App\Models\Kunjungan;
use App\Models\Tamu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ScanController extends Controller
{
    public function index()
    {
        $kunjunganTerbaru = Kunjungan::with(['tamu', 'petugas'])
            ->whereDate('waktu_scan', today())
            ->latest('waktu_scan')
            ->limit(20)
            ->get();

        $tamuBelumKeluar = Tamu::with(['pejabat'])
            ->where('status', 'disetujui')
            ->whereHas('kunjungan', fn($q) => $q->where('jenis', 'masuk'))
            ->whereDoesntHave('kunjungan', fn($q) => $q->where('jenis', 'keluar'))
            ->get()
            ->map(function ($tamu) {
                $masuk = $tamu->kunjungan()->where('jenis', 'masuk')->latest('waktu_scan')->first();
                $tamu->waktu_masuk_dt = $masuk?->waktu_scan;
                $tamu->durasi_jam     = $masuk ? (int) $masuk->waktu_scan->diffInHours(now()) : 0;
                return $tamu;
            })
            ->sortByDesc('durasi_jam')
            ->values();

        return view('scan.index', compact('kunjunganTerbaru', 'tamuBelumKeluar'));
    }

    public function proses(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
        ]);

        $token = strtoupper(trim($request->qr_token));

        if (!preg_match('/^[A-Z0-9]{8}$/', $token)) {
            Log::warning('scan.format_invalid', [
                'user_id' => Auth::id(),
                'ip'      => $request->ip(),
                'token'   => $token,
            ]);
            return back()->withInput()->with('scan_error',
                'Format kode QR tidak valid. Kode harus terdiri dari tepat 8 karakter huruf (A–Z) dan angka (0–9). ' .
                'Pastikan QR Code yang dipindai berasal dari sistem ini.'
            );
        }

        $tamu = Tamu::where('qr_token', $token)->first();

        if (!$tamu) {
            Log::warning('scan.token_not_found', [
                'user_id' => Auth::id(),
                'ip'      => $request->ip(),
                'token'   => $token,
            ]);
            return back()->withInput()->with('scan_error', 'QR Code tidak valid atau tidak ditemukan.');
        }

        if (!$tamu->disetujui()) {
            Log::warning('scan.token_not_approved', [
                'user_id' => Auth::id(),
                'ip'      => $request->ip(),
                'tamu_id' => $tamu->id,
            ]);
            return back()->withInput()->with('scan_error', 'Tamu ini belum mendapatkan persetujuan kunjungan.');
        }

        // Gunakan hasMany biasa agar exists() bekerja dengan benar
        $sudahMasuk  = $tamu->kunjungan()->where('jenis', 'masuk')->exists();
        $sudahKeluar = $tamu->kunjungan()->where('jenis', 'keluar')->exists();

        // QR hangus: sudah check-out tidak bisa digunakan lagi
        if ($sudahKeluar) {
            Log::info('scan.qr_expired', [
                'user_id' => Auth::id(),
                'ip'      => $request->ip(),
                'tamu_id' => $tamu->id,
            ]);
            return back()->with([
                'qr_hangus'      => true,
                'qr_hangus_nama' => $tamu->nama,
                'scan_error'     => "QR Code ini sudah tidak aktif. Kunjungan {$tamu->nama} telah selesai dan tidak dapat digunakan kembali.",
            ]);
        }

        // Cek batas waktu 48 jam sejak check-in
        if ($sudahMasuk) {
            $kunjunganMasuk = $tamu->kunjungan()->where('jenis', 'masuk')->latest('waktu_scan')->first();
            if ($kunjunganMasuk && $kunjunganMasuk->waktu_scan->diffInHours(now()) > 48) {
                Log::warning('scan.session_expired', [
                    'user_id' => Auth::id(),
                    'ip'      => $request->ip(),
                    'tamu_id' => $tamu->id,
                ]);
                return back()->withInput()->with('scan_error', "Sesi kunjungan {$tamu->nama} telah kedaluwarsa (lebih dari 48 jam sejak check-in). Hubungi admin untuk penanganan lebih lanjut.");
            }
        }

        $jenis = $sudahMasuk ? 'keluar' : 'masuk';

        Kunjungan::create([
            'tamu_id'     => $tamu->id,
            'discan_oleh' => Auth::id(),
            'jenis'       => $jenis,
            'waktu_scan'  => now(),
        ]);

        Log::info('scan.success', [
            'user_id' => Auth::id(),
            'ip'      => $request->ip(),
            'tamu_id' => $tamu->id,
            'jenis'   => $jenis,
        ]);

        $isCheckOut = $jenis === 'keluar';

        $pesan = $isCheckOut
            ? "Tamu {$tamu->nama} berhasil CHECK-OUT. QR Code telah dinonaktifkan."
            : "Tamu {$tamu->nama} berhasil CHECK-IN.";

        return back()->with([
            'scan_success'     => true,
            'scan_tamu_nama'   => $tamu->nama,
            'scan_tamu_tujuan' => $tamu->tujuan_kunjungan,
            'scan_jenis'       => $jenis,
            'scan_qr_hangus'   => $isCheckOut,
            'success'          => $pesan,
        ]);
    }

    public function manualCheckout(Request $request, Tamu $tamu)
    {
        if (!$tamu->disetujui()) {
            return back()->with('error', 'Tamu ini tidak memiliki status disetujui.');
        }

        $sudahMasuk  = $tamu->kunjungan()->where('jenis', 'masuk')->exists();
        $sudahKeluar = $tamu->kunjungan()->where('jenis', 'keluar')->exists();

        if (!$sudahMasuk) {
            return back()->with('error', "Tamu {$tamu->nama} belum melakukan check-in.");
        }

        if ($sudahKeluar) {
            return back()->with('error', "Tamu {$tamu->nama} sudah melakukan check-out sebelumnya.");
        }

        Kunjungan::create([
            'tamu_id'     => $tamu->id,
            'discan_oleh' => Auth::id(),
            'jenis'       => 'keluar',
            'waktu_scan'  => now(),
            'catatan'     => 'Checkout manual oleh petugas',
        ]);

        return back()->with('success', "Tamu {$tamu->nama} berhasil di-checkout secara manual.");
    }
}
