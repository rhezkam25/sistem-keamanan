<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    protected $table = 'kunjungan';

    protected $fillable = [
        'tamu_id',
        'discan_oleh',
        'jenis',
        'waktu_scan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'waktu_scan' => 'datetime',
        ];
    }

    public function tamu()
    {
        return $this->belongsTo(Tamu::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'discan_oleh');
    }
}
