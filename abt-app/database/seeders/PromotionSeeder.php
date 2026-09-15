<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\Category;
use App\Services\PromotionBannerGenerator;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $generator = app(PromotionBannerGenerator::class);
        $jokiCat = Category::where('name', 'like', '%Joki%')->first();
        $webCat = Category::where('name', 'like', '%Website%')->first();

        $templates = [
            [
                'title' => 'JASA JOKI ALL TUGAS KULIAH & SEKOLAH',
                'category_id' => $jokiCat?->id,
                'category_type' => 'joki',
                'tagline' => 'Cepat • Bebas Plagiasi Turnitin • Garansi Revisi • 24 Jam',
                'target_platform' => 'WhatsApp Groups, Story WA & Telegram',
                'copywriting' => "🎓 *BINGUNG TUGAS KULIAH NUMPUK & DEADLINE MEPET?* 🎓\n\nTenang, *ABT-JOKI* siap bantu beresin semua tugas kamu sampai tuntas & rapi!\n\n✨ *Layanan Kami Meliputi:*\n• Makalah / Paper / Essay / Resume / Review Jurnal\n• Pembuatan Slide Presentasi PPT Keren & Interaktif\n• Tugas Harian, Tugas Mingguan, UTS, & UAS All Jurusan\n• Olah Data Kuesioner & Analisis Statistik\n\n🔥 *Kenapa Harus ABT-JOKI?*\n✔ 100% Bebas Plagiasi (Cek Turnitin)\n✔ Garansi Revisi Sampai Nilai Maksimal / ACC\n✔ Privasi & Kerahasiaan Mahasiswa Aman 100%\n✔ Fast Response & Layanan Kilat 24 Jam!\n\n📲 *Konsultasi & Order Sekarang:*\nWhatsApp: 0889-8950-4780\nWebsite: https://abtfreelance.com",
            ],
            [
                'title' => 'BIMBINGAN SKRIPSI, TESIS & OLAH DATA SPSS',
                'category_id' => $jokiCat?->id,
                'category_type' => 'joki',
                'tagline' => 'Bab 1-5 • Uji Validitas • Uji Hipotesis • Bimbingan Sidang',
                'target_platform' => 'WhatsApp & Mahasiswa Akhir',
                'copywriting' => "🎓 *STUCK BAB 4 ATAU BINGUNG OLAH DATA SKRIPSI?* 📊\n\n*ABT-JOKI* siap bimbing dan selesaikan naskah skripsi & olah data kamu sampai ACC Dosen Pembimbing!\n\n✨ *Paket Bimbingan & Joki Skripsi:*\n• Bimbingan / Pengerjaan Bab 1 sampai Bab 5 Lengkap\n• Olah Data SPSS, SmartPLS, AMOS, EViews\n• Uji Validitas, Reliabilitas, Regresi, Asumsi Klasik & Hipotesis\n• Pembuatan Kuesioner Google Form & Tabulasi Data 100+ Responden\n\n💡 *Keuntungan Eksklusif:*\n✔ Diberikan Penjelasan Lengkap Cara Baca Output SPSS / Hasil Olah Data\n✔ Bimbingan Siap Menghadapi Sidang Skripsi & Sempro\n✔ Garansi Revisi Sepuasnya Tanpa Ribet\n\n📲 *Chat Sekarang:* WhatsApp 0889-8950-4780",
            ],
            [
                'title' => 'JASA PEMBUATAN WEBSITE & TUGAS CODING KILAT',
                'category_id' => $webCat?->id,
                'category_type' => 'website',
                'tagline' => 'Laravel • PHP • Python • Web Landing Page • Bugfix',
                'target_platform' => 'WhatsApp, Mahasiswa IT & Pebisnis',
                'copywriting' => "💻 *BUTUH WEBSITE CEPAT ATAU TUGAS CODING ERROR?* ⚡\n\n*ABT-DEV STUDIO* melayani jasa pembuatan website profesional & pengerjaan tugas pemrograman kilat!\n\n✨ *Layanan Website & Coding:*\n• Website Landing Page / Portofolio / Company Profile / Toko Online\n• Web Application Fullstack (Laravel, PHP, MySQL, Tailwind CSS)\n• Tugas Pemrograman Python, Java, C++, HTML/CSS/JavaScript\n• Perbaikan Bug, Error Script, Refactoring & Penambahan Fitur\n\n🚀 *Keunggulan:*\n✔ Tampilan Desain Modern, Responsif di HP & Laptop\n✔ Kode Bersih, Terstruktur & Mudah Dipelajari\n✔ Full Support & Panduan Instalasi Lengkap\n\n📲 *Konsultasi Gratis:* WhatsApp 0889-8950-4780",
            ],
            [
                'title' => 'LAYANAN JOKI TUGAS KILAT 24 JAM (EXPRESS)',
                'category_id' => $jokiCat?->id,
                'category_type' => 'joki',
                'tagline' => 'Selesai 3-12 Jam • Anti-Telat • Standby Setiap Hari',
                'target_platform' => 'WhatsApp Groups & Story WA',
                'copywriting' => "⚡ *DEADLINE TUGAS BESOK PAGI ATAU HITUNGAN JAM LAGI?* 🚨\n\nJangan panik! Gunakan layanan *JOKI KILAT EXPRESS 24 JAM* dari ABT-JOKI!\n\n🕒 *Estimasi Pengerjaan:*\n• Tugas Ringan / Resume / PPT: 2 - 4 Jam Selesai!\n• Makalah / Paper / Essay: 4 - 8 Jam Selesai!\n• Tugas Coding / Olah Data: 6 - 12 Jam Selesai!\n\n✨ *Jaminan Layanan:*\n✔ Standby Setiap Hari (Termasuk Hari Libur & Malam Hari)\n✔ Hasil Cepat, Rapi, & Tetap Berkualitas\n✔ Privasi 100% Rahasia\n\n📲 *Amankan Slot Kilat Kamu Sekarang:*\nWhatsApp: 0889-8950-4780",
            ],
            [
                'title' => 'OPEN SLOT FASTUR & TOURNAMENT EFOOTBALL MOBILE',
                'category_id' => null,
                'category_type' => 'tournament',
                'tagline' => 'Fastur 4 & 8 Slot • Match Kilat • Hadiah Juara Langsung Cair',
                'target_platform' => 'Komunitas eFootball & Grup WA Gamers',
                'copywriting' => "⚽ *OPEN SLOT FASTUR EFOOTBALL MOBILE MALAM INI!* 🔥\n\nYuk asah skill tim kamu, buktikan jadi yang terbaik, dan bawa pulang hadiah uang tunai langsung cair!\n\n🏆 *Format Turnamen:*\n• Sistem: Fastur 4 / 8 Slot Tim (Sistem Gugur)\n• Match Cepat, Fairplay, dan Live Update Bagan di Web!\n• Biaya Pendaftaran: Terjangkau & Hadiah Juara 1 Langsung Transfer\n\n📱 *Pantau Live Slot & Bagan Turnamen:* https://abtfreelance.com/turnamen/efootball/live\n\nSlot sangat terbatas! Langsung daftarkan tim kamu sekarang:\n📲 *WhatsApp Admin:* 0889-8950-4780",
            ],
        ];

        foreach ($templates as $idx => $tmpl) {
            $existing = Promotion::where('title', $tmpl['title'])->first();
            if ($existing) continue;

            $filename = 'promo_default_' . ($idx + 1) . '.jpg';
            $bannerPath = "promotions/banners/{$filename}";
            $absPath = storage_path("app/public/{$bannerPath}");

            $catName = 'ABT FREELANCE';
            if ($tmpl['category_type'] === 'joki') $catName = 'JOKI TUGAS & AKADEMIK';
            if ($tmpl['category_type'] === 'website') $catName = 'WEBSITE & CODING';
            if ($tmpl['category_type'] === 'tournament') $catName = 'EFOOTBALL TOURNAMENT';

            $generator->generate($tmpl['title'], $catName, $tmpl['tagline'], $absPath);

            Promotion::create([
                'category_id' => $tmpl['category_id'],
                'category_type' => $tmpl['category_type'],
                'title' => $tmpl['title'],
                'tagline' => $tmpl['tagline'],
                'banner_path' => $bannerPath,
                'banner_type' => 'auto_generated',
                'copywriting' => $tmpl['copywriting'],
                'target_platform' => $tmpl['target_platform'],
                'is_active' => true,
                'sort_order' => $idx + 1,
            ]);
        }
    }
}
