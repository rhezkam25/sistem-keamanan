<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tamu', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nomor_id');
            $table->string('no_hp');
            $table->string('jenis_kendaraan')->nullable();
            $table->string('plat_kendaraan')->nullable();
            $table->string('tujuan_kunjungan');
            $table->foreignId('didaftarkan_oleh')->constrained('users');
            $table->foreignId('pejabat_id')->constrained('users');
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan_pejabat')->nullable();
            $table->string('qr_token')->unique()->nullable();
            $table->timestamp('disetujui_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tamu');
    }
};
