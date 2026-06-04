<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'jabatan',
        'role',
        'is_active',
        'can_view_absensi',
        'pejabat_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'is_active'          => 'boolean',
            'can_view_absensi'   => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function pejabat()
    {
        return $this->belongsTo(User::class, 'pejabat_id');
    }

    public function staf()
    {
        return $this->hasMany(User::class, 'pejabat_id');
    }

    public function tamuDidaftarkan()
    {
        return $this->hasMany(Tamu::class, 'didaftarkan_oleh');
    }

    public function tamuUntukApproval()
    {
        return $this->hasMany(Tamu::class, 'pejabat_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPejabat(): bool
    {
        return $this->role === 'pejabat';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isSatpam(): bool
    {
        return $this->role === 'satpam';
    }

    public function canScanQr(): bool
    {
        return in_array($this->role, ['admin', 'satpam']);
    }

    public function canApprove(): bool
    {
        return in_array($this->role, ['admin', 'pejabat']);
    }

    public function canInputTamu(): bool
    {
        return in_array($this->role, ['admin', 'pejabat', 'staff']);
    }

    public function canViewAbsensi(): bool
    {
        return $this->isAdmin() || ($this->isPejabat() && $this->can_view_absensi);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function absensiHariIni()
    {
        return $this->hasOne(Absensi::class)->whereDate('tanggal', today());
    }
}
