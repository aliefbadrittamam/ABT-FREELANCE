<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('sub_category_id')->nullable()->after('category_id')->constrained('sub_categories')->nullOnDelete();
            $table->foreignId('major_id')->nullable()->after('sub_category_id')->constrained('majors')->nullOnDelete();
            $table->string('major_custom')->nullable()->after('major_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['sub_category_id']);
            $table->dropForeign(['major_id']);
            $table->dropColumn(['sub_category_id', 'major_id', 'major_custom']);
        });

        Schema::dropIfExists('sub_categories');
        Schema::dropIfExists('majors');
    }
};
