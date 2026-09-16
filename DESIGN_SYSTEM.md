# ⚡ ABT-FREELANCE UI/UX DESIGN SYSTEM
## **Single Source of Truth (SSOT) for Visual Design, Theme Tokens, Layouts, and Component Guidelines**
> *Pedoman standar desain visual antarmuka (UI/UX) untuk menjaga konsistensi warna, tipografi, komponen, layout, dan branding pada seluruh modul aplikasi saat ini dan pengembangan aplikasi di masa depan.*

---

## 📑 DAFTAR ISI (TABLE OF CONTENTS)
1. [Filosofi & Identitas Visual (Brand Identity)](#1-filosofi--identitas-visual-brand-identity)
2. [Sistem Warna (Color Palette & Tokens)](#2-sistem-warna-color-palette--tokens)
   - [2.1 Dark Mode Obsidian Palette (Primary / Default)](#21-dark-mode-obsidian-palette-primary--default)
   - [2.2 Light Mode Palette](#22-light-mode-palette)
   - [2.3 Accent & Neon Voltage Highlights](#23-accent--neon-voltage-highlights)
   - [2.4 Semantic Status Colors](#24-semantic-status-colors)
   - [2.5 Platform & Third-Party Brand Colors](#25-platform--third-party-brand-colors)
3. [Tipografi & Font Hierarchy (Typography)](#3-tipografi--font-hierarchy-typography)
   - [3.1 Font Family](#31-font-family)
   - [3.2 Type Scale & Heading Hierarchy](#32-type-scale--heading-hierarchy)
   - [3.3 Iconography Standards](#33-iconography-standards)
4. [Struktur Layout, Grid & Ornamen Teknis](#4-struktur-layout-grid--ornamen-teknis)
   - [4.1 Tech Grid Pattern Background](#41-tech-grid-pattern-background)
   - [4.2 Cyber Neon Corner Brackets](#42-cyber-neon-corner-brackets)
   - [4.3 Spacing, Border Radius & Elevation](#43-spacing-border-radius--elevation)
5. [Standar Komponen UI (Component Standards)](#5-standar-komponen-ui-component-standards)
   - [5.1 Buttons (Tombol Aksi)](#51-buttons-tombol-aksi)
   - [5.2 Form Inputs, Dropdowns & Textareas](#52-form-inputs-dropdowns--textareas)
   - [5.3 Badges, Chips & Status Pills](#53-badges-chips--status-pills)
   - [5.4 Tables & Data Grids](#54-tables--data-grids)
   - [5.5 Cards & Containers](#55-cards--containers)
   - [5.6 Modals, Lightbox & Drawers](#56-modals-lightbox--drawers)
6. [Pedoman Desain Gambar & Banner (Poster & Composers)](#6-pedoman-desain-gambar--banner-poster--composers)
7. [Aturan Praktis untuk Pembuatan Fitur / Aplikasi Selanjutnya (Do's & Don'ts)](#7-aturan-praktis-untuk-pembuatan-fitur--aplikasi-selanjutnya-dos--donts)

---

## 1. FILOSOFI & IDENTITAS VISUAL (BRAND IDENTITY)

Desain **ABT-FREELANCE (ABTJOKI)** mengusung tema **"High-Voltage Cyberpunk Professional"**:
* **Modern & Eksklusif**: Memadukan warna gelap pekat (*Midnight Obsidian Dark*) dengan aksen **Neon Voltage Yellow (`#E8FF00`)** yang memancarkan energi tinggi, kecepatan, dan ketelitian teknologi.
* **Keterbacaan Maksimal (High Contrast)**: Setiap teks dan angka data finansial memiliki kontras tinggi yang tajam sehingga mudah dibaca di layar HP maupun laptop.
* **Presisi & Kecepatan**: Layout dirancang berbasis *fast-action* dengan tombol 1-klik, keyboard shortcut, dan clipboard paste.

---

## 2. SISTEM WARNA (COLOR PALETTE & TOKENS)

### 2.1 Dark Mode Obsidian Palette (Primary / Default)

| Token Name | Hex Code | Tailwind Equivalent / Usage | Fungsi / Penempatan |
|---|---|---|---|
| `bg-canvas-dark` | `#0c0d10` | `bg-[#0c0d10]` | Background paling dasar kanvas, poster, & body utama |
| `bg-surface-dark` | `#121212` | `bg-[#121212]` | Background halaman utama dark mode |
| `bg-card-dark` | `#1e1e1e` | `bg-[#1e1e1e]` | Background kartu kontainer, tabel, form card |
| `bg-card-header` | `#181818` | `bg-[#181818]` | Background header tabel, input box, live preview |
| `bg-input-dark` | `#252525` | `bg-[#252525]` | Background input text, select option, search bar |
| `border-dark-subtle` | `#2a2a2a` | `border-[#2a2a2a]` | Garis batas card, divider horizontal |
| `border-dark-input` | `#333333` | `border-[#333]` | Garis batas input field, button outline |
| `text-white` | `#ffffff` | `text-white` | Teks judul utama, headline, angka penting |
| `text-gray-light` | `#f0f0f0` | `text-[#f0f0f0]` | Teks body paragraf, deskripsi |
| `text-gray-muted` | `#9ca3af` | `text-gray-400` | Teks keterangan sekunder, timestamp, label kecil |
| `text-gray-subtle` | `#6b7280` | `text-gray-500` | Placeholder, icon non-aktif |

---

### 2.2 Light Mode Palette

| Token Name | Hex Code | Tailwind Class | Fungsi / Penempatan |
|---|---|---|---|
| `bg-surface-light` | `#f9f9f9` | `bg-surface` | Background halaman mode terang |
| `bg-card-light` | `#ffffff` | `bg-white` | Background card, container utama |
| `bg-container-low` | `#f3f3f4` | `bg-surface-container-low` | Background header tabel light mode |
| `border-light-subtle` | `#E4E4E7` | `border-border-subtle` | Garis batas card & tabel light mode |
| `text-on-surface` | `#1a1c1c` | `text-on-surface` | Teks judul & angka utama light mode |
| `text-secondary` | `#5d5e60` | `text-secondary` | Teks sekunder, label formulir |

---

### 2.3 Accent & Neon Voltage Highlights

| Token Name | Hex Code | Tailwind Class | Kegunaan |
|---|---|---|---|
| `primary-container` | `#E8FF00` | `bg-primary-container` / `text-primary-container` | **Warna Brand Utama**: Tombol aksi primer, border frame foto, highlight aktif, corner accents |
| `primary-hover` | `#D9EF00` | `hover:brightness-95` | Efek hover tombol neon kuning |
| `primary-glow` | `rgba(232, 255, 0, 0.2)` | `bg-primary-container/20` | Badge background, ring fokus input, tab aktif |
| `primary-dark-olive` | `#5a6400` | `text-primary` | Warna hijau-zaitun kontras untuk teks di atas latar terang |

---

### 2.4 Semantic Status Colors

| Status Tag | Background (Pill) | Border | Text Color | Icon / Meaning |
|---|---|---|---|---|
| **Lunas (Paid)** | `bg-emerald-500/10` | `border-emerald-500/20` | `text-emerald-500` / `#22C55E` | ✅ Pembayaran tuntas |
| **DP Terbayar (Partial)** | `bg-blue-500/10` | `border-blue-500/20` | `text-blue-500` / `#3B82F6` | 💳 Uang muka terverifikasi |
| **Belum Bayar (Unpaid)** | `bg-amber-500/10` | `border-amber-500/20` | `text-amber-500` / `#F59E0B` | ⏳ Menunggu pembayaran |
| **Dibatalkan (Canceled)** | `bg-gray-500/10` | `border-gray-500/20` | `text-gray-500` / `#6B7280` | ❌ Tagihan dibatalkan |
| **Urgent (< 12 Jam)** | `bg-red-500/10` | `border-red-500/20` | `text-red-500` / `#EF4444` | 🚨 Batas waktu mepet |

---

### 2.5 Platform & Third-Party Brand Colors

| Platform | Hex Code | Tailwind Implementation | Kegunaan |
|---|---|---|---|
| **WhatsApp** | `#25D366` | `bg-[#25D366] text-white` | Tombol kirim pesan / share WhatsApp |
| **Telegram** | `#229ED9` | `bg-[#229ED9] text-white` | Tombol kirim channel / integrasi bot |
| **BCA Bank** | `#0066AE` | Badge / Icon Bank BCA | Informasi rekening transfer |
| **DANA Wallet** | `#118EEA` | Badge / Icon E-Wallet DANA | Informasi rekening transfer |
| **SeaBank** | `#FF5722` | Badge / Icon Bank SeaBank | Informasi rekening transfer |
| **QRIS Nasional**| `#EE3124` | Badge / Frame Modal QRIS | Pembayaran digital QRIS |

---

## 3. TIPOGRAFI & FONT HIERARCHY (TYPOGRAPHY)

### 3.1 Font Family

1. **Primary Interface Font**:
   `font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;`
   - Digunakan untuk: Seluruh teks UI, tombol, label, kartu, tabel, dan heading.
2. **Monospace & Data Font**:
   `font-family: 'JetBrains Mono', 'Fira Code', 'SF Mono', Consolas, monospace;`
   - Digunakan untuk: Nomor invoice (`INV-JOKI-082...`), nominal uang (`Rp 500.000`), kode token (`32-char`), format chat copywriting, live preview.
3. **Iconography**:
   - Google **Material Symbols Outlined**:
     `<span class="material-symbols-outlined">icon_name</span>`
     *Variations: `font-variation-settings: 'FILL' 0, 'wght' 400, 'opsz' 24`*.

---

### 3.2 Type Scale & Heading Hierarchy

| Elemen UI | Font Size | Font Weight | Line Height | Letter Spacing | Tailwind Classes |
|---|---|---|---|---|---|
| **Page Title** | 24px - 30px | 900 (Black) | 1.15 | `-0.025em` | `text-2xl sm:text-[30px] font-black tracking-tight` |
| **Section Header** | 18px - 20px | 800 (Extra Bold) | 1.25 | `-0.015em` | `text-lg sm:text-xl font-extrabold tracking-tight` |
| **Card Title / Subhead** | 14px - 16px | 700 (Bold) | 1.35 | `normal` | `text-sm sm:text-base font-bold text-on-surface dark:text-white` |
| **Form Label** | 11px | 700 (Bold) | 1.4 | `0.05em` | `text-[11px] font-bold uppercase tracking-wider text-secondary dark:text-gray-300` |
| **Body Text** | 13px - 14px | 400 (Regular) / 500 (Medium) | 1.5 | `normal` | `text-xs sm:text-sm text-on-surface dark:text-gray-200` |
| **Badge / Meta Text** | 10px - 11px | 600 (Semi Bold) / 700 (Bold) | 1.2 | `0.025em` | `text-[10px] sm:text-[11px] font-bold` |
| **Display Currency / Big Stats** | 28px - 36px | 900 (Black) | 1.1 | `-0.03em` | `text-2xl sm:text-4xl font-black font-mono text-on-surface dark:text-white` |

---

## 4. STRUKTUR LAYOUT, GRID & ORNAMEN TEKNIS

### 4.1 Tech Grid Pattern Background
Digunakan pada Live Invoice Preview, Dokumen Ekspor PNG/PDF, dan Banner Generator:

```css
/* Tech Grid Pattern CSS */
.invoice-neon-grid {
    background-color: #ffffff;
    background-image: 
        linear-gradient(to right, rgba(232, 255, 0, 0.08) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(232, 255, 0, 0.08) 1px, transparent 1px);
    background-size: 24px 24px;
}

/* Dark Theme Tech Grid */
.dark-tech-grid {
    background-color: #0c0d10;
    background-image: 
        linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
    background-size: 60px 60px;
}
```

---

### 4.2 Cyber Neon Corner Brackets
Aksen sudut siku-siku futuristik yang dipasang di 4 pojok dokumen/kartu:

```css
.neon-corner-tl { 
    position: absolute; top: -1px; left: -1px; width: 14px; height: 14px; 
    border-top: 2px solid rgba(232, 255, 0, 0.8); border-left: 2px solid rgba(232, 255, 0, 0.8); 
}
.neon-corner-tr { 
    position: absolute; top: -1px; right: -1px; width: 14px; height: 14px; 
    border-top: 2px solid rgba(232, 255, 0, 0.8); border-right: 2px solid rgba(232, 255, 0, 0.8); 
}
.neon-corner-bl { 
    position: absolute; bottom: -1px; left: -1px; width: 14px; height: 14px; 
    border-bottom: 2px solid rgba(232, 255, 0, 0.8); border-left: 2px solid rgba(232, 255, 0, 0.8); 
}
.neon-corner-br { 
    position: absolute; bottom: -1px; right: -1px; width: 14px; height: 14px; 
    border-bottom: 2px solid rgba(232, 255, 0, 0.8); border-right: 2px solid rgba(232, 255, 0, 0.8); 
}
```

---

### 4.3 Spacing, Border Radius & Elevation

* **Border Radius Standards**:
  - `rounded-xl` (`12px` / `0.75rem`): Seluruh Card, Container Utama, Form Box, Modal Window, Photo Slot Box.
  - `rounded-lg` (`8px` / `0.5rem`): Seluruh Button, Input Fields, Dropdown Select, Table Rows Hover.
  - `rounded-full` (`9999px`): Seluruh Status Badges, Chips, Notification Counters, Floating Action Buttons.
* **Elevation & Shadows**:
  - `shadow-sm`: Digunakan untuk Card dan Container agar tegas namun tetap *flat-modern*.
  - `shadow-2xs` / `shadow-xs`: Tombol sekunder.
  - `shadow-2xl` / `backdrop-blur-sm`: Modal backdrop & dropdown overlay.

---

## 5. STANDAR KOMPONEN UI (COMPONENT STANDARDS)

### 5.1 Buttons (Tombol Aksi)

```html
<!-- 1. Primary Neon Action Button (Utama) -->
<button class="px-5 py-2.5 bg-primary-container text-on-surface font-bold text-xs sm:text-sm rounded-lg hover:brightness-95 transition shadow-sm flex items-center gap-2">
    <span class="material-symbols-outlined text-base">add</span>
    Simpan & Terbitkan
</button>

<!-- 2. Secondary Neutral / Dark Button (Batal / Filter) -->
<button class="px-4 py-2 bg-gray-100 dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-gray-200 font-bold text-xs rounded-lg hover:bg-gray-200 dark:hover:bg-[#333] transition flex items-center gap-1.5">
    <span class="material-symbols-outlined text-base">save</span>
    Simpan Draft
</button>

<!-- 3. WhatsApp Direct Share Button -->
<a href="https://api.whatsapp.com/send?..." target="_blank" class="px-4 py-2 bg-[#25D366] text-white rounded-lg text-xs font-bold hover:brightness-95 transition flex items-center gap-1.5 shadow-xs">
    <span class="material-symbols-outlined text-sm">chat</span>
    Kirim ke WA
</a>

<!-- 4. Telegram Action Button -->
<button class="px-4 py-2 bg-[#229ED9] text-white rounded-lg text-xs font-bold hover:brightness-95 transition flex items-center gap-1.5">
    <span class="material-symbols-outlined text-sm">send</span>
    Posting ke Channel
</button>

<!-- 5. Danger / Delete Button -->
<button class="p-1.5 text-secondary dark:text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition">
    <span class="material-symbols-outlined text-lg">delete</span>
</button>
```

---

### 5.2 Form Inputs, Dropdowns & Textareas

```html
<!-- Input Text / Select / Textarea Standard -->
<div class="space-y-1.5">
    <label class="block text-[11px] font-bold text-on-surface-variant dark:text-gray-300 uppercase tracking-wider">
        Judul Pekerjaan
    </label>
    <input type="text" class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white font-medium focus:ring-2 focus:ring-primary focus:border-primary outline-none transition placeholder:text-secondary/50">
</div>
```

---

### 5.3 Badges, Chips & Status Pills

```html
<!-- Status Lunas -->
<span class="inline-flex items-center gap-1 text-[11px] font-bold bg-status-lunas/10 text-status-lunas px-2.5 py-0.5 rounded-full border border-status-lunas/20">
    <span class="w-1.5 h-1.5 rounded-full bg-status-lunas"></span> Lunas
</span>

<!-- Status DP Terbayar -->
<span class="inline-flex items-center gap-1 text-[11px] font-bold bg-status-dp/10 text-status-dp px-2.5 py-0.5 rounded-full border border-status-dp/20">
    <span class="w-1.5 h-1.5 rounded-full bg-status-dp"></span> DP Terbayar
</span>

<!-- Status Belum Bayar -->
<span class="inline-flex items-center gap-1 text-[11px] font-bold bg-status-pending/10 text-status-pending px-2.5 py-0.5 rounded-full border border-status-pending/20">
    <span class="w-1.5 h-1.5 rounded-full bg-status-pending"></span> Belum Bayar
</span>

<!-- Code Tag / Sub-Kategori Chip -->
<span class="text-[10px] bg-primary-container/20 text-on-surface dark:text-primary-container px-2 py-0.5 rounded-md font-bold font-mono">
    INV-JOKI-082
</span>
```

---

### 5.4 Tables & Data Grids

* Header tabel: `bg-surface-container-low dark:bg-[#181818] border-b border-border-subtle dark:border-[#2a2a2a] text-[11px] uppercase font-semibold text-secondary dark:text-gray-400`.
* Body row: `hover:bg-surface-variant/30 dark:hover:bg-[#252525] transition-colors text-xs sm:text-sm divide-y divide-border-subtle dark:divide-[#2a2a2a]`.
* Table container wajib membungkus tabel dengan `overflow-x-auto min-w-[640px]` agar mobile responsive tanpa layout pecah.

---

## 6. PEDOMAN DESAIN GAMBAR & BANNER (POSTER & COMPOSERS)

Untuk seluruh file gambar kolase testimoni dan banner promosi yang di-generate via `Intervention Image`:

```
┌───────────────────────────────────────────────────────────────┐
│ #88                       ABTJOKI          INV-JOKI-082...    │ ◄── Top Header Bar (Neon Yellow)
├───────────────────────────────────────────────────────────────┤
│                                                               │
│   ┌───────────────────────────────────────────────────────┐   │
│   │                                                       │   │
│   │                                                       │   │ ◄── 3px Solid Neon Yellow Border
│   │                 SCREENSHOT BUKTI TUGAS                │   │     (#E8FF00) with #ffffff Inner Fill
│   │               (100% Bersih & Tanpa Crop)              │   │
│   │                                                       │   │
│   │                                                       │   │
│   └───────────────────────────────────────────────────────┘   │
│                                                               │
│  [Background: Midnight Dark #0c0d10 + Tech Grid + ABTJOKI WM] │
└───────────────────────────────────────────────────────────────┘
```

* **Dimensi Kanvas Standar**: `1080 x 1080 px` (Square 1:1 Aspect Ratio).
* **Latar Belakang**: Midnight Dark Charcoal `#0c0d10`.
* **Tech Grid Lines**: Garis tipis setiap 60px berjarak warna `#161920`.
* **Pola Watermark Latar**: Tulisan berulang `ABTJOKI` berselang-seling (staggered) dengan warna neon gelap halus `#262c1c`.
* **Bingkai Slot Foto**: Border 3px Solid Neon Yellow (`#E8FF00`) dengan bagian dalam putih bersih (`#ffffff`) agar screenshot WA & bukti transfer tidak terdistorsi warna hitam.
* **Header Bar**:
  - Kiri Atas: `#XX` (Nomor Testimoni).
  - Tengah Atas: `ABTJOKI` (Teks brand neon yellow).
  - Kanan Atas: Nomor Invoice (contoh: `INV-JOKI-082-260904-181514`).
* **Larangan Keras**: DILARANG menaruh logo atau kotak hitam solid di tengah area screenshot agar bukti tidak tertimpa.

---

## 7. ATURAN PRAKTIS UNTUK PEMBUATAN FITUR / APLIKASI SELANJUTNYA (DO'S & DON'TS)

### ✅ Yang WAJIB Dilakukan (DO'S):
1. **Gunakan Palette Resmi**: Selalu gunakan warna token yang terdefinisi di atas (Dark `#1e1e1e`/`#181818`, Neon `#E8FF00`).
2. **Dukungan Dark Mode Penuh**: Setiap class elemen wajib memiliki pasangan mode gelap (contoh: `bg-white dark:bg-[#1e1e1e] text-on-surface dark:text-white border-border-subtle dark:border-[#2a2a2a]`).
3. **Monospace untuk Nomor & Uang**: Selalu gunakan font monospace (`font-mono`) pada penomoran invoice, kode token, dan nominal uang.
4. **Clipboard Copy Toast**: Setiap tombol salin wajib menyertakan feedback visual teks *"Tersalin!"* (menggunakan state Alpine.js `x-data="{ copied: false }"`).
5. **Smart URL Handling**: Selalu gunakan pengecekan `$invoice->isLocal()` sebelum mencantumkan link URL pada format chat WhatsApp (sembunyikan localhost saat di server lokal, aktifkan saat live hosting).
6. **Mobile Responsive First**: Pastikan tabel memiliki `overflow-x-auto` dan tombol aksi menyesuaikan ukuran layar mobile (`text-xs sm:text-sm`).

### ❌ Yang DILARANG (DON'TS):
1. **Jangan Menggunakan Warna Biru Standar untuk Aksi Utama**: Tombol aksi primer harus selalu menggunakan Neon Yellow `bg-primary-container text-on-surface`.
2. **Jangan Meng-Crop Screenshot Klien**: Selalu gunakan CSS `object-contain` dan PHP `contain()` (bukan `cover`) agar screenshot chat dan angka transfer tidak terpotong.
3. **Jangan Menaruh Box / Watermark di Tengah Foto**: Watermark hanya diperbolehkan di background luar atau header atas.
4. **Jangan Hardcode Port / URL Localhost**: Selalu gunakan helper `route()` atau `config('app.url')`.
5. **Jangan Mengubah Skema Warna Sembarangan**: Konsistensi adalah kunci profesionalisme brand ABT-FREELANCE.

---

*Dokumen Design System ini adalah Pedoman Resmi & Single Source of Truth (SSOT) untuk seluruh ekosistem **ABT-FREELANCE (ABTJOKI)**.*
