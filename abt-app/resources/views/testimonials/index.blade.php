@extends('layouts.app')

@section('title', 'Testimoni — ABT-FREELANCE')
@section('header', 'Testimoni')

@section('content')
<header class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-[32px] font-bold text-on-surface dark:text-white tracking-tight leading-tight sm:leading-10">Testimoni</h1>
        <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5 sm:mt-1">Kelola testimoni klien & posting ke Telegram.</p>
    </div>
    <a href="{{ route('testimonials.create') }}" class="bg-primary-container text-on-surface font-semibold text-xs sm:text-sm px-4 sm:px-5 py-2.5 rounded-lg hover:brightness-95 transition flex items-center gap-2 shadow-sm w-fit">
        <span class="material-symbols-outlined text-base sm:text-lg">add</span>
        Upload Testimoni Baru
    </a>
</header>

<!-- Testimonial Gallery -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
    @forelse($testimonials as $t)
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] overflow-hidden hover:shadow-md hover:border-on-surface-variant/30 transition-all duration-300 group relative shadow-sm">
        @if($t->testimonial_number)
        <div class="absolute top-2.5 left-2.5 z-10 bg-on-surface/90 dark:bg-black/80 backdrop-blur-sm text-primary-container font-bold text-[11px] px-2.5 py-0.5 rounded-full shadow-sm">
            #{{ $t->testimonial_number }}
        </div>
        @endif
        @if($t->composed_image_path)
        <div class="aspect-square overflow-hidden bg-surface-container dark:bg-[#181818]">
            <img src="{{ asset('storage/' . $t->composed_image_path) }}" alt="Kolase" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        </div>
        @endif
        <div class="p-3.5 sm:p-4">
            <div class="flex justify-between items-start mb-2">
                <div class="flex-1 pr-2">
                    <p class="text-xs sm:text-sm font-semibold text-on-surface dark:text-white line-clamp-1">
                        {{ $t->major ? $t->major . ' ' . $t->task_title : ($t->client_name ?: 'Testimoni #' . $t->testimonial_number) }}
                    </p>
                    @if($t->deliverables)
                    <p class="text-[11px] text-primary dark:text-primary-container font-medium mt-0.5 line-clamp-1">({{ $t->deliverables }})</p>
                    @endif
                    <p class="text-[10px] text-secondary dark:text-gray-400 mt-1">{{ $t->created_at->format('d M Y') }}</p>
                </div>
                @if($t->posted_to_telegram)
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold bg-status-lunas/10 text-status-lunas px-2 py-0.5 rounded-full shrink-0">
                    <span class="material-symbols-outlined text-xs">check_circle</span>
                    Telegram
                </span>
                @else
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold bg-status-pending/10 text-status-pending px-2 py-0.5 rounded-full shrink-0">
                    <span class="material-symbols-outlined text-xs">warning</span>
                    Draft
                </span>
                @endif
            </div>
            <div class="mt-3 pt-3 border-t border-border-subtle dark:border-[#2a2a2a] flex justify-between items-center">
                <span class="text-[10px] text-secondary dark:text-gray-400 font-mono">
                    #{{ $t->testimonial_number }}
                </span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('testimonials.edit', $t) }}" class="text-xs text-secondary dark:text-gray-400 hover:text-on-surface dark:hover:text-white font-medium flex items-center gap-1 transition">
                        <span class="material-symbols-outlined text-sm">edit</span>
                        Edit
                    </a>
                    <form action="{{ route('testimonials.destroy', $t) }}" method="POST" onsubmit="return confirm('Hapus testimoni ini? Postingan di Telegram (jika ada) juga akan dihapus.')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium flex items-center gap-0.5 transition">
                            <span class="material-symbols-outlined text-sm">delete</span>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full py-16 text-center text-on-surface-variant dark:text-gray-400">
        <span class="material-symbols-outlined text-4xl sm:text-5xl opacity-30 mb-3">photo_library</span>
        <p class="text-xs sm:text-sm">Belum ada testimoni. Upload testimoni pertama!</p>
    </div>
    @endforelse
</div>

@if($testimonials->hasPages())
<div class="mt-6">{{ $testimonials->links() }}</div>
@endif
@endsection
