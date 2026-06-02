<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal')->index();

            // Absen masuk
            $table->timestamp('waktu_masuk')->nullable();
            $table->decimal('latitude_masuk', 10, 7)->nullable();
            $table->decimal('longitude_masuk', 10, 7)->nullable();
            $table->float('akurasi_masuk')->nullable();
            $table->string('foto_masuk')->nullable();

            // Absen keluar
            $table->timestamp('waktu_keluar')->nullable();
            $table->decimal('latitude_keluar', 10, 7)->nullable();
            $table->decimal('longitude_keluar', 10, 7)->nullable();
            $table->float('akurasi_keluar')->nullable();
            $table->string('foto_keluar')->nullable();

            $table->integer('durasi_menit')->nullable();
            $table->enum('status', ['hadir', 'belum_keluar', 'tidak_hadir'])->default('belum_keluar');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
