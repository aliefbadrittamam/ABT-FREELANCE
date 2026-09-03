# STITCH.md — Prompt untuk Generate UI di Google Stitch

## Cara Pakai
1. Buka [Google Stitch](https://stitch.withgoogle.com), pilih mode **Standard** (text-to-design)
2. **Paste Prompt 1** sebagai pesan pertama di project baru — ini yang membangun "design system" (warna, font, mood) yang dipakai Stitch buat layar-layar berikutnya
3. Lanjutkan **Prompt 2 sampai 9 satu per satu**, di project yang sama, biar Stitch tetap konsisten pakai desain yang sama tiap layar. **Jangan gabung beberapa prompt jadi satu**
4. Setelah tiap layar ke-generate, cek dulu apakah semua elemen yang diminta muncul. Kalau ada yang kurang pas, edit inkremental (contoh: "perbesar tombol utama") — jangan tulis ulang prompt dari awal
5. Setelah semua layar oke, pakai fitur **export code** (React atau HTML/CSS) di Stitch buat dijadikan referensi tampilan Blade view Laravel kamu

> Catatan konteks: karena aplikasi ini dipakai **lokal** (klien tidak mengakses web-nya sama sekali), semua layar di bawah ini sebenarnya "punya admin sendiri" — nggak ada lagi bedanya versi publik vs admin. Halaman invoice tetap didesain rapi karena dipakai sebagai dasar tampilan PDF yang dikirim ke klien.

> Catatan limit: mode Standard (Gemini 2.5 Flash) di Stitch ada limit sekitar 350 generate/bulan di plan gratis. Rencanakan iterasi seperlunya.

---

## Design System (konteks yang dipakai di semua layar)
- **Produk**: ABT-FREELANCE — web app lokal buat manajemen invoice, kategori jasa, dan testimoni klien freelance
- **Gaya**: clean, dominan putih, dengan aksen kuning neon yang berani — kontras tegas, terasa fresh & energik tapi tetap rapi/profesional
- **Background**: Putih `#FFFFFF`, sekunder abu sangat muda `#F4F4F5`
- **Teks**: Charcoal/hitam pekat `#18181B`
- **Warna aksen brand (tombol utama, highlight, logo, active state)**: Kuning Neon `#E8FF00` — gunakan teks gelap (`#18181B`) di atas warna ini karena terangnya tinggi
- **Border/netral**: Abu muda `#E4E4E7`
- **Warna status** (khusus badge status invoice/testimoni, terpisah dari warna aksen brand):
  - Status Lunas: Hijau `#22C55E`
  - Status DP Terbayar: Biru `#3B82F6`
  - Status Belum Bayar: Oranye/Amber `#F59E0B`
- **Font**: Inter, sans-serif
- **Radius**: rounded-xl (sudut lembut)
- **Mood referensi**: seperti Linear atau Notion tapi dengan aksen warna yang lebih berani (kuning neon), bukan playful/childish — tetap terasa profesional & terpercaya buat dokumen bisnis seperti invoice

---

## Prompt 1 — Dashboard / Halaman Utama (mulai project baru di sini)

```
Design a clean web app dashboard screen called "ABT-FREELANCE" — a local business management tool for a solo freelancer who offers task-writing/joki tugas services, expanding into web development and design services. This is the main screen shown when the app opens.

Design system: dominant white background (#FFFFFF), secondary very light gray (#F4F4F5), bold neon yellow accent color (#E8FF00) used for primary buttons, highlights, active nav states and logo — with dark charcoal text (#18181B) on top of the yellow for readability. Body text in charcoal (#18181B). Inter font. Soft rounded corners (rounded-xl). Mood: clean and professional like Linear or Notion, but with a bold, energetic neon yellow accent instead of muted colors — still trustworthy enough for a business/invoicing tool, not childish or overly playful.

Layout: left sidebar navigation on white background with icons + labels for Dashboard (active, highlighted in neon yellow), Kategori, Invoice, Testimoni. Top of main content area shows page title "Dashboard Penghasilan".

Below that, 2 summary cards side by side: "Total Pendapatan" (large bold number, card with a subtle neon yellow accent border or icon) and "Piutang / Belum Lunas" (large bold number, neutral gray card).

Below the cards, a line or bar chart titled "Pendapatan per Bulan" showing monthly revenue trend over the last 6 months, using neon yellow as the chart's main data color against a white background.

Below the chart, a breakdown list titled "Pendapatan per Kategori Jasa" listing category name + total amount per row (e.g. "Joki Tugas — Rp 10.200.000", "Jasa Website — Rp 5.200.000").

Desktop-first, clean SaaS dashboard aesthetic, generous whitespace, data-focused.
```

---

## Prompt 2 — Login (Opsional)

```
Using the same design system, design a simple, minimal login screen for ABT-FREELANCE — an optional lightweight lock screen since the app runs locally on the owner's own computer.

Screen: centered login card on the white/light-gray background. Card contains: "ABT-FREELANCE" wordmark at top in charcoal text with a small neon yellow accent underline or icon, heading "Masuk", a single password input field, a primary button "Masuk" in neon yellow with dark charcoal text, and small helper text below ("Aplikasi ini hanya untuk pemakaian pribadi"). Centered, minimal, single column, works well on a laptop screen.
```

---

## Prompt 3 — Kategori Jasa

```
Using the same design system and sidebar layout, design the "Kategori Jasa" screen.

Layout: same sidebar navigation (highlight "Kategori" in neon yellow as active). Main content: page title "Kategori Jasa" with a primary button "Tambah Kategori" (neon yellow background, dark text) top right. Below, a simple list/table of existing categories, each row showing category name, number of invoices under it, and small edit/delete icon buttons.

Include a modal design mockup: "Tambah Kategori Baru" with a text input for category name and a "Simpan" button in neon yellow.

Clean, minimal, functional table styling consistent with the dashboard screen.
```

---

## Prompt 4 — Daftar Invoice

```
Using the same design system and sidebar, design the "Daftar Invoice" screen.

Layout: sidebar (highlight "Invoice"), page title "Daftar Invoice" with primary button "Buat Invoice Baru" (neon yellow) top right. Below, a filter row with a dropdown to filter by kategori jasa and tabs to filter by status (Semua, Belum Bayar, DP Terbayar, Lunas).

Below filters, a table with columns: Nomor Invoice, Nama Klien, Kategori, Total, Status (as a colored pill badge — Amber #F59E0B for Belum Bayar, Blue #3B82F6 for DP Terbayar, Green #22C55E for Lunas — these status colors are separate from the neon yellow brand accent), Deadline, and a "Lihat/Edit" action button per row.

Clean table design on white background, status badges clearly color-coded and easy to scan.
```

---

## Prompt 5 — Buat Invoice Baru

```
Using the same design system, design the "Buat Invoice Baru" form screen.

Layout: sidebar navigation, page title "Buat Invoice Baru". A clean vertical form in a centered white card with these fields in order: Nama Klien (text input), Kategori Jasa (dropdown), Deskripsi Pekerjaan (textarea), Deadline (date picker), a segmented control labeled "Jenis Pembayaran" with two options "Dengan DP" and "Bayar Lunas Langsung" (selected option highlighted in neon yellow), a conditional field "Jumlah DP" (shown expanded, tied to the "Dengan DP" option), and "Total Biaya" (currency input). At the bottom, a primary button "Buat Invoice" in neon yellow with dark text, and a secondary outlined "Batal" button.

Form should feel simple and fast to fill, generous spacing, clear labels, consistent with the clean white + neon yellow aesthetic.
```

---

## Prompt 6 — Detail Invoice (Preview + Export PDF)

```
Design the "Detail Invoice" screen for ABT-FREELANCE — this is the preview page the freelancer looks at before exporting the invoice as a PDF file to manually send to a client via WhatsApp (this is NOT a public web page, just an internal preview/export screen).

Layout: a clean invoice-document-style card. Top: "ABT-FREELANCE" wordmark/logo placeholder and "INVOICE" label with invoice number (e.g. INV-2026-001) and date. Client info: "Kepada: [Nama Klien]". A details section: Kategori Jasa, Deskripsi Pekerjaan, Deadline. A pricing summary: Total Biaya, DP Dibayar, and Sisa Bayar as separate rows (Sisa Bayar emphasized in bold). A status badge showing the current payment status as a colored pill (Amber/Blue/Green per status).

Below that, a "Pembayaran QRIS" section: a large QR code image placeholder centered, with text below it "Transfer sesuai nominal: Rp [total]".

At the top of the page (outside the invoice card itself, as page-level actions), show three buttons: "Export PDF" (primary, neon yellow), "Export Gambar" (secondary, outlined with neon yellow border), and "Edit Invoice" (secondary, outlined gray) — plus a "Status Pembayaran" stepper: Belum Bayar → DP Terbayar → Lunas with the current step highlighted in neon yellow and completed steps in green.

Style: professional, trustworthy, clean white background with neon yellow used sparingly for emphasis (buttons, current step, key totals) — the invoice itself should look like a real, printable business document, not overly colorful.
```

---

## Prompt 7 — Testimoni (Riwayat + Upload 4-Slot)

```
Using the same design system and sidebar, design the "Testimoni" screen — combining an upload form for a new testimonial and a history/gallery of past ones (this doubles as the "public showcase" reference for the freelancer, since the actual public display is the Telegram channel — this screen is just the local admin view).

Layout: sidebar (highlight "Testimoni"), page title "Testimoni" with primary button "Upload Testimoni Baru" (neon yellow) top right.

Upload form (shown expanded in this mockup): 4 distinct upload boxes arranged in a 2x2 grid, each clearly labeled: "Tugas", "Chat dengan Customer", "Hasil", "Pelunasan" — each box shows a dashed border placeholder with an upload icon and "Klik untuk upload" text, border color using a light neon yellow tint on hover/focus state. Below the 4-box grid: a text input "Nama Klien (opsional)" and a textarea "Caption (opsional)". Below that, a primary button "Buat & Kirim ke Telegram" in neon yellow.

Below the form, show a gallery grid (riwayat) of previously submitted testimonials — each card shows the composed 2x2 collage thumbnail, client name, date, and a small status badge "Terkirim ke Telegram" (green) or "Gagal Kirim" (amber), with an edit icon on each card.
```

---

## Prompt 8 — Edit Testimoni (ganti per-slot)

```
Using the same design system, design the "Edit Testimoni" screen. Similar to the upload form, but now the 4 grid boxes (Tugas, Chat dengan Customer, Hasil, Pelunasan) already show existing thumbnail images instead of empty placeholders, and each box has a small "Ganti Gambar" button overlay (neon yellow accent) so the admin can swap just one image without affecting the others. Show a preview of the current composed collage image at the top of the page for reference. Include a "Simpan Perubahan" primary button (neon yellow) at the bottom, with helper text: "Postingan Telegram yang sudah ada akan otomatis diperbarui".
```

---

## Prompt 9 — Edit Invoice (Status Pembayaran)

```
Using the same design system, design the "Edit Invoice" screen — similar to the "Buat Invoice Baru" form but with existing data pre-filled, plus a "Status Pembayaran" section at the top showing a horizontal stepper: Belum Bayar → DP Terbayar → Lunas (current step highlighted in neon yellow, completed steps in green), and a dropdown or button group to change the status manually. Include a "Lihat & Export PDF" button near the top linking back to the detail/preview screen.
```

---

## Tips Iterasi Setelah Generate
- Kalau ada elemen yang kurang pas, kasih instruksi spesifik & kecil dulu, contoh: *"Make the neon yellow accent more saturated and use it only on the primary button, not the whole card background"* — bukan generate ulang dari nol
- Cek konsistensi warna status (Amber/Blue/Green) di semua layar yang ada badge status — jangan sampai ketimpa warna kuning neon brand, karena fungsinya beda (brand accent vs status semantik)
- Setelah puas, export ke **HTML/CSS** dari Stitch, lalu adaptasi manual ke Blade view Laravel kamu (atau ke komponen Filament kalau pakai custom theme)
