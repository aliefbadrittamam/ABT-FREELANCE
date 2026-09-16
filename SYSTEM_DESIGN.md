# 🏛️ SYSTEM DESIGN & ARCHITECTURE DOCUMENT
## **ABT-FREELANCE (ABTJOKI ECOSYSTEM)**
> *Comprehensive Technical Architecture, Database Schema, Workflow Pipeline, Telegram Bot Engine, and Component Specifications for Freelance Invoice, Testimonial, Marketing Hub, and eFootball Tournament Module.*

---

## 📑 DAFTAR ISI (TABLE OF CONTENTS)
1. [Overview & System Architecture](#1-overview--system-architecture)
2. [Tech Stack & Infrastructure](#2-tech-stack--infrastructure)
3. [Database Schema & Entity Relationship Diagram (ERD)](#3-database-schema--entity-relationship-diagram-erd)
4. [Core Modules & Technical Specifications](#4-core-modules--technical-specifications)
   - [4.1 Invoice & Real Cash-Flow Accounting System](#41-invoice--real-cash-flow-accounting-system)
   - [4.2 Hunter & Worker Profit Sharing System](#42-hunter--worker-profit-sharing-system)
   - [4.3 Testimonial Engine & Dynamic Image Composer](#43-testimonial-engine--dynamic-image-composer)
   - [4.4 Marketing Hub & Auto-Banner Generator](#44-marketing-hub--auto-banner-generator)
   - [4.5 eFootball Tournament Engine (Fastur & Custom Cup)](#45-efootball-tournament-engine-fastur--custom-cup)
   - [4.6 Telegram Bot Gateway (@abt_joki_bot & @abt_tournament_efootball_bot)](#46-telegram-bot-gateway-abt_joki_bot--abt_tournament_efootball_bot)
5. [Security, Authentication & Role Matrix](#5-security-authentication--role-matrix)
6. [API & Webhook Endpoints](#6-api--webhook-endpoints)
7. [Deployment & Production Setup](#7-deployment--production-setup)

---

## 1. OVERVIEW & SYSTEM ARCHITECTURE

**ABT-FREELANCE** adalah platform manajemen operasional *all-in-one* yang dirancang khusus untuk memfasilitasi bisnis jasa freelance akademik (*joki tugas, bimbingan skripsi, olah data, pembuatan website, desain grafis*) dan penyelenggara turnamen *eFootball Mobile* (Fastur 4/8 tim & Bagan Sistem Gugur 8-64 tim).

### 📐 High-Level Architecture Diagram
```
                             ┌───────────────────────────────────┐
                             │       USERS / CLIENTS / ADMIN     │
                             └─────────────────┬─────────────────┘
                                               │
               ┌───────────────────────────────┼───────────────────────────────┐
               ▼                               ▼                               ▼
     ┌──────────────────┐            ┌──────────────────┐            ┌──────────────────┐
     │   ADMIN WEB UI   │            │ CLIENT VIEW PORTAL│           │  TELEGRAM BOTS   │
     │   (/, /invoices, │            │  (/i/{token},    │           │  (@abt_joki_bot, │
     │   /testimonials) │            │  /turnamen/live) │           │  @tournament_bot)│
     └─────────┬────────┘            └─────────┬────────┘            └─────────┬────────┘
               │                               │                               │
               └───────────────────────────────┼───────────────────────────────┘
                                               │
                                               ▼
                             ┌───────────────────────────────────┐
                             │       LARAVEL 11 ROUTING CORE     │
                             │  (Middlewares: auth, throttle,    │
                             │   csrf, signed, web, api)         │
                             └─────────────────┬─────────────────┘
                                               │
         ┌──────────────────┬──────────────────┼──────────────────┬──────────────────┐
         ▼                  ▼                  ▼                  ▼                  ▼
  ┌──────────────┐   ┌──────────────┐   ┌──────────────┐   ┌──────────────┐   ┌──────────────┐
  │ Invoice      │   │ Testimonial  │   │ Marketing Hub│   │ Tournament   │   │ Payment /    │
  │ Controller   │   │ Controller   │   │ Controller   │   │ Controller   │   │ Fonnte WA    │
  └──────┬───────┘   └──────┬───────┘   └──────┬───────┘   └──────┬───────┘   └──────┬───────┘
         │                  │                  │                  │                  │
         ▼                  ▼                  ▼                  ▼                  ▼
  ┌──────────────┐   ┌──────────────┐   ┌──────────────┐   ┌──────────────┐   ┌──────────────┐
  │ Puppeteer    │   │ Intervention │   │ Banner       │   │ Bracket      │   │ Telegram &   │
  │ Chrome (PDF) │   │ Image v3 GD  │   │ Generator    │   │ Engine (Tree)│   │ Fonnte API   │
  └──────┬───────┘   └──────┬───────┘   └──────┬───────┘   └──────┬───────┘   └──────┬───────┘
         │                  │                  │                  │                  │
         └──────────────────┴──────────────────┼──────────────────┴──────────────────┘
                                               │
                                               ▼
                             ┌───────────────────────────────────┐
                             │       STORAGE & PERSISTENCE       │
                             │   MySQL Database (abt_freelance)  │
                             │   Local Storage (/storage/public) │
                             └───────────────────────────────────┘
```

---

## 2. TECH STACK & INFRASTRUCTURE

| Komponen | Teknologi | Keterangan / Versi |
|---|---|---|
| **Framework Backend** | Laravel 11.x | PHP 8.2+ (Strict Types, Eloquent ORM) |
| **Database** | MySQL 8.0.x | InnoDB Engine, utf8mb4 collation |
| **Frontend Styling** | Tailwind CSS 3.x | Custom Dark Mode (`#0c0d10`, `#121212`, `#1e1e1e`), Neon (`#E8FF00`) |
| **Reactivity & UI** | Alpine.js 3.x | Modal toggle, copy-to-clipboard, image paste, dynamic filter |
| **Image Processing** | Intervention Image v3 | GD Driver (Dynamic Collage, Alpha Masking, Custom Banner Generator) |
| **Document Renderer** | Puppeteer Chrome Headless | `render_image.mjs` (PNG HD) & `render_pdf.mjs` / DomPDF (PDF A4 2-Page) |
| **Bot Gateway** | Telegram Bot API | Webhook & Long Polling support (`@abt_joki_bot`, `@abt_tournament_efootball_bot`) |
| **WhatsApp Gateway** | Fonnte API | Direct HTTP API & Smart Localhost Fallback (`api.whatsapp.com`) |

---

## 3. DATABASE SCHEMA & ENTITY RELATIONSHIP DIAGRAM (ERD)

```
  ┌────────────────────────┐         1:N         ┌────────────────────────┐
  │       CATEGORIES       │ ─────────────────── │     SUB_CATEGORIES     │
  ├────────────────────────┤                     ├────────────────────────┤
  │ id (PK)                │                     │ id (PK)                │
  │ name                   │                     │ category_id (FK)       │
  │ invoice_prefix         │                     │ name                   │
  │ brand_name             │                     └────────────────────────┘
  │ tagline                │
  └───────────┬────────────┘
              │ 1:N
              │
              │                      1:N         ┌────────────────────────┐
              ├───────────────────────────────── │       PROMOTIONS       │
              │                                  ├────────────────────────┤
              │                                  │ id (PK)                │
              │                                  │ category_id (FK, null) │
              │                                  │ category_type          │
              │                                  │ title                  │
              │                                  │ tagline                │
              │                                  │ banner_path            │
              │                                  │ banner_type            │
              │                                  │ copywriting            │
              │                                  │ is_active              │
              │                                  └────────────────────────┘
              │ 1:N
              ▼
  ┌────────────────────────┐         1:1         ┌────────────────────────┐
  │        INVOICES        │ ─────────────────── │      TESTIMONIALS      │
  ├────────────────────────┤                     ├────────────────────────┤
  │ id (PK)                │                     │ id (PK)                │
  │ invoice_number (UQ)    │                     │ invoice_id (FK, null)  │
  │ access_token (UQ)      │                     │ testimonial_number (UQ)│
  │ client_name            │                     │ major                  │
  │ category_id (FK)       │                     │ task_title             │
  │ sub_category_id (FK)   │                     │ deliverables           │
  │ sub_category_custom    │                     │ image_tugas_path       │
  │ major_id (FK)          │                     │ image_chat_path        │
  │ major_custom           │                     │ image_hasil_path       │
  │ description            │                     │ image_pelunasan_path   │
  │ deadline               │                     │ composed_image_path    │
  │ payment_type (dp/full) │                     │ caption                │
  │ dp_amount              │                     │ client_name            │
  │ dp_paid_at             │                     │ posted_to_telegram     │
  │ total_amount           │                     │ telegram_message_id    │
  │ status                 │                     │ deleted_at (SoftDelete)│
  │ paid_at                │                     └────────────────────────┘
  │ has_worker             │
  │ my_role (hunter/worker)│         1:N         ┌────────────────────────┐
  │ worker_percentage      │ ─────────────────── │         MAJORS         │
  │ my_share_amount        │ (major_id FK)       ├────────────────────────┤
  │ partner_share_amount   │                     │ id (PK)                │
  │ payout_status          │                     │ name (UQ)              │
  │ payout_at              │                     └────────────────────────┘
  └────────────────────────┘

  ┌────────────────────────┐         1:N         ┌────────────────────────┐
  │      TOURNAMENTS       │ ─────────────────── │TOURNAMENT_PARTICIPANTS │
  ├────────────────────────┤                     ├────────────────────────┤
  │ id (PK)                │                     │ id (PK)                │
  │ name                   │                     │ tournament_id (FK)     │
  │ type (fastur/custom)   │                     │ slot_number            │
  │ max_slots (4/8/16/32)  │                     │ team_name              │
  │ registration_fee       │                     │ whatsapp_number        │
  │ prize_first_place      │                     │ status (pending/paid)  │
  │ admin_profit           │                     │ payment_proof_path     │
  │ status (open/ongoing..)│                     └────────────────────────┘
  │ live_link              │         1:N         ┌────────────────────────┐
  │ winner_participant_id  │ ─────────────────── │   TOURNAMENT_MATCHES   │
  └────────────────────────┘                     ├────────────────────────┤
                                                 │ id (PK)                │
                                                 │ tournament_id (FK)     │
                                                 │ round (1,2,3,semi,fin) │
                                                 │ match_number           │
                                                 │ participant1_id (FK)   │
                                                 │ participant2_id (FK)   │
                                                 │ score_participant1     │
                                                 │ score_participant2     │
                                                 │ winner_id (FK)         │
                                                 │ status (pending/ready) │
                                                 └────────────────────────┘

  ┌────────────────────────┐
  │    PAYMENT_SETTINGS    │
  ├────────────────────────┤
  │ id (PK)                │
  │ bank_info              │
  │ qris_image_path        │
  │ default_tournament_... │
  │ fonnte_token           │
  └────────────────────────┘
```

---

## 4. CORE MODULES & TECHNICAL SPECIFICATIONS

### 4.1 Invoice & Real Cash-Flow Accounting System
* **Format Penomoran Cyber Timestamp**:
  `INV-[PREFIX]-[SEQUENCE]-[YYMMDD]-[HHMMSS]`
  *Contoh*: `INV-JOKI-082-260904-181514` (Prefix otomatis dari Kategori Jasa).
* **Real Cash-Flow Tracking**:
  - `dp_paid_at`: Mencatat waktu uang muka (DP) diterima.
  - `paid_at`: Mencatat waktu sisa pelunasan diselesaikan.
  - Saat invoice berstatus `dp_paid` beralih ke `paid`, pencatatan kas masuk pada hari pelunasan **hanya menghitung sisa tagihan** (`total_amount - dp_amount`), mencegah pencatatan omzet ganda (*double-counting*).
* **Smart URL Resolution (`isLocal()` vs Hosted)**:
  - Pada lingkungan lokal (`localhost` / `127.0.0.1`), sistem otomatis menyembunyikan URL portal klien dari template chat WhatsApp dan menyertakan rekening pembayaran bank resmi.
  - Saat aplikasi di-hosting pada domain publik, link portal `/i/{token}` otomatis aktif.

### 4.2 Hunter & Worker Profit Sharing System
* **Struktur Bagi Hasil 80/20**:
  - **Admin sebagai Hunter**: Admin mengambil komisi 20% (`hunter_percentage`), mitra joki luar mengambil 80% (`worker_percentage`).
  - **Admin sebagai Worker**: Admin mengambil 80%, hunter luar mengambil 20%.
  - Opsi persentase dapat dikustomisasi (1% s/d 99%).
* **Payout Status Tracking**:
  - Setiap invoice mitra memiliki status pencairan fee: `unpaid` / `paid` beserta timestamp `payout_at`.
  - Tombol aksi 1-klik untuk konfirmasi transfer fee via WhatsApp mitra.

### 4.3 Testimonial Engine & Dynamic Image Composer
* **Dynamic Image Composition (`TestimonialComposer.php`)**:
  - Menggabungkan 1 hingga 4 foto bukti screenshot tanpa cropping (`contain()` scaling).
  - Background: **Midnight Obsidian Dark (`#0C0D10`)** + **Tech Grid 60px** + **Pola Watermark `ABTJOKI`**.
  - Frame Foto: **Border Neon Yellow 3px (`#E8FF00`)** dengan latar dalam putih bersih (`#ffffff`).
  - Header Info: **Kiri: `#XX` (Nomor Testi)** | **Tengah: `ABTJOKI`** | **Kanan: Nomor Invoice**.
* **Fitur Tambahan**:
  - **Direct Clipboard Paste (`Ctrl + V`)**: Menempel screenshot langsung dari clipboard tanpa perlu menyimpan file.
  - **Auto-Advancing Active Slot**: Slot foto aktif otomatis bergeser ke slot kosong berikutnya setelah paste.
  - **Draft Mode & Restore Lock**: SoftDeletes dengan kunci penghapusan 7 hari (`isDeletable()`).

### 4.4 Marketing Hub & Auto-Banner Generator
* **Auto-Banner Generator (`PromotionBannerGenerator.php`)**:
  - Me-render poster promosi HD 1080x1080 bertema Dark-Neon ABT secara otomatis dari database tanpa aplikasi editing luar.
  - Menghasilkan judul promosi berukuran besar, list poin keunggulan, dan footer CTA WhatsApp.
* **Aksi Cepat 1-Klik**:
  - `[ 📋 Salin Teks ]`: Menyalin copywriting promosi lengkap dengan emoji.
  - `[ 🖼️ Unduh Poster ]`: Download banner poster HD.
  - `[ 💬 Buka WhatsApp ]`: Membuka chat WA dengan teks terisi otomatis.
  - `[ 🚀 Posting ke Channel ]`: 1-klik terbit ke Channel Telegram `@ABT_TESTIMONI`.

### 4.5 eFootball Tournament Engine (Fastur & Custom Cup)
* **Sesi Fastur (4 & 8 Slot)**:
  - Real-time auto-polling `/turnamen/efootball/live/data` tanpa reload halaman.
  - Modal QRIS responsif dengan fullscreen zoom lightbox & download QRIS.
  - Status guard: Turnamen harus dimulai (`ongoing`) sebelum Juara 1 dapat ditentukan via mahkota 👑.
* **Custom Bracket Cup (8, 16, 32, 64 Tim)**:
  - Algoritma bagan sistem gugur otomatis (*Single Elimination Tournament Tree*).
  - Fitur acak bagan (*Randomize / Shuffle Slots*), pencatatan skor, dan auto-advance pemenang ke babak berikutnya.

### 4.6 Telegram Bot Gateway
* **Bot Invoice (`@abt_joki_bot`)**:
  - Wizard interaktif pembuatan invoice 5 langkah.
  - Quick status lookup dengan mengetik nomor (contoh: `86`).
  - Filter status terpadu dalam 1 menu: **`[ 💳 DP Terbayar ]`**, **`[ ⏳ Belum Bayar ]`**, **`[ ✅ Lunas ]`**, dan **`[ 📋 Semua ]`**.
  - Switch metode pembayaran instan (**`[ 🔄 Ubah ke Full Pay ]`** ↔ **`[ 💳 Ubah ke DP 50% ]`**).
  - Pembuatan testimoni langsung dari invoice dengan mengirim 1-4 foto di chat bot.
  - Pengiriman file invoice PDF dan PNG HD langsung ke chat.
* **Bot Turnamen (`@abt_tournament_efootball_bot`)**:
  - Buka sesi fastur, kelola slot pendaftar, mulai laga, dan tentukan juara.

---

## 5. SECURITY, AUTHENTICATION & ROLE MATRIX

```
┌────────────────────────┬─────────────┬──────────────┬────────────────────────┐
│ Endpoint / Resource    │ Role Akses  │ Auth Guard   │ Rate Limiter           │
├────────────────────────┼─────────────┼──────────────┼────────────────────────┤
│ /login                 │ Guest       │ guest        │ 5 requests / minute    │
│ / (Dashboard)          │ Admin       │ auth         │ Standard web session   │
│ /invoices/*            │ Admin       │ auth         │ Standard web session   │
│ /testimonials/*        │ Admin       │ auth         │ Standard web session   │
│ /promotions/*          │ Admin       │ auth         │ Standard web session   │
│ /categories/*          │ Admin       │ auth         │ Standard web session   │
│ /payment/*             │ Admin       │ auth         │ Standard web session   │
│ /tour-organizer/*      │ Admin       │ auth         │ Standard web session   │
│ /i/{token} (Portal)    │ Public      │ none (token) │ 60 requests / minute   │
│ /turnamen/efootball/.. │ Public      │ none         │ 120 requests / minute  │
│ /api/telegram/webhook  │ Telegram IP │ none (Secret)│ Unrestricted webhook   │
└────────────────────────┴─────────────┴──────────────┴────────────────────────┘
```

* **Access Token Security**: Setiap invoice diproteksi dengan 32-karakter acak cryptographically secure token (`Str::random(32)`).
* **7-Day Deletion Lock**: Testimoni yang telah berusia lebih dari 7 hari dikunci dari penghapusan permanen tidak disengaja.

---

## 6. API & WEBHOOK ENDPOINTS

| HTTP Method | URI | Controller Action | Deskripsi |
|---|---|---|---|
| `POST` | `/api/telegram/webhook` | `TelegramWebhookController@handle` | Webhook receiver Bot Invoice |
| `POST` | `/api/tournament/webhook` | `TournamentWebhookController@handle` | Webhook receiver Bot Turnamen |
| `GET` | `/turnamen/efootball/live/data` | Closure (`routes/web.php`) | JSON endpoint auto-polling live slot |
| `GET` | `/i/{token}` | `ClientInvoiceController@show` | Portal publik tampilan invoice klien |
| `GET` | `/i/{token}/export/{format}` | `ClientInvoiceController@export` | Download PNG / PDF klien |
| `POST` | `/invoices/{invoice}/task-file`| `InvoiceController@uploadTaskFile` | Upload file tugas ke storage |
| `GET` | `/promotions/{promotion}/download-banner` | `PromotionController@downloadBanner` | Download banner promosi HD |
| `POST` | `/promotions/{promotion}/post-telegram` | `PromotionController@postToTelegram` | Posting iklan ke Channel Telegram |

---

## 7. DEPLOYMENT & PRODUCTION SETUP

### 🚀 Production Checklist:
1. **Environment Configuration (`.env`)**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_secure_password
   
   TELEGRAM_BOT_TOKEN=8873740744:AAFSzVw1MGaS5YQYtLnSah_GoaKn-5wk4jE
   TELEGRAM_CHANNEL_ID=@ABT_TESTIMONI
   TELEGRAM_TOURNAMENT_BOT_TOKEN=8543693371:AAHxQydMIHhknAN07xlJy0XTRHzYazmjg8I
   ```
2. **Storage Symlink**:
   ```bash
   php artisan storage:link
   ```
3. **Telegram Webhook Registration**:
   ```bash
   php artisan telegram:set-webhook https://yourdomain.com
   ```
4. **Optimization Commands**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---
*Dokumen ini dibuat dan divalidasi untuk ekosistem **ABT-FREELANCE (ABTJOKI)** — Hak Cipta & Hak Milik: Alief Badrit Tamam.*
