<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    public function index()
    {
        $user       = auth()->user();
        $pengaturan = Pengaturan::aktif();
        $absensiHariIni = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->first();

        return view('absensi.index', compact('pengaturan', 'absensiHariIni'));
    }

    public function masuk(Request $request)
    {
        $pengaturan = Pengaturan::aktif();

        if (!$pengaturan->sudahDikonfigurasi()) {
            return back()->withErrors(['lokasi' => 'Titik lokasi kantor belum dikonfigurasi oleh admin.']);
        }

        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'akurasi'   => 'required|numeric|min:0',
            'foto'      => 'nullable|string',
        ]);

        $user = auth()->user();

        // Cek apakah sudah absen masuk hari ini
        $sudahAbsen = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->exists();

        if ($sudahAbsen) {
            return back()->withErrors(['absen' => 'Anda sudah melakukan absen masuk hari ini.']);
        }

        // Validasi jarak di server
        $jarak = $this->hitungJarak(
            $pengaturan->kantor_lat, $pengaturan->kantor_lng,
            $request->latitude, $request->longitude
        );

        if ($jarak > $pengaturan->radius_meter) {
            return back()->withErrors([
                'lokasi' => "Anda berada {$jarak} meter dari kantor. Absensi hanya dapat dilakukan dalam radius {$pengaturan->radius_meter} meter.",
            ]);
        }

        // Simpan foto selfie jika ada
        $fotoPath = null;
        if ($request->filled('foto')) {
            $fotoPath = $this->simpanFotoBase64($request->foto, 'absensi/' . $user->id);
        }

        Absensi::create([
            'user_id'         => $user->id,
            'tanggal'         => today(),
            'waktu_masuk'     => now(),
            'latitude_masuk'  => $request->latitude,
            'longitude_masuk' => $request->longitude,
            'akurasi_masuk'   => $request->akurasi,
            'foto_masuk'      => $fotoPath,
            'status'          => 'belum_keluar',
        ]);

        return back()->with('success', 'Absen masuk berhasil dicatat pada ' . now()->format('H:i') . '.');
    }

    public function keluar(Request $request)
    {
        $pengaturan = Pengaturan::aktif();

        if (!$pengaturan->sudahDikonfigurasi()) {
            return back()->withErrors(['lokasi' => 'Titik lokasi kantor belum dikonfigurasi oleh admin.']);
        }

        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'akurasi'   => 'required|numeric|min:0',
            'foto'      => 'nullable|string',
        ]);

        $user = auth()->user();

        $absensi = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->whereNotNull('waktu_masuk')
            ->whereNull('waktu_keluar')
            ->first();

        if (!$absensi) {
            return back()->withErrors(['absen' => 'Anda belum melakukan absen masuk hari ini.']);
        }

        // Validasi minimum jam kerja
        if (!$absensi->sudahCukupKerja($pengaturan->jam_kerja_minimum)) {
            $sisaMenit = $absensi->sisaMenitKerja($pengaturan->jam_kerja_minimum);
            $sisaJam   = intdiv($sisaMenit, 60);
            $sisaSisa  = $sisaMenit % 60;
            return back()->withErrors([
                'waktu' => "Belum memenuhi {$pengaturan->jam_kerja_minimum} jam kerja. Sisa waktu: {$sisaJam} jam {$sisaSisa} menit.",
            ]);
        }

        // Validasi jarak di server
        $jarak = $this->hitungJarak(
            $pengaturan->kantor_lat, $pengaturan->kantor_lng,
            $request->latitude, $request->longitude
        );

        if ($jarak > $pengaturan->radius_meter) {
            return back()->withErrors([
                'lokasi' => "Anda berada {$jarak} meter dari kantor. Absensi hanya dapat dilakukan dalam radius {$pengaturan->radius_meter} meter.",
            ]);
        }

        $fotoPath = null;
        if ($request->filled('foto')) {
            $fotoPath = $this->simpanFotoBase64($request->foto, 'absensi/' . $user->id);
        }

        $durasiMenit = now()->diffInMinutes($absensi->waktu_masuk);

        $absensi->update([
            'waktu_keluar'     => now(),
            'latitude_keluar'  => $request->latitude,
            'longitude_keluar' => $request->longitude,
            'akurasi_keluar'   => $request->akurasi,
            'foto_keluar'      => $fotoPath,
            'durasi_menit'     => $durasiMenit,
            'status'           => 'hadir',
        ]);

        return back()->with('success', 'Absen keluar berhasil dicatat pada ' . now()->format('H:i') . '. Durasi kerja: ' . $absensi->fresh()->durasiFormatted() . '.');
    }

    public function riwayat(Request $request)
    {
        $user  = auth()->user();
        $bulan = $request->integer('bulan', now()->month);
        $tahun = $request->integer('tahun', now()->year);

        $absensi = Absensi::where('user_id', $user->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderByDesc('tanggal')
            ->paginate(20);

        return view('absensi.riwayat', compact('absensi', 'bulan', 'tahun'));
    }

    private function hitungJarak(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return round($R * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function simpanFotoBase64(string $base64, string $folder): ?string
    {
        $allowed = ['jpeg', 'jpg', 'png', 'gif', 'webp'];

        if (!preg_match('/^data:image\/([a-zA-Z]+);base64,/', $base64, $matches)) {
            return null;
        }

        $ext = strtolower($matches[1]);
        if (!in_array($ext, $allowed, true)) {
            return null;
        }

        $data = base64_decode(substr($base64, strpos($base64, ',') + 1), strict: true);
        if ($data === false || strlen($data) > 2 * 1024 * 1024) {
            return null;
        }

        // Validate actual image content via magic bytes
        if (@getimagesizefromstring($data) === false) {
            return null;
        }

        $safeExt  = $ext === 'jpeg' ? 'jpg' : $ext;
        $filename = $folder . '/' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $safeExt;
        Storage::disk('public')->put($filename, $data);
        return $filename;
    }
}
