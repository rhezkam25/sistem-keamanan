<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    public function index()
    {
        $pengaturan = Pengaturan::aktif();
        return view('pengaturan.index', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'kantor_nama'       => 'required|string|max:255',
            'kantor_lat'        => 'required|numeric|between:-90,90',
            'kantor_lng'        => 'required|numeric|between:-180,180',
            'radius_meter'      => 'required|integer|min:50|max:5000',
            'jam_kerja_minimum' => 'required|integer|min:1|max:24',
        ], [
            'kantor_lat.required'        => 'Titik lokasi kantor harus ditentukan. Klik pada peta untuk memilih lokasi.',
            'kantor_lng.required'        => 'Titik lokasi kantor harus ditentukan. Klik pada peta untuk memilih lokasi.',
            'kantor_lat.between'         => 'Koordinat latitude tidak valid.',
            'kantor_lng.between'         => 'Koordinat longitude tidak valid.',
            'radius_meter.min'           => 'Radius minimal 50 meter.',
            'radius_meter.max'           => 'Radius maksimal 5000 meter.',
            'jam_kerja_minimum.min'      => 'Jam kerja minimum minimal 1 jam.',
            'jam_kerja_minimum.max'      => 'Jam kerja minimum maksimal 24 jam.',
        ]);

        $pengaturan = Pengaturan::aktif();
        $pengaturan->update($validated);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
