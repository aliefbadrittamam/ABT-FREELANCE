@extends('layouts.app')

@section('title', 'Pusat Materi Iklan & Promosi — ABT-FREELANCE')
@section('header', 'Materi Iklan & Promosi')

@section('content')
<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8 gap-4">
    <div>
        <h1 class="text-2xl sm:text-[30px] font-black text-on-surface dark:text-white tracking-tight leading-tight">Materi Iklan & Promosi</h1>
        <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5">Gudang materi penawaran jasa, poster promosi siap sebar, dan copywriting WhatsApp/Telegram.</p>
    </div>
    <a href="{{ route('promotions.create') }}" class="flex items-center px-4 sm:px-5 py-2.5 bg-primary-container text-on-surface rounded-lg font-bold text-xs sm:text-sm shadow-sm hover:brightness-95 transition-all w-fit gap-2">
        <span class="material-symbols-outlined text-base sm:text-lg">campaign</span>
        + Tambah Materi Iklan
    </a>
</div>

<!-- Category Tabs & Search Bar -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 border-b border-border-subtle dark:border-[#2a2a2a] pb-4">
    <!-- Category Filter Tabs (Horizontal scrollable) -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
        <a href="{{ route('promotions.index', array_filter(['search' => $search])) }}" 
           class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shrink-0 {{ $selectedCategory === 'all' ? 'bg-on-surface text-white dark:bg-white dark:text-on-surface font-bold shadow-xs' : 'text-secondary dark:text-gray-400 hover:bg-surface-variant dark:hover:bg-[#252525]' }}">
            <span class="material-symbols-outlined text-base">apps</span>
            Semua
            <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $selectedCategory === 'all' ? 'bg-white/20 text-white dark:bg-black/20 dark:text-on-surface' : 'bg-gray-100 dark:bg-[#333]' }}">
                {{ $totalCount }}
            </span>
        </a>

        <a href="{{ route('promotions.index', array_filter(['category' => 'joki', 'search' => $search])) }}" 
           class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shrink-0 {{ $selectedCategory === 'joki' ? 'bg-on-surface text-white dark:bg-white dark:text-on-surface font-bold shadow-xs' : 'text-secondary dark:text-gray-400 hover:bg-surface-variant dark:hover:bg-[#252525]' }}">
            <span class="material-symbols-outlined text-base">assignment</span>
            Joki Tugas
            <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $selectedCategory === 'joki' ? 'bg-white/20 text-white dark:bg-black/20 dark:text-on-surface' : 'bg-gray-100 dark:bg-[#333]' }}">
                {{ $jokiCount }}
            </span>
        </a>

        <a href="{{ route('promotions.index', array_filter(['category' => 'website', 'search' => $search])) }}" 
           class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shrink-0 {{ $selectedCategory === 'website' ? 'bg-on-surface text-white dark:bg-white dark:text-on-surface font-bold shadow-xs' : 'text-secondary dark:text-gray-400 hover:bg-surface-variant dark:hover:bg-[#252525]' }}">
            <span class="material-symbols-outlined text-base">code</span>
            Website & Coding
            <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $selectedCategory === 'website' ? 'bg-white/20 text-white dark:bg-black/20 dark:text-on-surface' : 'bg-gray-100 dark:bg-[#333]' }}">
                {{ $webCount }}
            </span>
        </a>

        <a href="{{ route('promotions.index', array_filter(['category' => 'tournament', 'search' => $search])) }}" 
           class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shrink-0 {{ $selectedCategory === 'tournament' ? 'bg-on-surface text-white dark:bg-white dark:text-on-surface font-bold shadow-xs' : 'text-secondary dark:text-gray-400 hover:bg-surface-variant dark:hover:bg-[#252525]' }}">
            <span class="material-symbols-outlined text-base">sports_soccer</span>
            eFootball
            <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $selectedCategory === 'tournament' ? 'bg-white/20 text-white dark:bg-black/20 dark:text-on-surface' : 'bg-gray-100 dark:bg-[#333]' }}">
                {{ $tourCount }}
            </span>
        </a>

        @foreach($categories as $cat)
        @if(!in_array(strtolower($cat->name), ['joki tugas', 'jasa website', 'desain grafis']))
        <a href="{{ route('promotions.index', array_filter(['category' => $cat->id, 'search' => $search])) }}" 
           class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shrink-0 {{ $selectedCategory == $cat->id ? 'bg-on-surface text-white dark:bg-white dark:text-on-surface font-bold shadow-xs' : 'text-secondary dark:text-gray-400 hover:bg-surface-variant dark:hover:bg-[#252525]' }}">
            <span class="material-symbols-outlined text-base">label</span>
            {{ $cat->name }}
        </a>
        @endif
        @endforeach
    </div>

    <!-- Search Box -->
    <form method="GET" action="{{ route('promotions.index') }}" class="relative w-full md:w-72 shrink-0">
        @if($selectedCategory !== 'all')
        <input type="hidden" name="category" value="{{ $selectedCategory }}">
        @endif
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-secondary dark:text-gray-400 text-sm">search</span>
        <input type="text" name="search" value="{{ $search }}" placeholder="Cari materi iklan..." 
               class="w-full pl-9 pr-8 py-1.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white placeholder:text-secondary/50 focus:outline-none focus:ring-1 focus:ring-primary">
        @if($search)
        <a href="{{ route('promotions.index', array_filter(['category' => $selectedCategory !== 'all' ? $selectedCategory : null])) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-secondary hover:text-red-500 text-xs font-bold">×</a>
        @endif
    </form>
</div>

<!-- Promotion Cards Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
    @forelse($promotions as $promo)
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] overflow-hidden shadow-sm flex flex-col justify-between hover:border-primary/50 transition-all duration-200 group"
         x-data="{ copied: false, text: @json($promo->copywriting) }">
        
        <div>
            <!-- Banner Image Preview -->
            <div class="relative aspect-video sm:aspect-4/3 bg-surface-container dark:bg-[#181818] overflow-hidden flex items-center justify-center border-b border-border-subtle dark:border-[#2a2a2a]">
                @if($promo->banner_path && file_exists(storage_path('app/public/' . $promo->banner_path)))
                <img src="{{ asset('storage/' . $promo->banner_path) }}" alt="{{ $promo->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @else
                <div class="flex flex-col items-center justify-center p-6 text-center text-secondary dark:text-gray-500">
                    <span class="material-symbols-outlined text-4xl text-primary/60 mb-2">campaign</span>
                    <p class="text-xs font-bold text-on-surface dark:text-gray-300">{{ $promo->title }}</p>
                    <p class="text-[11px] mt-1">{{ $promo->tagline ?: 'Materi Promosi Siap Salin' }}</p>
                </div>
                @endif

                <!-- Category Badge -->
                <div class="absolute top-2.5 left-2.5">
                    <span class="inline-flex items-center gap-1 bg-black/80 backdrop-blur-xs text-primary-container font-bold text-[10px] px-2.5 py-0.5 rounded-full border border-white/10 shadow-xs">
                        {{ $promo->category_label }}
                    </span>
                </div>

                @if($promo->banner_type === 'auto_generated')
                <div class="absolute top-2.5 right-2.5">
                    <span class="inline-flex items-center gap-0.5 bg-primary-container text-on-surface font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xs">
                        <span class="material-symbols-outlined text-[11px]">auto_awesome</span> HD Poster
                    </span>
                </div>
                @endif
            </div>

            <!-- Content Details -->
            <div class="p-4 sm:p-5 space-y-3">
                <div>
                    <h3 class="font-bold text-sm sm:text-base text-on-surface dark:text-white line-clamp-1 leading-snug" title="{{ $promo->title }}">
                        {{ $promo->title }}
                    </h3>
                    @if($promo->tagline)
                    <p class="text-[11px] text-primary dark:text-primary-container font-semibold mt-0.5 line-clamp-1">
                        {{ $promo->tagline }}
                    </p>
                    @endif
                </div>

                <!-- Copywriting Box -->
                <div>
                    <label class="block text-[10px] font-bold text-secondary dark:text-gray-400 uppercase tracking-wider mb-1 flex items-center justify-between">
                        <span>Teks Copywriting:</span>
                        <span class="text-[10px] text-emerald-500 font-semibold" x-show="copied" x-cloak>✓ Tersalin ke Clipboard!</span>
                    </label>
                    <div class="bg-surface dark:bg-[#181818] p-3 rounded-lg border border-border-subtle dark:border-[#333] font-mono text-[11px] text-on-surface dark:text-gray-200 max-h-36 overflow-y-auto whitespace-pre-wrap select-all scrollbar-thin shadow-inner leading-relaxed"
                         x-text="text"></div>
                </div>
            </div>
        </div>

        <!-- Card Action Footer -->
        <div class="p-4 sm:p-5 pt-0 border-t border-border-subtle dark:border-[#2a2a2a] mt-2">
            <div class="flex items-center justify-between gap-2 pt-3">
                <!-- Fast Copy Button -->
                <button type="button" 
                        @click="navigator.clipboard.writeText(text); copied = true; setTimeout(() => copied = false, 2500)"
                        class="flex-1 py-2 px-3 bg-primary-container text-on-surface font-bold text-xs rounded-lg hover:brightness-95 transition flex items-center justify-center gap-1.5 shadow-2xs">
                    <span class="material-symbols-outlined text-base" x-text="copied ? 'check' : 'content_copy'"></span>
                    <span x-text="copied ? 'Tersalin!' : 'Salin Teks Iklan'"></span>
                </button>

                <!-- WhatsApp Direct Share -->
                <a :href="'https://api.whatsapp.com/send?text=' + encodeURIComponent(text)" target="_blank"
                   class="p-2 bg-[#25D366] text-white rounded-lg text-xs font-bold hover:brightness-95 transition shrink-0 flex items-center justify-center"
                   title="Buka & Kirim via WhatsApp">
                    <span class="material-symbols-outlined text-base">chat</span>
                </a>

                @if($promo->banner_path && file_exists(storage_path('app/public/' . $promo->banner_path)))
                <!-- Download Banner -->
                <a href="{{ route('promotions.downloadBanner', $promo) }}" 
                   class="p-2 bg-surface-container dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-gray-300 rounded-lg text-xs hover:bg-gray-200 dark:hover:bg-[#333] transition shrink-0 flex items-center justify-center"
                   title="Unduh Poster HD">
                    <span class="material-symbols-outlined text-base">download</span>
                </a>

                <!-- Post to Telegram Channel -->
                <form action="{{ route('promotions.postTelegram', $promo) }}" method="POST" class="inline" onsubmit="return confirm('Posting poster dan teks iklan ini ke Channel Telegram @ABT_TESTIMONI?')">
                    @csrf
                    <button type="submit" class="p-2 bg-[#229ED9] text-white rounded-lg text-xs font-bold hover:brightness-95 transition shrink-0 flex items-center justify-center" title="Posting ke Channel Telegram">
                        <span class="material-symbols-outlined text-base">send</span>
                    </button>
                </form>
                @endif

                <!-- Edit & Delete Dropdown / Actions -->
                <div class="flex items-center gap-1">
                    <a href="{{ route('promotions.edit', $promo) }}" class="p-1.5 text-secondary dark:text-gray-400 hover:text-primary rounded-md" title="Edit">
                        <span class="material-symbols-outlined text-base">edit</span>
                    </a>
                    <form action="{{ route('promotions.destroy', $promo) }}" method="POST" onsubmit="return confirm('Hapus materi iklan {{ $promo->title }}?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 text-secondary dark:text-gray-400 hover:text-red-500 rounded-md" title="Hapus">
                            <span class="material-symbols-outlined text-base">delete</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full py-16 text-center text-secondary dark:text-gray-400 bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-8">
        <span class="material-symbols-outlined text-4xl text-primary/60 mb-2">campaign</span>
        <h4 class="text-sm font-bold text-on-surface dark:text-white mb-1">Belum Ada Materi Iklan</h4>
        <p class="text-xs text-secondary dark:text-gray-400 max-w-md mx-auto mb-4">Mulai tambahkan materi penawaran jasa atau gunakan tombol template otomatis.</p>
        <a href="{{ route('promotions.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary-container text-on-surface font-bold text-xs rounded-lg shadow-sm">
            <span class="material-symbols-outlined text-base">add</span> Buat Iklan Pertama
        </a>
    </div>
    @endforelse
</div>

@if($promotions->hasPages())
<div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs sm:text-sm text-on-surface-variant dark:text-gray-400">
    <span>Menampilkan {{ $promotions->firstItem() }}-{{ $promotions->lastItem() }} dari {{ $promotions->total() }} materi iklan</span>
    <div>{{ $promotions->links() }}</div>
</div>
@endif
@endsection
