# 00. OVERVIEW & ARSITEKTUR BISNIS MODUL TURNAMEN eFOOTBALL MOBILE

Dokumen ini adalah cetak biru (*blueprint*) dan acuan konsep bisnis untuk modul **Tour Organizer > eFootball Mobile** di dalam ekosistem aplikasi **ABT-FREELANCE**.

---

## 🎯 1. Konsep & Model Bisnis

Turnamen eFootball Mobile ini diselenggarakan dengan sistem **Sesi Cepat / Paralel (Bukan Jadwal Turnamen Panjang)**:
1. **Model Sesi (Session-based)**:
   - Turnamen berjalan per sesi (contoh: *Sesi 1*, *Sesi 2*, *Sesi 3*).
   - Dapat diadakan kapan saja secara dadakan (*fast tournament* / *inrush*) begitu slot penuh.
   - Sesi dapat di-reset atau dibuat baru setiap hari.
2. **Sesi Paralel (Concurrent Sessions)**:
   - Admin dapat membuka beberapa sesi sekaligus yang berjalan bersamaan (misal: Sesi 1 regis 5K dan Sesi 2 regis 10K buka berbarengan).
3. **Kapasitas Slot Tim**:
   - **Default: 8 Tim (8 Slot)**.
   - **Opsi Tambahan: 4 Tim (4 Slot)** (untuk format turnamen mini/kilat).
4. **Struktur Hadiah & Profit Admin**:
   - **Hanya Juara 1** yang mendapatkan hadiah (*Winner Takes All*).
   - Profit admin diambil langsung dari selisih total uang registrasi peserta dikurangi hadiah juara 1.
   - Skema Default:

| Skema Biaya | Biaya Regis / Tim | Total Pemasukan (8 Tim) | Hadiah Juara 1 | Laba Bersih Admin |
| :--- | :--- | :--- | :--- | :--- |
| **5K Get 30K** | Rp 5.000 | Rp 40.000 | Rp 30.000 | **Rp 10.000** |
| **10K Get 60K** | Rp 10.000 | Rp 80.000 | Rp 60.000 | **Rp 20.000** |
| **15K Get 95K** | Rp 15.000 | Rp 120.000 | Rp 95.000 | **Rp 25.000** |
| **20K Get 130K** | Rp 20.000 | Rp 160.000 | Rp 130.000 | **Rp 30.000** |
| **Custom** | Bebas | `Regis * Slot` | Ditentukan Admin | `Pemasukan - Hadiah` |

---

## 👥 2. Alur Pendaftaran & Pembayaran

1. **Prinsip Pendaftaran**:
   - Input data peserta dibuat sangat simpel: **Nama Tim** (wajib) dan **Kontak WhatsApp** (opsional).
   - Tidak perlu nama kapten atau data berbelit.
   - **Prinsip Masuk Slot = Sudah Bayar**: Ketika admin memasukkan tim ke dalam slot nomor 1-8, itu berarti tim tersebut sudah membayar biaya registrasi via Transfer Bank / QRIS.
2. **Tanpa Invoice Terpisah**:
   - Transaksi turnamen tidak memerlukan invoice formal per tim agar alur cepat dan ringan.
   - Pembayaran menggunakan rekening bank & QRIS resmi yang sudah ada di ABT-FREELANCE (BCA, DANA, SeaBank, QRIS statis 0% fee).
3. **Pencatatan Keuangan Bersih**:
   - Laba bersih admin otomatis tercatat di dashboard turnamen saat turnamen diselesaikan.

---

## 📢 3. Format Pesan Broadcast WhatsApp (1-Klik Copy)

Sistem secara otomatis mengenerate format teks yang siap di-copy dan dipaste ke grup WhatsApp / komunitas eFootball setiap kali ada slot yang terisi:

```text
🏆 TURNAMEN eFOOTBALL MOBILE (5K GET 30K)
📌 Sesi 1
💰 Biaya Registrasi: Rp 5.000 / Tim
🎁 Hadiah Juara 1: Rp 30.000

✅ DAFTAR SLOT PESERTA:
1. GARUDA ESPORT ✅
2. BARCELONA REBORN ✅
3. NAGA HITAM FC ✅
4. [ KOSONG ]
5. [ KOSONG ]
6. [ KOSONG ]
7. [ KOSONG ]
8. [ KOSONG ]

📢 Sisa 5 Slot Lagi!
💬 Hubungi Admin untuk registrasi & kunci slot!
```

---

## 🏆 4. Penentuan Juara & Hadiah

1. **Bracket Dalam Game**:
   - Sistem bracket dan pertandingan sudah diatur langsung di dalam game eFootball Mobile oleh para peserta/admin room, sehingga web sistem tidak perlu mengelola bracket pertandingan.
2. **Tandai Juara 1 (1-Klik)**:
   - Pada tabel peserta sesi, admin cukup menekan tombol **`[ 🏆 Tandai Juara 1 ]`** pada tim pemenang.
   - Tim langsung mendapatkan lencana emas `🏆 JUARA 1`.
3. **Template Pesan Konfirmasi Hadiah ke Pemenang**:
   - Sistem otomatis menghasilkan draf pesan WhatsApp ke kontak juara:
     ```text
     Halo [Nama Tim], Selamat telah menjadi JUARA 1 pada Turnamen eFootball Sesi [X]!
     Hadiah sebesar Rp [Nominal Hadiah] akan segera ditransfer.
     Silakan kirimkan nomor rekening Bank / E-Wallet Anda. Terima kasih!
     ```
4. **Bukti Transfer Hadiah**:
   - Admin memiliki opsi untuk mengunggah foto screenshot bukti transfer hadiah ke pemenang sebagai arsip transparansi turnamen.

---

## 🌐 5. Halaman Publik Live (`/turnamen/efootball/live`)

- Halaman publik tanpa login yang dirancang khusus dengan tema *High-Voltage Neon Grid* yang futuristik.
- Menampilkan tabel responsif seluruh sesi yang sedang **BUKA (`OPEN`)** atau **PENUH (`FULL`)**.
- Peserta dapat melihat secara langsung slot mana saja yang sudah terisi dan berapa slot yang masih tersisa secara real-time.
- Siap dibagikan ke publik menggunakan **Ngrok** (`https://xxxx.ngrok-free.app/turnamen/efootball/live`) dari laptop lokal.

---

## 🤖 6. Bot Telegram Khusus Turnamen (`@abt_efootballTournament_bot`)

- Menggunakan Bot Telegram terpisah dari bot invoice untuk memisahkan operasional jasa tugas dan turnamen gaming.
- Dilengkapi command listener mandiri: `php artisan tournament:listen`.
- Memungkinkan admin membuat sesi, menambahkan tim ke slot, melihat sisa slot, dan menandai juara langsung dari Telegram HP.

---

## 🗺️ Roadmap File Panduan Prompt:

| File | Konten Prompt |
| :--- | :--- |
| `01_DATABASE_DAN_MODEL.md` | Migrasi tabel `tournaments` & `tournament_participants` + Model & Relasi |
| `02_ADMIN_BACKEND_CONTROLLER.md` | Controller CRUD Sesi, Tambah Tim, Set Juara, Complete, & Broadcast |
| `03_VIEWS_ADMIN_DASHBOARD.md` | Blade View Admin: Index Sesi, Form Preset Cepat, Show Slot 1-8, Modal Tambah Tim |
| `04_HALAMAN_PUBLIK_LIVE.md` | Blade View Publik `/turnamen/efootball/live` bertema neon responsif |
| `05_INTEGRASI_TELEGRAM_BOT.md` | Service & Command Bot Telegram Turnamen Terpisah |
| `06_PANDUAN_TESTING_DAN_NGROK.md` | Panduan menjalankan Ngrok, testing end-to-end, dan checklist 0-error |
