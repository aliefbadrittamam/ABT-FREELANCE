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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('title');
            $table->string('client_name');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->text('description');
            $table->date('deadline');
            $table->enum('payment_type', ['dp', 'full']);
            $table->decimal('dp_amount', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['unpaid', 'dp_paid', 'paid'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
