<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tamu extends Model
{
    protected $table = 'tamu';

    protected $fillable = [
        'nama',
        'nomor_id',
        'no_hp',
        'jenis_kendaraan',
        'plat_kendaraan',
        'tujuan_kunjungan',
        'didaftarkan_oleh',
        'pejabat_id',
        'status',
        'catatan_pejabat',
        'qr_token',
        'disetujui_pada',
    ];

    protected function casts(): array
    {
        return [
            'disetujui_pada' => 'datetime',
        ];
    }

    public function pendaftar()
    {
        return $this->belongsTo(User::class, 'didaftarkan_oleh');
    }

    public function pejabat()
    {
        return $this->belongsTo(User::class, 'pejabat_id');
    }

    public function kunjungan()
    {
        return $this->hasMany(Kunjungan::class);
    }

    public function kunjunganMasuk()
    {
        return $this->hasOne(Kunjungan::class)->where('jenis', 'masuk')->latestOfMany();
    }

    public function kunjunganKeluar()
    {
        return $this->hasOne(Kunjungan::class)->where('jenis', 'keluar')->latestOfMany();
    }

    public function menunggu(): bool
    {
        return $this->status === 'menunggu';
    }

    public function disetujui(): bool
    {
        return $this->status === 'disetujui';
    }

    public function ditolak(): bool
    {
        return $this->status === 'ditolak';
    }

    public function sudahMasuk(): bool
    {
        return $this->kunjunganMasuk()->exists();
    }

    public function sudahKeluar(): bool
    {
        return $this->kunjunganKeluar()->exists();
    }

    public static function generateQrToken(): string
    {
        return strtoupper(Str::random(8));
    }
}
