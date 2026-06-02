<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('pengaturan');
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->string('kantor_nama')->default('Kantor');
            $table->decimal('kantor_lat', 10, 7)->nullable();
            $table->decimal('kantor_lng', 10, 7)->nullable();
            $table->integer('radius_meter')->default(200);
            $table->integer('jam_kerja_minimum')->default(12);
            $table->timestamps();
        });

        // Seed satu baris default
        DB::table('pengaturan')->insert([
            'kantor_nama'       => 'KJRI Penang',
            'kantor_lat'        => null,
            'kantor_lng'        => null,
            'radius_meter'      => 200,
            'jam_kerja_minimum' => 12,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
