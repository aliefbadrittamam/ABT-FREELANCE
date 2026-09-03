# PRD — ABT-FREELANCE (Invoice & Testimoni, Local App)

## 1. Latar Belakang
ABT-FREELANCE adalah alat internal untuk freelancer penyedia berbagai jasa (saat ini joki tugas, dengan rencana ekspansi ke jasa lain seperti pembuatan website, desain, dll). Aplikasi ini **dijalankan secara lokal** oleh admin (pemilik bisnis) di komputernya sendiri — klien **tidak pernah mengakses aplikasi ini**. Semua invoice dikirim manual oleh admin dalam bentuk file PDF via WhatsApp/chat, dan testimoni tetap otomatis ter-posting ke channel Telegram publik.

Dibutuhkan alat manajemen sederhana untuk:
- Membuat invoice profesional per pesanan secara cepat dan dinamis, dikelompokkan per kategori jasa
- Meng-export invoice sebagai file PDF (lengkap dengan QRIS pembayaran) untuk dikirim manual ke klien
- Mengotomatisasi posting testimoni klien (dalam format kolase 4-grid) ke channel Telegram
- Memantau penghasilan dari seluruh lini jasa dalam satu dashboard

## 2. Tujuan
- Mempercepat pembuatan invoice per order (target < 2 menit per invoice)
- Invoice dalam bentuk PDF terlihat profesional saat dikirim ke klien
- Testimoni otomatis tersusun rapi jadi 1 gambar kolase dan ter-posting ke Telegram tanpa proses manual
- Sistem mudah diperluas ke jenis jasa baru tanpa ubah struktur data (lewat kategori dinamis)
- Bisa memantau total penghasilan & piutang kapan saja
- Mendukung alur pembayaran fleksibel (dengan DP atau lunas langsung)
- Tidak butuh biaya hosting sama sekali — cukup dijalankan lokal

## 3. Target Pengguna
- **Admin** (pemilik jasa) — satu-satunya user, menjalankan aplikasi di komputer sendiri untuk mengelola kategori, invoice & testimoni
- **Klien** — **tidak mengakses aplikasi sama sekali**. Klien hanya menerima file PDF invoice yang dikirim manual oleh admin via WhatsApp/chat lain

## 4. Fitur Utama

### 4.1 Manajemen Kategori Jasa
- Admin bisa menambah, mengedit, dan menghapus kategori jasa (contoh: "Joki Tugas", "Jasa Website", "Desain Grafis")
- Kategori dipakai saat membuat invoice, sehingga laporan pendapatan bisa dipilah per lini bisnis
- Sistem tidak dibatasi hanya untuk joki tugas — kategori baru bisa ditambahkan kapan saja tanpa perlu ubah kode/struktur database

### 4.2 Manajemen Invoice
- Admin membuat invoice baru dengan input dinamis: **judul invoice** (custom, bisa diedit bebas — misal "Invoice Pembuatan Website Toko Kue Bu Rina"), nama klien, kategori jasa (dropdown), deskripsi detail pekerjaan, deadline, total biaya
- **Jenis pembayaran fleksibel** — admin pilih salah satu:
  - **Dengan DP**: isi nominal DP, sisa dibayar belakangan (alur status: Belum Bayar → DP Terbayar → Lunas)
  - **Bayar Lunas Langsung**: tanpa DP, langsung bayar penuh (alur status: Belum Bayar → Lunas, skip status DP)
- Nomor invoice ter-generate otomatis (contoh: `INV-2026-001`) — terpisah dari judul invoice, tetap ada sebagai identifier resmi
- Invoice bisa diedit setelah dibuat, termasuk judulnya
- Invoice punya halaman preview di aplikasi lokal, menampilkan semua detail + metode pembayaran yang aktif
- **Invoice bisa di-export sebagai file PDF maupun gambar (JPG)** — admin pilih format sesuai kebutuhan: PDF buat dokumen resmi/arsip, gambar buat dikirim cepat lewat WhatsApp (langsung ke-preview di chat tanpa perlu di-tap dulu) — inilah yang dikirim manual oleh admin ke klien (bukan link web)
- Riwayat semua invoice dalam tabel, bisa difilter per kategori jasa

### 4.3 Testimoni (Kolase 4-Grid)
- Form upload testimoni punya **4 slot gambar tetap** dengan label:
  1. Tugas
  2. Chat dengan Customer
  3. Hasil
  4. Pelunasan
- Setelah admin upload ke-4 gambar, sistem **otomatis menyusun jadi 1 gambar kolase grid 2x2**
- Gambar kolase inilah yang dikirim sebagai **1 post foto** ke channel Telegram (bukan 4 foto terpisah) — channel Telegram inilah yang berfungsi sebagai etalase publik testimoni (bukan halaman website, karena aplikasi ini dipakai lokal)
- Ke-4 gambar asli tetap tersimpan di sistem sebagai arsip/referensi admin
- Admin bisa lihat riwayat testimoni di aplikasi lokal, termasuk status terkirim/gagal ke Telegram
- **Edit gampang, per-slot** — kalau ada 1 gambar yang salah/mau diganti, admin cukup ganti slot itu saja, tidak perlu upload ulang ke-4 gambar. Sistem otomatis menyusun ulang kolase dengan gambar baru, lalu memperbarui postingan Telegram yang sudah ada (bukan membuat postingan baru/duplikat)

### 4.4 Dashboard Penghasilan
- **Total pendapatan** — dihitung dari seluruh invoice berstatus "Lunas"
- **Grafik pendapatan per bulan** — melihat tren naik/turun
- **Piutang (belum lunas)** — total nominal yang masih outstanding dari invoice "Belum Bayar"/"DP Terbayar"
- **Breakdown pendapatan per kategori jasa** — misal "Joki Tugas: Rp X", "Jasa Website: Rp Y"
- **Jumlah order per bulan** — berapa invoice yang closing tiap bulannya

### 4.5 Autentikasi (Opsional)
- Karena aplikasi hanya dijalankan & diakses lokal oleh admin sendiri, **login bersifat opsional di v1**
- Kalau suatu saat admin ingin menambahkan lock sederhana (misal laptop sering dipakai orang lain), bisa ditambahkan kapan saja lewat Laravel Breeze tanpa mengubah struktur data

## 5. Alur Pengguna (ringkas)
1. Admin buka aplikasi secara lokal (`php artisan serve`, akses via `localhost`)
2. (Sekali di awal, atau kapan saja) Admin tambah kategori jasa baru kalau diperlukan
3. Admin buat invoice baru, pilih kategori & jenis pembayaran (DP atau lunas langsung)
4. Admin export invoice sebagai PDF, lalu kirim manual ke klien via WhatsApp/chat
5. Klien buka PDF → lihat detail invoice + QRIS → transfer sesuai nominal
6. Admin cek mutasi manual, lalu update status invoice di aplikasi
7. Dashboard penghasilan otomatis ter-update begitu status invoice berubah jadi "Lunas"
8. Setelah project selesai, admin minta 4 jenis bukti ke klien (tugas, chat, hasil, pelunasan), lalu upload lewat aplikasi
9. Sistem otomatis susun jadi 1 gambar kolase → ke-post ke channel Telegram (etalase publik testimoni)

## 6. Di Luar Cakupan (v1 — belum dikerjakan dulu)
- Hosting/deployment publik — v1 cuma jalan lokal, klien tidak mengakses aplikasi
- Payment gateway / QRIS dinamis dengan konfirmasi pembayaran otomatis
- Login/akun untuk klien
- Notifikasi WhatsApp otomatis
- Multi-admin / multi-user
- Approval testimoni sebelum tayang
- Custom field berbeda per kategori — v1 masih pakai deskripsi bebas untuk semua kategori
- Label teks otomatis di atas tiap gambar dalam kolase (v1: hanya gambar disusun grid, tanpa overlay teks)
- Halaman galeri testimoni terpisah — cukup diwakili oleh channel Telegram sebagai etalase publik

## 7. Metrik Keberhasilan (informal, buat evaluasi sendiri)
- Waktu pembuatan invoice + export PDF di bawah 2 menit
- Testimoni otomatis tersusun jadi kolase & masuk Telegram tanpa langkah manual tambahan
- File PDF invoice terlihat rapi & profesional saat dikirim ke klien
- Dashboard penghasilan langsung ter-update setiap status invoice berubah
- Bisa menambah kategori jasa baru tanpa bantuan developer/ubah kode
- Alur "lunas langsung" nggak nampilin field DP yang membingungkan di invoice
- Aplikasi tetap jalan lancar tanpa biaya hosting bulanan
