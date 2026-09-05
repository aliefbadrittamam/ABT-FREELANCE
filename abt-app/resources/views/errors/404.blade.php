<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Halaman Tidak Ditemukan</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#000000',
                        'primary-container': '#E8FF00',
                        'on-surface': '#111111',
                        'secondary': '#666666',
                        'border-subtle': '#e5e7eb',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-[#f8fafc] text-on-surface font-sans antialiased min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center space-y-4 bg-white p-8 rounded-2xl border border-border-subtle shadow-xs">
        <div class="w-16 h-16 rounded-2xl bg-primary-container/20 text-on-surface flex items-center justify-center mx-auto mb-2">
            <span class="material-symbols-outlined text-4xl">search_off</span>
        </div>
        <h1 class="text-4xl font-black tracking-tight text-on-surface">404</h1>
        <h2 class="text-base font-bold text-on-surface">Halaman Tidak Ditemukan</h2>
        <p class="text-xs text-secondary leading-relaxed">
            Link invoice atau halaman yang Anda tuju tidak tersedia, salah ketik, atau telah dihapus.
        </p>
        <div class="pt-2">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-primary-container text-on-surface text-xs font-bold rounded-xl shadow-xs hover:brightness-95 transition">
                <span class="material-symbols-outlined text-base">home</span>
                Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
