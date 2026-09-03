<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Invoice;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $joki = Category::firstOrCreate(['name' => 'Joki Tugas']);
        $web = Category::firstOrCreate(['name' => 'Jasa Website']);
        $desain = Category::firstOrCreate(['name' => 'Desain Grafis']);

        // Invoices
        $invoices = [
            [
                'invoice_number' => 'INV-2026-001',
                'title' => 'Pengembangan Landing Page Bisnis',
                'client_name' => 'PT. Teknologi Maju',
                'category_id' => $web->id,
                'description' => "Pengembangan Landing Page Bisnis dengan integrasi formulir kontak dan Google Maps.\n- Desain responsif mobile & desktop\n- Integrasi form ke WhatsApp\n- Optimasi SEO dasar",
                'deadline' => Carbon::now()->addDays(7),
                'payment_type' => 'dp',
                'dp_amount' => 2500000,
                'total_amount' => 5500000,
                'status' => 'dp_paid',
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'invoice_number' => 'INV-2026-002',
                'title' => 'Penyelesaian Tugas Pemrograman Web',
                'client_name' => 'Budi Santoso',
                'category_id' => $joki->id,
                'description' => "Pengerjaan tugas akhir pemrograman web dengan Laravel dan MySQL. Termasuk pembuatan database schema dan CRUD lengkap.",
                'deadline' => Carbon::now()->addDays(2),
                'payment_type' => 'full',
                'dp_amount' => null,
                'total_amount' => 350000,
                'status' => 'unpaid',
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'invoice_number' => 'INV-2026-003',
                'title' => 'Desain Branding & Logo Studio',
                'client_name' => 'Creative Studio AB',
                'category_id' => $desain->id,
                'description' => "Paket komplit desain logo, kartu nama, kop surat, dan brand guideline booklet (PDF).",
                'deadline' => Carbon::now()->subDays(10),
                'payment_type' => 'full',
                'dp_amount' => null,
                'total_amount' => 1200000,
                'status' => 'paid',
                'paid_at' => Carbon::now()->subDays(8),
                'created_at' => Carbon::now()->subDays(12),
            ],
            [
                'invoice_number' => 'INV-2026-004',
                'title' => 'Website Company Profile Toko Kopi',
                'client_name' => 'Toko Kopi Senja',
                'category_id' => $web->id,
                'description' => "Website company profile 5 halaman: Beranda, Tentang Kami, Menu, Galeri, Kontak. Termasuk setup domain & hosting.",
                'deadline' => Carbon::now()->addDays(14),
                'payment_type' => 'dp',
                'dp_amount' => 1000000,
                'total_amount' => 2800000,
                'status' => 'dp_paid',
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'invoice_number' => 'INV-2026-005',
                'title' => 'Makalah Analisis Data SPSS Bab 4-5',
                'client_name' => 'Siti Nurhaliza',
                'category_id' => $joki->id,
                'description' => "Analisis data regresi linier berganda menggunakan SPSS 26. Termasuk uji validitas, reliabilitas, asumsi klasik, dan interpretasi output.",
                'deadline' => Carbon::now()->subDays(3),
                'payment_type' => 'full',
                'dp_amount' => null,
                'total_amount' => 450000,
                'status' => 'paid',
                'paid_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(4),
            ],
        ];

        foreach ($invoices as $inv) {
            Invoice::firstOrCreate(['invoice_number' => $inv['invoice_number']], $inv);
        }
    }
}
