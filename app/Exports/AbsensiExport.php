<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class AbsensiExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(
        private ?string $dari = null,
        private ?string $sampai = null,
        private ?int $userId = null,
    ) {}

    public function query(): Builder
    {
        return Absensi::with('user')
            ->when($this->userId, fn($q) => $q->where('user_id', $this->userId))
            ->when($this->dari,   fn($q) => $q->whereDate('tanggal', '>=', $this->dari))
            ->when($this->sampai, fn($q) => $q->whereDate('tanggal', '<=', $this->sampai))
            ->orderBy('tanggal')
            ->orderBy('user_id');
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NIP',
            'Tanggal',
            'Jam Masuk',
            'Jam Keluar',
            'Durasi',
            'Status',
            'Lat Masuk',
            'Lng Masuk',
            'Akurasi Masuk (m)',
            'Lat Keluar',
            'Lng Keluar',
            'Akurasi Keluar (m)',
        ];
    }

    public function map($row): array
    {
        return [
            $row->user?->name ?? '-',
            $row->user?->nip  ?? '-',
            $row->tanggal?->format('d/m/Y') ?? '-',
            $row->waktu_masuk  ? $row->waktu_masuk->format('H:i:s')  : '-',
            $row->waktu_keluar ? $row->waktu_keluar->format('H:i:s') : '-',
            $row->durasiFormatted(),
            match ($row->status) {
                'hadir'        => 'Hadir',
                'belum_keluar' => 'Belum Keluar',
                'tidak_hadir'  => 'Tidak Hadir',
                default        => $row->status,
            },
            $row->latitude_masuk,
            $row->longitude_masuk,
            $row->akurasi_masuk  ? round($row->akurasi_masuk)  : '-',
            $row->latitude_keluar,
            $row->longitude_keluar,
            $row->akurasi_keluar ? round($row->akurasi_keluar) : '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
