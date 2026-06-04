<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'user_id',
        'tanggal',
        'waktu_masuk',
        'latitude_masuk',
        'longitude_masuk',
        'akurasi_masuk',
        'foto_masuk',
        'waktu_keluar',
        'latitude_keluar',
        'longitude_keluar',
        'akurasi_keluar',
        'foto_keluar',
        'durasi_menit',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'          => 'date',
            'waktu_masuk'      => 'datetime',
            'waktu_keluar'     => 'datetime',
            'latitude_masuk'   => 'float',
            'longitude_masuk'  => 'float',
            'latitude_keluar'  => 'float',
            'longitude_keluar' => 'float',
            'akurasi_masuk'    => 'float',
            'akurasi_keluar'   => 'float',
            'durasi_menit'     => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeHariIni(Builder $query): Builder
    {
        return $query->whereDate('tanggal', today());
    }

    public function scopeBulanIni(Builder $query): Builder
    {
        return $query->whereYear('tanggal', now()->year)
                     ->whereMonth('tanggal', now()->month);
    }

    public function durasiFormatted(): string
    {
        if ($this->durasi_menit === null || $this->durasi_menit < 0) {
            return '-';
        }
        $jam   = intdiv($this->durasi_menit, 60);
        $menit = $this->durasi_menit % 60;
        return "{$jam} jam {$menit} menit";
    }

    public function sudahCukupKerja(int $minimumJam = 12): bool
    {
        if (!$this->waktu_masuk) {
            return false;
        }
        return $this->waktu_masuk->diffInMinutes(now()) >= ($minimumJam * 60);
    }

    public function sisaMenitKerja(int $minimumJam = 12): int
    {
        if (!$this->waktu_masuk) {
            return $minimumJam * 60;
        }
        $sudahMenit = $this->waktu_masuk->diffInMinutes(now());
        return max(0, ($minimumJam * 60) - (int) $sudahMenit);
    }
}
