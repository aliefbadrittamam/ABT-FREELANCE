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
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('major')->nullable()->after('testimonial_number'); // Jurusan / Program Studi
            $table->string('task_title')->nullable()->after('major'); // Judul Tugas / Mata Kuliah (misal: UAS 2 dan 3, Tugas Akhir)
            $table->string('deliverables')->nullable()->after('task_title'); // Output/hasil (misal: Makalah, Jurnal, Proposal kegiatan dan PPT)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn(['major', 'task_title', 'deliverables']);
        });
    }
};
