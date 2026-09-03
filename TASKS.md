# TASKS.md — Roadmap Implementasi (Laravel, Local-Only, buat Vibecoding)

Gunakan file ini sebagai checklist. Kerjakan satu fase sampai selesai & jalan sebelum lanjut ke fase berikutnya, biar AI coding assistant kamu fokus dan konteksnya nggak kepanjangan tiap sesi.

> Tips: di awal tiap sesi vibecoding, kasih tau AI assistant kamu isi `PRD.md` dan `DESIGN.md` biar dia paham konteks project sebelum mulai ngoding.

## Fase 0 — Setup Project Lokal
- [ ] Install Laravel 11 baru: `composer create-project laravel/laravel abt-freelance`
- [ ] Setup SQLite: buat file kosong `database/database.sqlite`, set `DB_CONNECTION=sqlite` dan `DB_DATABASE` (path absolut) di `.env`
- [ ] *(Opsional, direkomendasikan)* Install Filament PHP: `composer require filament/filament` lalu `php artisan filament:install --panels`
- [ ] *(Opsional)* Install Laravel Breeze kalau mau tetap pasang login sederhana: `composer require laravel/breeze --dev` lalu `php artisan breeze:install`
- [ ] Install image processing: `composer require intervention/image`
- [ ] Install PDF export: `composer require barryvdh/laravel-dompdf`
- [ ] Jalankan `php artisan storage:link` biar folder `storage/app/public` bisa diakses browser
- [ ] Setup repo Git (opsional, bagus buat backup kode)
- [ ] Test: jalankan `php artisan serve`, buka `http://localhost:8000`, pastikan halaman default Laravel muncul

## Fase 1 — Migrasi Database
- [ ] Buat migration `categories` (id, name, timestamps)
- [ ] Buat migration `invoices` sesuai skema di `DESIGN.md` (termasuk `payment_type` enum, `dp_amount` nullable, `status` enum, `paid_at` nullable, `category_id` foreign key dengan `onDelete('restrict')`)
- [ ] Buat migration `testimonials` (4 kolom path gambar + `composed_image_path` + `posted_to_telegram` + `telegram_message_id`)
- [ ] Jalankan `php artisan migrate`
- [ ] Buat seeder kategori awal, misal "Joki Tugas", biar langsung bisa dipakai di Fase 3

## Fase 2 — Kategori Jasa
- [ ] **Kalau pakai Filament**: buat Filament Resource untuk model `Category` (`php artisan make:filament-resource Category`) — dapat halaman list/create/edit otomatis
- [ ] **Kalau manual**: buat `CategoryController` + routes (`resource route`) + Blade views (index, create/edit modal atau halaman terpisah)
- [ ] Tambahkan validasi: tolak hapus kategori yang masih dipakai invoice
- [ ] Test: tambah kategori baru "Jasa Website", pastikan langsung muncul di list

## Fase 3 — Fitur Invoice (dengan opsi DP / Lunas Langsung)
- [ ] Buat model `Invoice` dengan relasi `belongsTo(Category::class)`
- [ ] Buat form invoice (Filament Resource atau `InvoiceController` + Blade):
  - [ ] Input: client_name, dropdown category, description, deadline, total_amount
  - [ ] Toggle "Dengan DP" / "Bayar Lunas Langsung" (`payment_type`)
  - [ ] Kalau "Dengan DP" dipilih → tampilkan input `dp_amount`, kalau tidak → sembunyikan
- [ ] Generate `invoice_number` otomatis saat `store()` (format `INV-{tahun}-{urutan}`)
- [ ] Buat halaman list `/invoices` (tabel + filter kategori/status)
- [ ] Buat halaman `/invoices/{id}` — preview invoice lengkap (detail + QRIS)
- [ ] Buat halaman `/invoices/{id}/edit`:
  - [ ] Update data
  - [ ] Ubah status — kalau `payment_type = full`, opsi status cuma "Belum Bayar" & "Lunas" (skip "DP Terbayar")
  - [ ] Set `paid_at` otomatis (pakai model event/observer) saat status berubah jadi "Lunas"
- [ ] Test: buat 1 invoice dengan DP dan 1 invoice lunas langsung, cek tampilan preview beda di masing-masing

## Fase 4 — QRIS Statis + Export PDF & Gambar
- [ ] Upload gambar QRIS pribadi ke `storage/app/public/assets/qris.png`
- [ ] Tampilkan QRIS di halaman preview invoice (`/invoices/{id}`) beserta teks total yang harus ditransfer
- [ ] Pastikan Node.js terinstall di komputer, lalu `npm install puppeteer` di root project
- [ ] Install `spatie/browsershot`: `composer require spatie/browsershot`
- [ ] Buat Blade view khusus buat invoice yang bisa dipakai buat PDF maupun gambar (reuse layout dari halaman preview)
- [ ] Buat route `/invoices/{id}/export/{format}` + method `export($format)` di `InvoiceController`:
  - [ ] Kalau `$format == 'pdf'` → `Browsershot::html($html)->pdf()` → download PDF
  - [ ] Kalau `$format == 'jpg'` → `Browsershot::html($html)->windowSize(800, 1200)->save($path)` → download JPG
- [ ] Tambah 2 tombol di halaman preview invoice: "Export PDF" dan "Export Gambar"
- [ ] Test: export PDF dan gambar dari beberapa invoice (DP & lunas langsung), cek hasilnya rapi, QRIS jelas terlihat, dan CSS ter-render presisi sesuai desain

## Fase 5 — Fitur Testimoni 4-Grid + Telegram
- [ ] Buat bot Telegram lewat BotFather, catat token
- [ ] Tambahkan bot sebagai admin di channel testimoni, catat channel ID
- [ ] Simpan `TELEGRAM_BOT_TOKEN` & `TELEGRAM_CHANNEL_ID` di `.env`
- [ ] Buat model `Testimonial` + migration (kalau belum dari Fase 1)
- [ ] Buat form upload testimoni (`/testimonials`):
  - [ ] 4 kotak upload dengan label: Tugas, Chat dengan Customer, Hasil, Pelunasan
  - [ ] Input caption & nama klien (opsional)
- [ ] Buat `TestimonialController@store`:
  - [ ] Simpan 4 gambar asli ke `storage/app/public/testimonials/raw`
  - [ ] Compose jadi 1 gambar grid 2x2 pakai `intervention/image` (resize seragam + composite + padding putih)
  - [ ] Simpan gambar kolase ke `storage/app/public/testimonials/composed`
  - [ ] Panggil Telegram Bot API `sendPhoto` pakai Laravel `Http` client, kirim gambar kolase
  - [ ] Simpan `telegram_message_id`, update `posted_to_telegram`
- [ ] Buat halaman `/testimonials` (riwayat + preview kolase + status kirim Telegram)
- [ ] Buat halaman `/testimonials/{id}/edit`:
  - [ ] Tampilkan 4 gambar existing, masing-masing punya tombol "Ganti Gambar" sendiri
  - [ ] Slot yang tidak diganti otomatis tetap pakai gambar lama
- [ ] Buat `TestimonialController@update`:
  - [ ] Terima 0-4 file (hanya slot yang diganti)
  - [ ] Compose ulang kolase 2x2
  - [ ] Kalau `posted_to_telegram = true` → panggil Telegram `editMessageMedia` pakai `telegram_message_id`, ganti foto di postingan lama (bukan post baru)
- [ ] Test: upload testimoni, cek kolase rapi & muncul otomatis di channel Telegram; lalu coba edit 1 slot, pastikan postingan Telegram ke-update (bukan duplikat)

## Fase 6 — Dashboard Penghasilan
- [ ] Buat `DashboardController@index` dengan query agregasi (lihat detail query di `DESIGN.md`):
  - [ ] Total pendapatan
  - [ ] Piutang
  - [ ] Pendapatan per bulan
  - [ ] Breakdown per kategori
- [ ] Buat halaman `/` (dashboard):
  - [ ] Kartu ringkasan: Total Pendapatan & Total Piutang
  - [ ] Grafik pendapatan per bulan (Chart.js via CDN, atau Filament chart widget kalau pakai Filament)
  - [ ] Tabel/breakdown pendapatan per kategori jasa
- [ ] Test: ubah status beberapa invoice jadi "Lunas" (termasuk yang lunas langsung), pastikan angka & grafik di dashboard ter-update

## Fase 7 — Polish, Backup & Siap Dipakai
- [ ] Terapkan desain dari Stitch (putih dominan + aksen kuning neon) ke semua halaman
- [ ] *(Opsional)* Aktifkan Laravel Breeze kalau mau ada lock sederhana di depan aplikasi
- [ ] Install Google Drive Desktop, buat folder khusus backup (misal `ABT-FREELANCE-Backup`)
- [ ] Install `spatie/laravel-backup`: `composer require spatie/laravel-backup`, publish config-nya
- [ ] Konfigurasi disk backup di `config/backup.php` supaya hasil zip disimpan ke path folder Google Drive tadi
- [ ] Test: jalankan `php artisan backup:run`, pastikan file zip muncul di folder lokal dan ke-upload otomatis ke Google Drive
- [ ] *(Opsional)* Setup Windows Task Scheduler biar `php artisan backup:run` jalan otomatis (misal tiap malam)
- [ ] Tulis README singkat cara jalanin app tiap hari kerja: `php artisan serve` → buka `http://localhost:8000`
- [ ] Catat juga di README cara restore dari backup zip kalau suatu saat dibutuhkan (extract zip → copy `database.sqlite` & folder `storage` balik ke tempatnya)
- [ ] Cek sekali lagi alur end-to-end: buat invoice → export PDF/gambar → kirim manual → update status → upload testimoni → cek dashboard → jalankan backup manual
