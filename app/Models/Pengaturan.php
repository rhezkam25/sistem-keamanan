<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $fillable = [
        'kantor_nama',
        'kantor_lat',
        'kantor_lng',
        'radius_meter',
        'jam_kerja_minimum',
    ];

    protected function casts(): array
    {
        return [
            'kantor_lat'        => 'float',
            'kantor_lng'        => 'float',
            'radius_meter'      => 'integer',
            'jam_kerja_minimum' => 'integer',
        ];
    }

    public static function aktif(): self
    {
        return static::firstOrCreate([], [
            'kantor_nama'       => 'Kantor',
            'kantor_lat'        => null,
            'kantor_lng'        => null,
            'radius_meter'      => 200,
            'jam_kerja_minimum' => 12,
        ]);
    }

    public function sudahDikonfigurasi(): bool
    {
        return $this->kantor_lat !== null && $this->kantor_lng !== null;
    }
}
