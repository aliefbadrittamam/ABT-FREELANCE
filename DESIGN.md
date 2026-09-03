# DESIGN.md — Arsitektur & Desain Teknis (Laravel, Local-Only)

## 1. Tech Stack
| Layer | Pilihan | Kenapa |
|---|---|---|
| Framework | Laravel 11 (PHP) | Mature buat internal tool/admin panel, ekosistem besar, jalan lokal tanpa drama |
| Admin Panel Builder | Filament PHP *(opsional, sangat direkomendasikan)* | Generate CRUD kategori/invoice/testimoni cepat lewat PHP class deklaratif, hemat waktu development |
| Database | SQLite (file lokal `database/database.sqlite`) | Zero-setup, cocok single-user local app; gampang di-backup (tinggal copy 1 file); bisa migrasi ke MySQL nanti kalau perlu |
| Image processing | `intervention/image` | Compose 4 gambar testimoni jadi 1 kolase grid 2x2 |
| Export Invoice | `spatie/browsershot` | Render Blade view invoice pakai headless Chrome, hasilnya bisa PDF *maupun* gambar (JPG) dari template yang sama — CSS modern dari desain Stitch ke-render presisi. Butuh Node.js + Puppeteer terinstall lokal |
| Bot | Telegram Bot API via Laravel `Http` Client | Kirim & update testimoni ke channel |
| File Storage | Laravel local disk (`storage/app/public`) | Nggak butuh cloud storage lagi karena semuanya lokal |
| Hosting | **Tidak diperlukan** — dijalankan lokal via `php artisan serve` | Klien tidak pernah mengakses aplikasi ini |
| Auth | Opsional — Laravel Breeze kalau mau pasang lock sederhana | App cuma diakses di komputer admin sendiri |
| Chart (dashboard) | Chart.js via CDN, atau Filament widget kalau pakai Filament | Ringan, cukup buat grafik pendapatan bulanan |

## 2. Arsitektur Sistem (garis besar)

```
Browser (localhost, komputer admin)
        │
        ▼
   Laravel App (lokal, php artisan serve)
        │
        ├──> SQLite (database lokal, 1 file)
        ├──> intervention/image (compose kolase testimoni)
        ├──> spatie/browsershot (generate PDF & gambar invoice via headless Chrome)
        ├──> storage/app/public (simpan gambar QRIS, testimoni, PDF)
        └──> Telegram Bot API (butuh internet, cuma buat kirim testimoni)
```

Karena aplikasi ini **hanya diakses oleh admin sendiri di localhost**, tidak ada lagi pemisahan rute "publik" vs "admin" seperti rancangan sebelumnya — semua halaman otomatis privat. Invoice tidak lagi dibagikan lewat link web; sebagai gantinya, admin **export ke PDF** dan kirim manual ke klien.

## 3. Struktur Routing

| Route | Fungsi |
|---|---|
| `/` | Dashboard Penghasilan (halaman utama) |
| `/categories` | List + kelola kategori jasa |
| `/invoices` | Daftar semua invoice, bisa difilter per kategori/status |
| `/invoices/create` | Form buat invoice baru |
| `/invoices/{id}` | Detail/Preview invoice (tampilkan QRIS, tombol Export PDF & Cetak) |
| `/invoices/{id}/edit` | Edit invoice + ubah status |
| `/invoices/{id}/export/{format}` | Endpoint generate & download invoice — `{format}` = `pdf` atau `jpg` |
| `/testimonials` | Riwayat testimoni + form upload 4-slot (menggantikan konsep "galeri publik") |
| `/testimonials/{id}/edit` | Edit testimoni — ganti gambar per-slot |
| `/login` | *(opsional)* kalau tetap mau pasang Laravel Breeze |

> Kalau pakai Filament, sebagian besar route di atas (kategori, invoice, testimoni) otomatis ter-generate lewat Filament Resource — kamu cuma perlu definisikan field & tabelnya, bukan bikin controller + view manual satu-satu.

## 4. Skema Database

**Table: `categories`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, pk | auto increment |
| name | string | contoh: "Joki Tugas", "Jasa Website" |
| created_at, updated_at | timestamp | |

**Table: `invoices`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, pk | |
| invoice_number | string | contoh: `INV-2026-001` |
| client_name | string | |
| category_id | foreignId → categories.id | `onDelete('restrict')` biar histori tetap akurat |
| description | text | detail pekerjaan/tugas |
| deadline | date | |
| payment_type | enum(`dp`, `full`) | `full` = bayar lunas langsung tanpa DP |
| dp_amount | decimal, nullable | wajib diisi kalau `payment_type = dp`, null kalau `full` |
| total_amount | decimal | |
| status | enum(`unpaid`, `dp_paid`, `paid`) | kalau `payment_type = full`, status `dp_paid` tidak dipakai (langsung `unpaid` → `paid`) |
| paid_at | timestamp, nullable | diisi otomatis saat status berubah jadi `paid`, dipakai buat dashboard penghasilan |
| created_at, updated_at | timestamp | |

**Table: `testimonials`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, pk | |
| image_tugas_path | string | path lokal di `storage/app/public/testimonials/raw` |
| image_chat_path | string | |
| image_hasil_path | string | |
| image_pelunasan_path | string | |
| composed_image_path | string | hasil gabungan grid 2x2 — dikirim ke Telegram |
| caption | text, nullable | |
| client_name | string, nullable | |
| posted_to_telegram | boolean | default `false` |
| telegram_message_id | string, nullable | |
| created_at, updated_at | timestamp | |

**Storage lokal (`storage/app/public`):**
- `assets/qris.png` — gambar QRIS statis, upload sekali dipakai di semua invoice
- `testimonials/raw/` — 4 gambar asli per testimoni
- `testimonials/composed/` — gambar kolase hasil gabungan
- Jalankan `php artisan storage:link` sekali di awal biar folder ini bisa diakses browser (`/storage/...`)

## 5. Environment Variables (`.env`)

```
APP_NAME="ABT-FREELANCE"
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

TELEGRAM_BOT_TOKEN=            # dari @BotFather
TELEGRAM_CHANNEL_ID=           # contoh: @nama_channel atau -100xxxxxxxxxx
```

## 6. Controller / Fitur per Modul

| Modul | Controller (atau Filament Resource) | Fungsi utama |
|---|---|---|
| Kategori | `CategoryController` | index, store, update, destroy (tolak delete kalau masih dipakai invoice) |
| Invoice | `InvoiceController` | index, create, store (generate invoice_number), show (preview), edit, update (set `paid_at` otomatis saat status → `paid`), `export($format)` — `format` = `pdf` atau `jpg` |
| Testimoni | `TestimonialController` | index, store (upload 4 file → compose kolase → simpan → kirim Telegram), edit, update (0-4 file opsional → compose ulang → `editMessageMedia` ke postingan lama) |
| Dashboard | `DashboardController` | index — agregasi total pendapatan, piutang, pendapatan per bulan, breakdown per kategori |

### Query agregasi dashboard (`DashboardController@index`)
- Total pendapatan: `Invoice::where('status', 'paid')->sum('total_amount')`
- Piutang: untuk tiap invoice belum lunas → `unpaid` = `total_amount`, `dp_paid` = `total_amount - dp_amount`, dijumlahkan semua
- Pendapatan per bulan: `groupBy(DB::raw('MONTH(paid_at)'))` untuk invoice `status = paid`
- Breakdown per kategori: join `categories`, `groupBy('category_id')`, `sum('total_amount')` where `status = paid`

## 7. Alur Composing Testimoni Grid (detail)

1. Admin isi form `/testimonials` dengan 4 file upload (Tugas, Chat dengan Customer, Hasil, Pelunasan) + caption/nama klien opsional
2. `TestimonialController@store` menerima request:
   - Ke-4 file disimpan ke `storage/app/public/testimonials/raw`
   - Pakai `intervention/image`: resize tiap gambar ke ukuran seragam (misal 540x540, `fit()`), gabungkan jadi 1 canvas 1080x1080 (2x2) pakai `Image::canvas()` + `insert()` di tiap kuadran, beri padding putih antar gambar
   - Gambar kolase disimpan ke `storage/app/public/testimonials/composed`
3. Insert 1 row ke tabel `testimonials` (4 path asli + 1 path kolase + caption + client_name)
4. Server `Http::attach(...)->post('https://api.telegram.org/bot' . env('TELEGRAM_BOT_TOKEN') . '/sendPhoto', [...])` dengan `chat_id` dan gambar kolase
5. Simpan `message_id` hasil response ke DB, set `posted_to_telegram = true`
6. Kalau composing gagal atau kirim ke Telegram gagal, data tetap tersimpan di DB (`posted_to_telegram = false`) supaya admin bisa retry manual

### Edit Testimoni (ganti 1 slot tanpa upload ulang semua)

1. Halaman `/testimonials/{id}/edit` menampilkan 4 gambar yang sudah ada, masing-masing dengan tombol "Ganti Gambar" terpisah
2. Admin cuma upload ulang slot yang mau diganti — slot lain otomatis pakai gambar lama
3. `TestimonialController@update` menerima 0-4 file (opsional per slot):
   - Slot yang dapat file baru → simpan file baru, ganti path di DB
   - Slot yang tidak dapat file baru → tetap pakai path lama
4. Compose ulang kolase 2x2 dari 4 path (kombinasi lama + baru), update `composed_image_path`
5. Kalau `posted_to_telegram = true`, panggil endpoint Telegram `editMessageMedia` (bukan `sendPhoto` baru) dengan `chat_id`, `message_id` (dari `telegram_message_id`), dan gambar kolase baru — ini **mengganti foto di postingan yang sudah ada**, bukan bikin postingan duplikat
6. Kalau `editMessageMedia` gagal (misal pesan sudah terlalu lama), tampilkan notifikasi ke admin bahwa update lokal berhasil tapi update Telegram perlu dicek manual

## 8. Alur Invoice: DP vs Lunas Langsung

- Saat buat invoice, admin pilih `payment_type`:
  - **`dp`** → wajib isi `dp_amount`, status invoice bisa transisi `unpaid → dp_paid → paid`
  - **`full`** → field DP disembunyikan di form, status invoice hanya transisi `unpaid → paid` (skip `dp_paid`)
- Halaman preview invoice & PDF menyesuaikan tampilan:
  - Kalau `dp`: tampilkan baris Total, DP, dan Sisa Bayar
  - Kalau `full`: tampilkan baris Total saja dengan keterangan "Dibayar Lunas"

## 9. Alur Invoice: Preview + Export PDF/Gambar + QRIS

1. Gambar QRIS pribadi diupload sekali ke `storage/app/public/assets/qris.png`
2. Halaman `/invoices/{id}` menampilkan preview invoice lengkap: detail klien, kategori, deskripsi, total (+ DP/sisa kalau ada), dan gambar QRIS + teks "Transfer sesuai nominal: Rp X" (karena QRIS statis, nominal tidak ter-encode otomatis)
3. Ada 2 tombol export, keduanya render Blade view invoice yang sama lewat `spatie/browsershot` (headless Chrome), cuma beda output method:
   - **"Export PDF"** → route `/invoices/{id}/export/pdf` → `InvoiceController@export('pdf')` → `Browsershot::html($html)->pdf()` → download file PDF
   - **"Export Gambar"** → route `/invoices/{id}/export/jpg` → `InvoiceController@export('jpg')` → `Browsershot::html($html)->windowSize(800, 1200)->save($path)` → download file JPG
4. Admin pilih format sesuai kebutuhan, lalu kirim manual ke klien via WhatsApp/chat — gambar biasanya lebih cepat dibuka karena langsung preview di chat, PDF lebih cocok buat arsip/dokumen resmi
5. Setiap file yang di-export juga disimpan salinannya ke `storage/app/public/invoices/exports/{invoice_number}.{pdf|jpg}` sebagai arsip resmi — bukan cuma di-download sekali lalu hilang. Arsip ini otomatis ikut ke-backup lewat strategi di bagian 11
6. *(Opsional)* Tombol "Cetak Langsung" tetap bisa disediakan untuk print langsung dari browser kalau admin mau cetak fisik

> Catatan setup: `spatie/browsershot` butuh Node.js + `puppeteer` terinstall di komputer (`npm install puppeteer` di root project, atau global). Ini satu-satunya dependency non-PHP di project ini — worth it karena hasil render CSS-nya jauh lebih presisi ke desain Stitch dibanding engine PDF murni PHP.

## 10. Strategi Backup & Storage

Aplikasi ini **tidak butuh cloud storage supaya fiturnya jalan** — semua penyimpanan (database SQLite, gambar QRIS, testimoni, arsip export invoice) cukup pakai storage lokal karena app-nya memang cuma dipakai lokal. Tapi karena data yang disimpan adalah data bisnis/finansial (riwayat invoice & pembayaran), tetap perlu strategi backup supaya nggak hilang total kalau laptop rusak/hilang.

**Rekomendasi: Google Drive Desktop (gratis, 15GB) + `spatie/laravel-backup`**

1. Install Google Drive Desktop di laptop, biarkan dia bikin folder lokal yang otomatis ke-sync ke cloud (misal `G:\My Drive\ABT-FREELANCE-Backup`)
2. Install `spatie/laravel-backup`: `composer require spatie/laravel-backup`
3. Konfigurasi package ini supaya hasil backup (zip berisi database + folder storage) disimpan ke path lokal yang ada di dalam folder Google Drive tadi
4. Jalankan `php artisan backup:run` secara manual (misal di akhir sesi kerja), atau *(opsional)* jadwalkan otomatis lewat Windows Task Scheduler
5. Google Drive otomatis upload file zip backup itu ke cloud di background — tidak perlu setup API key, credential, atau kode integrasi cloud storage apapun

Kenapa lewat backup zip, bukan live-sync folder `storage/app/public` langsung ke Google Drive: kalau file `database.sqlite` ke-sync tepat saat sedang ditulis, risikonya bisa corrupt. Backup zip membuat snapshot yang "beku" dulu sebelum di-upload, jadi lebih aman.

Kapasitas 15GB gratis dari Google Drive jauh lebih dari cukup untuk skala penggunaan aplikasi ini (ratusan invoice + testimoni biasanya cuma makan beberapa ratus MB).

> Alternatif kalau nggak mau pakai Google Drive: OneDrive (gratis 5GB, biasanya udah include di Windows) atau Dropbox (gratis 2GB, lebih kecil) — caranya sama persis, tinggal ganti folder tujuan backup-nya.

## 11. Pertimbangan Desain UI


- Invoice (baik di layar preview maupun PDF) harus terlihat profesional: header nama bisnis "ABT-FREELANCE", nomor invoice, tanggal, kategori jasa, tabel rincian, total (+ DP/sisa kalau ada), QRIS di bagian bawah
- Form testimoni: 4 kotak upload dengan label jelas (Tugas / Chat dengan Customer / Hasil / Pelunasan) + preview thumbnail sebelum submit
- Dashboard penghasilan: kartu ringkasan (total pendapatan, piutang) di atas, grafik bulanan di tengah, tabel breakdown per kategori di bawah
- Palet warna: putih dominan + aksen kuning neon (lihat `STITCH.md` buat detail hex code & referensi visual)
- Karena aplikasi cuma dipakai lokal, tidak perlu terlalu ketat soal cross-browser/mobile compatibility — tapi tetap enak kalau dibuka di laptop dengan layar berbeda-beda
