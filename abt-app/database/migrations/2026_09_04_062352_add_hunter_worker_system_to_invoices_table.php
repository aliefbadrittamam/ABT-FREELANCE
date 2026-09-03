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
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('has_worker')->default(false)->after('status');
            // Role Anda dalam proyek ini:
            // 'hunter': Anda yang mencari order (Admin dapat 20% komisi, Worker dapat 80%)
            // 'worker': Anda yang mengerjakan tugas (Admin dapat 80% fee pengerjaan, Hunter luar dapat 20%)
            $table->enum('my_role', ['none', 'hunter', 'worker'])->default('none')->after('has_worker');
            
            // Alur dana:
            // 'client_to_me': Klien transfer ke rekening Anda dulu, lalu Anda transfer bagian partner
            // 'client_to_partner': Klien transfer ke partner luar dulu, lalu partner setor bagian Anda
            $table->enum('payment_flow', ['client_to_me', 'client_to_partner'])->default('client_to_me')->after('my_role');
            
            $table->string('partner_name')->nullable()->after('payment_flow'); // Nama Worker luar atau Hunter luar
            $table->string('partner_phone')->nullable()->after('partner_name'); // No WA partner
            
            $table->decimal('worker_percentage', 5, 2)->default(80.00)->after('partner_phone');
            $table->decimal('hunter_percentage', 5, 2)->default(20.00)->after('worker_percentage');
            
            $table->decimal('my_share_amount', 12, 2)->nullable()->after('hunter_percentage'); // Hak uang masuk untuk Anda (Rp)
            $table->decimal('partner_share_amount', 12, 2)->nullable()->after('my_share_amount'); // Hak uang untuk partner luar (Rp)
            
            $table->enum('payout_status', ['unpaid', 'paid'])->default('unpaid')->after('partner_share_amount'); // Status transfer bagi hasil
            $table->timestamp('payout_at')->nullable()->after('payout_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'has_worker', 'my_role', 'payment_flow', 'partner_name', 'partner_phone',
                'worker_percentage', 'hunter_percentage', 'my_share_amount', 'partner_share_amount',
                'payout_status', 'payout_at'
            ]);
        });
    }
};
