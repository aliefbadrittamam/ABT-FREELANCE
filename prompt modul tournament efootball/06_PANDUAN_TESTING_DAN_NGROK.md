# 06. PROMPT 6: PANDUAN TESTING END-TO-END & SHARING PUBLIK DENGAN NGROK

Dokumen ini berisi panduan langkah demi langkah untuk menjalankan tunnel Ngrok dari laptop lokal ke publik, membagikan link live slot ke para peserta di grup WhatsApp, serta checklist pengujian menyeluruh agar implementasi berjalan 100% tanpa error.

---

## 🌐 1. Panduan Menjalankan Ngrok untuk Akses Publik

Karena aplikasi berjalan di laptop lokal (`localhost:8000`), kita menggunakan **Ngrok** agar halaman live slot turnamen dapat dibuka oleh siapa saja dari HP / internet luar tanpa perlu sewa VPS/hosting terlebih dahulu.

### Langkah Setup & Eksekusi Ngrok:

1. **Pastikan Laravel Server Berjalan**:
   Di terminal 1 (folder `abt-app`):
   ```bash
   php artisan serve
   ```
   *(Server berjalan di `http://127.0.0.1:8000`).*

2. **Jalankan Ngrok Tunnel**:
   Buka terminal baru / PowerShell terpisah dan ketik:
   ```bash
   ngrok http 8000
   ```
   
3. **Dapatkan URL Publik HTTPS**:
   Ngrok akan menampilkan output seperti ini di terminal:
   ```text
   Session Status                online
   Forwarding                    https://a1b2-182-1-2-3.ngrok-free.app -> http://localhost:8000
   ```
   Salin link `https://...ngrok-free.app` tersebut.

4. **Bentuk Link Live Slot untuk Komunitas WhatsApp**:
   Gabungkan link Ngrok dengan route publik turnamen:
   👉 **`https://a1b2-182-1-2-3.ngrok-free.app/turnamen/efootball/live`**

5. **Kirimkan ke Grup WhatsApp**:
   Format pesan siap kirim:
   ```text
   🏆 LIVE MONITORING SLOT TURNAMEN eFOOTBALL ABT
   
   Pantau ketersediaan slot turnamen secara real-time di link berikut:
   👉 https://a1b2-182-1-2-3.ngrok-free.app/turnamen/efootball/live
   
   Slot terbatas! Hubungi Admin untuk registrasi & kunci tim kamu.
   ```

---

## 🧪 2. Checklist Pengujian Menyeluruh (Zero-Error Verification)

Lakukan pengujian berikut secara bertahap:

### ✅ Skenario 1: Pembuatan Sesi Baru
- [ ] Buka `http://localhost:8000/tour-organizer/efootball`.
- [ ] Klik **`Buat Sesi Baru`**.
- [ ] Klik tombol preset **`[ 5K Get 30K ]`**.
- [ ] Pastikan biaya terisi `5.000`, hadiah `30.000`, estimasi profit `10.000`.
- [ ] Klik **`Buka Sesi Turnamen`**.
- [ ] Hasil yang diharapkan: Sesi langsung terdaftar dengan status `OPEN` dan 8 slot kosong.

### ✅ Skenario 2: Pendaftaran Tim ke Slot
- [ ] Pada baris Slot #1, klik **`[ Isi Slot ]`**.
- [ ] Masukkan Nama Tim: `GARUDA FC`, No WA: `08123456789`.
- [ ] Klik Simpan.
- [ ] Hasil yang diharapkan: Slot #1 terisi `GARUDA FC`, status slot menjadi `1/8 Tim Terisi`.

### ✅ Skenario 3: Uji Fitur Salin Broadcast WA
- [ ] Klik tombol **`[ 📋 Salin Broadcast WA ]`**.
- [ ] Buka Notepad / WA, lalu tekan `Ctrl + V` (Paste).
- [ ] Hasil yang diharapkan: Format teks broadcast langsung ter-paste dengan Slot 1 bertanda `GARUDA FC ✅` dan slot 2-8 bertanda `[ KOSONG ]`.

### ✅ Skenario 4: Pengisian Penuh & Perubahan Status Otomatis
- [ ] Isi sisa slot sampai penuh 8 tim.
- [ ] Hasil yang diharapkan: Status turnamen otomatis berubah dari `OPEN` menjadi **`PENUH`** (warna merah/pink).

### ✅ Skenario 5: Penentuan Juara 1
- [ ] Klik tombol piala **`[ 🏆 Tandai Sebagai Juara 1 ]`** pada Tim Slot #1.
- [ ] Hasil yang diharapkan: Tim Slot #1 mendapatkan badge emas `JUARA 1`, muncul box template WhatsApp konfirmasi hadiah siap copy untuk dikirim ke sang juara.

### ✅ Skenario 6: Penyelesaian Sesi & Rekap Profit
- [ ] Klik tombol **`[ Task Alt: Selesaikan Sesi ]`**.
- [ ] Hasil yang diharapkan: Status berubah menjadi `SELESAI`, profit Rp 10.000 masuk ke kartu ringkasan laba bersih admin, dan turnamen masuk ke tabel riwayat.

### ✅ Skenario 7: Pengujian Sesi Paralel
- [ ] Buat Sesi 2 (10K Get 60K) saat Sesi 1 masih berjalan.
- [ ] Buka halaman publik `/turnamen/efootball/live`.
- [ ] Hasil yang diharapkan: Kedua sesi tampil berdampingan secara rapi dan masing-masing slot dapat dipantau mandiri.

---

## 🎯 3. Tips Praktis untuk Turnamen Sukses

1. **Jaga Konsistensi Preset**: Peserta turnamen eFootball sangat menyukai format ringkas seperti *5K get 30K* atau *10K get 60K* karena nominal terjangkau dan proses cepat.
2. **Kunci Slot Setelah Bukti Transfer Masuk**: Jangan masukkan tim ke slot sebelum dana masuk ke rekening BCA / DANA / QRIS Anda untuk menghindari slot palsu (*ghost slot*).
3. **Manfaatkan Broadcast Real-Time**: Setiap kali ada tim baru yang mendaftar, langsung klik *Salin Broadcast* dan kirim ke grup WhatsApp. Ini memicu rasa FOMO (*Fear of Missing Out*) peserta lain untuk segera mendaftar sebelum slot habis.
