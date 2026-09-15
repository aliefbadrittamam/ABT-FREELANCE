@extends('layouts.app')

@section('title', 'Tambah Materi Iklan Baru — ABT-FREELANCE')
@section('header', 'Tambah Materi Iklan')

@section('content')
<div class="mb-6 sm:mb-8">
    <a href="{{ route('promotions.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-secondary dark:text-gray-400 hover:text-primary dark:hover:text-primary-container mb-2">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        Kembali ke Daftar Iklan
    </a>
    <h1 class="text-2xl sm:text-[30px] font-black text-on-surface dark:text-white tracking-tight leading-tight">Buat Materi Iklan Baru</h1>
    <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5">Susun copywriting iklan dan buat poster otomatis bertema Dark-Neon atau unggah poster sendiri.</p>
</div>

<div class="max-w-4xl bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-8 shadow-sm"
     x-data="{
        title: '{{ old('title', '') }}',
        categoryType: '{{ old('category_type', 'joki') }}',
        tagline: '{{ old('tagline', '') }}',
        bannerOption: '{{ old('banner_option', 'generate') }}',
        copywriting: '{{ old('copywriting', '') }}',
        setTemplate(tTitle, tTagline, tType, tCopy) {
            this.title = tTitle;
            this.tagline = tTagline;
            this.categoryType = tType;
            this.copywriting = tCopy;
        }
     }">

    <!-- Quick Template Inspiration Pills -->
    <div class="mb-6 p-4 rounded-xl bg-surface-container/50 dark:bg-[#252525]/50 border border-border-subtle dark:border-[#333]">
        <label class="block text-[11px] font-bold text-on-surface dark:text-white uppercase tracking-wider mb-2 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-primary text-base">auto_fix_high</span>
            Gunakan Template Copywriting Cepat:
        </label>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="setTemplate('JASA JOKI ALL TUGAS KULIAH & SEKOLAH', 'Cepat • Bebas Plagiasi Turnitin • Garansi Revisi', 'joki', '🎓 *BINGUNG TUGAS KULIAH NUMPUK & DEADLINE MEPET?* 🎓\n\nTenang, *ABT-JOKI* siap bantu beresin semua tugas kamu sampai tuntas & rapi!\n\n✨ *Layanan Kami Meliputi:*\n• Makalah / Paper / Essay / Resume\n• Review Jurnal Nasional & Internasional\n• Pembuatan PPT Slide Presentasi Keren\n• Tugas Harian, UTS, & UAS All Jurusan\n\n🔥 *Kenapa Harus ABT-JOKI?*\n✔ 100% Bebas Plagiasi (Cek Turnitin)\n✔ Garansi Revisi Sampai ACC\n✔ Privasi & Kerahasiaan Aman 100%\n✔ Fast Response & Bisa Kilat 24 Jam!\n\n📲 *Konsultasi & Order Sekarang:* \nWhatsApp: 0889-8950-4780\nInstagram: @abtjoki')"
                    class="px-3 py-1.5 bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#333] hover:border-primary text-xs font-semibold rounded-lg transition">
                📄 All Tugas Kuliah
            </button>

            <button type="button" @click="setTemplate('BIMBINGAN SKRIPSI, TESIS & OLAH DATA SPSS', 'Bab 1-5 • Uji Validitas • Bimbingan Sampai Sidang', 'joki', '🎓 *STUCK BAB 4 ATAU BINGUNG OLAH DATA SKRIPSI?* 📊\n\n*ABT-JOKI* siap bimbing dan tuntaskan naskah skripsi & olah data kamu sampai ACC Dosen Pembimbing!\n\n✨ *Paket Bimbingan & Joki Skripsi:*\n• Bimbingan / Pengerjaan Bab 1 sampai Bab 5\n• Olah Data SPSS, PLS, AMOS, EViews\n• Uji Validitas, Reliabilitas, Regresi & Hipotesis\n• Pembuatan Kuisioner Google Form & Tabulasi Data\n\n💡 *Keuntungan:*\n✔ Diberikan Penjelasan Lengkap Cara Baca Output SPSS\n✔ Bimbingan Siap Menghadapi Sidang / Sempro\n✔ Garansi Revisi Sepuasnya\n\n📲 *Chat Sekarang:* WhatsApp 0889-8950-4780')"
                    class="px-3 py-1.5 bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#333] hover:border-primary text-xs font-semibold rounded-lg transition">
                📊 Skripsi & Olah Data
            </button>

            <button type="button" @click="setTemplate('JASA PEMBUATAN WEBSITE & TUGAS CODING', 'Laravel • PHP • Python • Web Landing Page', 'website', '💻 *BUTUH WEBSITE CEPAT ATAU TUGAS CODING ERROR?* ⚡\n\n*ABT-DEV STUDIO* melayani jasa pembuatan website profesional & pengerjaan tugas pemrograman kilat!\n\n✨ *Layanan Website & Coding:*\n• Website Landing Page / Portofolio / Toko Online\n• Web Application Fullstack (Laravel, PHP, MySQL, Tailwind)\n• Tugas Coding Python, Java, C++, HTML/CSS/JS\n• Perbaikan Bug, Error Script & Penambahan Fitur\n\n🚀 *Keunggulan:*\n✔ Tampilan Modern, Responsif di HP & Laptop\n✔ Kode Bersih, Terstruktur & Mudah Dipelajari\n✔ Full Support Panduan Instalasi\n\n📲 *Hubungi Kami:* WhatsApp 0889-8950-4780')"
                    class="px-3 py-1.5 bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#333] hover:border-primary text-xs font-semibold rounded-lg transition">
                💻 Web & Coding
            </button>

            <button type="button" @click="setTemplate('OPEN SLOT FASTUR EFOOTBALL MOBILE MALAM INI', 'Fastur 4 & 8 Slot • Match Cepat • Hadiah Langsung Cair', 'tournament', '⚽ *OPEN SLOT FASTUR EFOOTBALL MOBILE MALAM INI!* 🔥\n\nYuk asah skill tim kamu dan bawa pulang hadiah uang tunai langsung cair!\n\n🏆 *Rincian Sesi Turnamen:*\n• Format: Fastur 4 / 8 Slot Tim (Sistem Gugur)\n• Match Cepat, Fairplay, dan Live Update di Web!\n• Regis: Terjangkau & Hadiah Juara 1 Langsung Transfer\n\n📱 *Pantau Live Slot & Bagan:* https://domain.com/turnamen/efootball/live\n\nSlot terbatas! Amankan slot tim kamu sekarang:\n📲 *WhatsApp Admin:* 0889-8950-4780')"
                    class="px-3 py-1.5 bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#333] hover:border-primary text-xs font-semibold rounded-lg transition">
                🎮 Turnamen eFootball
            </button>
        </div>
    </div>

    <form action="{{ route('promotions.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="space-y-4 sm:space-y-5">
            <!-- Title -->
            <div>
                <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1.5">Judul Penawaran Iklan</label>
                <input type="text" name="title" x-model="title" required placeholder="Contoh: JASA JOKI ALL TUGAS KULIAH & SEKOLAH"
                    class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white font-medium focus:ring-2 focus:ring-primary outline-none">
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Category & Tagline -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1.5">Kategori Jasa</label>
                    <div class="space-y-2">
                        <select name="category_type" x-model="categoryType" required class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm font-medium focus:ring-2 focus:ring-primary outline-none">
                            <option value="joki">Joki Tugas & Skripsi</option>
                            <option value="website">Jasa Website & Coding</option>
                            <option value="tournament">Turnamen eFootball</option>
                            <option value="general">Umum / Branding</option>
                        </select>
                        <select name="category_id" class="w-full px-3.5 py-2 bg-surface dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-secondary dark:text-gray-400 rounded-lg text-xs outline-none">
                            <option value="">-- Tautkan ke Master Kategori (Opsional) --</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->prefix }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1.5">Tagline / Poin Unggulan</label>
                    <input type="text" name="tagline" x-model="tagline" placeholder="Contoh: Cepat • Bebas Plagiasi • Garansi Revisi"
                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                    <p class="text-[11px] text-secondary dark:text-gray-400 mt-1">Gunakan tanda titik bulat • untuk memisahkan poin pada poster otomatis.</p>
                </div>
            </div>

            <!-- Banner Poster Option -->
            <div class="p-4 rounded-xl border border-border-subtle dark:border-[#2a2a2a] bg-surface dark:bg-[#181818] space-y-3">
                <label class="block text-[11px] font-bold text-on-surface dark:text-white uppercase tracking-wider">
                    Pilihan Banner / Poster Iklan:
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex items-center gap-2.5 p-3 rounded-lg border cursor-pointer transition"
                           :class="bannerOption === 'generate' ? 'bg-primary-container/20 border-primary text-on-surface dark:text-white font-bold' : 'border-border-subtle dark:border-[#333] text-secondary dark:text-gray-400'">
                        <input type="radio" name="banner_option" value="generate" x-model="bannerOption" class="text-primary focus:ring-primary">
                        <div>
                            <span class="text-xs block">✨ Buat Otomatis</span>
                            <span class="text-[10px] font-normal opacity-80">Desain Dark-Neon HD</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-2.5 p-3 rounded-lg border cursor-pointer transition"
                           :class="bannerOption === 'upload' ? 'bg-primary-container/20 border-primary text-on-surface dark:text-white font-bold' : 'border-border-subtle dark:border-[#333] text-secondary dark:text-gray-400'">
                        <input type="radio" name="banner_option" value="upload" x-model="bannerOption" class="text-primary focus:ring-primary">
                        <div>
                            <span class="text-xs block">📤 Unggah Poster</span>
                            <span class="text-[10px] font-normal opacity-80">File gambar sendiri</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-2.5 p-3 rounded-lg border cursor-pointer transition"
                           :class="bannerOption === 'none' ? 'bg-primary-container/20 border-primary text-on-surface dark:text-white font-bold' : 'border-border-subtle dark:border-[#333] text-secondary dark:text-gray-400'">
                        <input type="radio" name="banner_option" value="none" x-model="bannerOption" class="text-primary focus:ring-primary">
                        <div>
                            <span class="text-xs block">📝 Teks Saja</span>
                            <span class="text-[10px] font-normal opacity-80">Tanpa gambar poster</span>
                        </div>
                    </label>
                </div>

                <!-- File Input if Upload Selected -->
                <div x-show="bannerOption === 'upload'" x-cloak class="pt-2">
                    <input type="file" name="banner_file" accept="image/*"
                           class="w-full text-xs text-on-surface dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-container file:text-on-surface hover:file:brightness-95 cursor-pointer">
                    <p class="text-[11px] text-secondary dark:text-gray-400 mt-1">Format: JPG, PNG, WEBP. Maks 5MB.</p>
                </div>
            </div>

            <!-- Copywriting Textarea -->
            <div>
                <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1.5">Teks Copywriting Iklan (Siap Salin & Sebar)</label>
                <textarea name="copywriting" x-model="copywriting" rows="8" required placeholder="Tuliskan format chat promosi lengkap dengan emoji, daftar jasa, keunggulan, dan kontak WhatsApp..."
                    class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm font-mono text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none leading-relaxed"></textarea>
                @error('copywriting') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Target Platform (Optional) -->
            <div>
                <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1">Target Media Penyebaran</label>
                <input type="text" name="target_platform" value="WhatsApp Groups & Telegram Channel" placeholder="Contoh: WhatsApp Groups, Story WA, Instagram, Telegram"
                    class="w-full px-3.5 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-border-subtle dark:border-[#2a2a2a] mt-6">
            <a href="{{ route('promotions.index') }}" class="px-5 py-2.5 bg-transparent dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-secondary dark:text-gray-300 font-semibold hover:bg-surface-variant transition">Batal</a>
            <button type="submit" class="px-6 py-2.5 bg-primary-container text-on-surface font-bold text-xs sm:text-sm rounded-lg hover:brightness-95 transition shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">save</span>
                Simpan Materi Iklan
            </button>
        </div>
    </form>
</div>
@endsection
