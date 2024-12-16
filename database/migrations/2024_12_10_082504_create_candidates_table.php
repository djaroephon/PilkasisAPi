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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('role', ['ketua', 'wakil']);
            $table->string('jurusan');
            $table->string('kelas');
            $table->string('foto')->nullable();  // Foto kandidat
            $table->text('visi_misi');  // Visi dan misi kandidat
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
