@extends('layouts.app')

@section('title', 'Testimoni — ABT-FREELANCE')
@section('header', 'Testimoni')

@section('content')
<div x-data="{
    deleteModalOpen: false,
    deleteUrl: '',
    testiNumber: '',
    isPostedToTelegram: false,
    deleteFromTelegram: false,
    isForceDelete: false,
    openDeleteModal(url, number, isPosted, force = false) {
        this.deleteUrl = url;
        this.testiNumber = number;
        this.isPostedToTelegram = isPosted;
        this.deleteFromTelegram = false;
        this.isForceDelete = force;
        this.deleteModalOpen = true;
    }
}">
    <!-- Header -->
    <header class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-[32px] font-bold text-on-surface dark:text-white tracking-tight leading-tight sm:leading-10">Testimoni</h1>
            <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5 sm:mt-1">Kelola testimoni klien & posting ke Telegram.</p>
        </div>
        @if($status !== 'trash')
        <a href="{{ route('testimonials.create') }}" class="bg-primary-container text-on-surface font-semibold text-xs sm:text-sm px-4 sm:px-5 py-2.5 rounded-lg hover:brightness-95 transition flex items-center gap-2 shadow-sm w-fit">
            <span class="material-symbols-outlined text-base sm:text-lg">add</span>
            Upload Testimoni Baru
        </a>
        @endif
    </header>

    <!-- Toolbar: Tabs + Search Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 border-b border-border-subtle dark:border-[#2a2a2a] pb-4">
        <!-- Status Tabs (Aktif & Sampah) -->
        <div class="flex items-center gap-2">
            <a href="{{ route('testimonials.index', array_filter(['search' => $search])) }}" 
               class="px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition flex items-center gap-2 {{ $status !== 'trash' ? 'bg-on-surface text-white dark:bg-white dark:text-on-surface' : 'text-on-surface-variant hover:bg-surface-variant dark:text-gray-400 dark:hover:bg-[#252525]' }}">
                <span class="material-symbols-outlined text-base sm:text-lg">photo_library</span>
                Semua Testimoni
                <span class="text-[11px] px-2 py-0.5 rounded-full {{ $status !== 'trash' ? 'bg-white/20 text-white dark:bg-black/20 dark:text-on-surface' : 'bg-surface-container dark:bg-[#333]' }}">
                    {{ $activeCount }}
                </span>
            </a>
            <a href="{{ route('testimonials.index', array_filter(['status' => 'trash', 'search' => $search])) }}" 
               class="px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition flex items-center gap-2 {{ $status === 'trash' ? 'bg-on-surface text-white dark:bg-white dark:text-on-surface' : 'text-on-surface-variant hover:bg-surface-variant dark:text-gray-400 dark:hover:bg-[#252525]' }}">
                <span class="material-symbols-outlined text-base sm:text-lg">delete_outline</span>
                Sampah (Trash)
                @if($trashCount > 0)
                <span class="text-[11px] px-2 py-0.5 rounded-full bg-red-500/10 text-red-500 dark:bg-red-500/20 font-bold">
                    {{ $trashCount }}
                </span>
                @endif
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('testimonials.index') }}" class="relative w-full md:w-80">
            @if($status === 'trash')
            <input type="hidden" name="status" value="trash">
            @endif
            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-3 text-secondary dark:text-gray-400 text-lg pointer-events-none">search</span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor (#80), jurusan, tugas..." 
                       class="w-full pl-9 pr-9 py-2 bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#333] rounded-xl text-xs sm:text-sm text-on-surface dark:text-white placeholder-secondary dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition">
                @if($search)
                <a href="{{ route('testimonials.index', $status === 'trash' ? ['status' => 'trash'] : []) }}" 
                   class="absolute right-3 text-secondary hover:text-on-surface dark:text-gray-400 dark:hover:text-white transition" title="Hapus pencarian">
                    <span class="material-symbols-outlined text-base">close</span>
                </a>
                @endif
            </div>
        </form>
    </div>

    @if($search)
    <div class="mb-4 flex items-center justify-between text-xs text-secondary dark:text-gray-400 bg-surface-container/50 dark:bg-[#181818] px-3.5 py-2 rounded-lg border border-border-subtle dark:border-[#2a2a2a]">
        <div>
            Hasil pencarian untuk: <strong class="text-on-surface dark:text-white">"{{ $search }}"</strong> ({{ $testimonials->total() }} ditemukan)
        </div>
        <a href="{{ route('testimonials.index', $status === 'trash' ? ['status' => 'trash'] : []) }}" class="text-primary dark:text-primary-container font-semibold hover:underline flex items-center gap-0.5">
            <span class="material-symbols-outlined text-sm">restart_alt</span>
            Reset
        </a>
    </div>
    @endif

    <!-- Testimonial Gallery -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @forelse($testimonials as $t)
        <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] overflow-hidden hover:shadow-md hover:border-on-surface-variant/30 transition-all duration-300 group flex flex-col justify-between shadow-sm">
            <div>
                <div class="relative aspect-square overflow-hidden bg-surface-container dark:bg-[#181818] flex items-center justify-center">
                    @if($t->testimonial_number)
                    <div class="absolute top-2.5 left-2.5 z-10 bg-on-surface/90 dark:bg-black/80 backdrop-blur-sm text-primary-container font-bold text-[11px] px-2.5 py-0.5 rounded-full shadow-sm">
                        #{{ $t->testimonial_number }}
                    </div>
                    @endif

                    @if($t->composed_image_path && file_exists(storage_path('app/public/' . $t->composed_image_path)))
                    <img src="{{ asset('storage/' . $t->composed_image_path) }}" alt="Kolase" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                    <div class="flex flex-col items-center justify-center text-on-surface-variant/40 dark:text-gray-600 gap-2 p-4 text-center">
                        <span class="material-symbols-outlined text-4xl">mark_chat_read</span>
                        <p class="text-xs font-medium text-secondary dark:text-gray-400">Arsip Testimoni Telegram</p>
                    </div>
                    @endif
                </div>

                <div class="p-3.5 sm:p-4">
                    <div class="flex justify-between items-start mb-2 gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-semibold text-on-surface dark:text-white truncate" title="{{ $t->major ? $t->major . ' ' . $t->task_title : ($t->client_name ?: 'Testimoni #' . $t->testimonial_number) }}">
                                {{ $t->major ? $t->major . ' ' . $t->task_title : ($t->client_name ?: 'Testimoni #' . $t->testimonial_number) }}
                            </p>
                            @if($t->deliverables)
                            <p class="text-[11px] text-primary dark:text-primary-container font-medium mt-0.5 truncate" title="({{ $t->deliverables }})">({{ $t->deliverables }})</p>
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
                </div>
            </div>

            <div class="px-3.5 sm:px-4 pb-3.5 sm:pb-4 pt-0">
                <div class="pt-3 border-t border-border-subtle dark:border-[#2a2a2a] flex justify-between items-center">
                    <span class="text-[10px] text-secondary dark:text-gray-400 font-mono">
                        #{{ $t->testimonial_number }}
                    </span>
                    
                    <div class="flex items-center gap-2">
                        @if($status === 'trash')
                            <!-- Restore Button -->
                            <form action="{{ route('testimonials.restore', $t->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-primary dark:text-primary-container hover:underline font-semibold flex items-center gap-0.5 transition">
                                    <span class="material-symbols-outlined text-sm">restore</span>
                                    Pulihkan
                                </button>
                            </form>
                            @if($t->isDeletable())
                            <!-- Permanent Delete Button -->
                            <button type="button" 
                                    @click="openDeleteModal('{{ route('testimonials.forceDelete', $t->id) }}', '{{ $t->testimonial_number }}', {{ $t->posted_to_telegram && $t->telegram_message_id ? 'true' : 'false' }}, true)"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium flex items-center gap-0.5 transition">
                                <span class="material-symbols-outlined text-sm">delete_forever</span>
                                Hapus Permanen
                            </button>
                            @else
                            <span class="text-[10px] text-secondary dark:text-gray-500 font-medium flex items-center gap-0.5" title="Terkunci permanen (> 7 hari)">
                                <span class="material-symbols-outlined text-xs">lock</span>
                                Terkunci
                            </span>
                            @endif
                        @else
                            <!-- Edit Button -->
                            <a href="{{ route('testimonials.edit', $t) }}" class="text-xs text-secondary dark:text-gray-400 hover:text-on-surface dark:hover:text-white font-medium flex items-center gap-1 transition">
                                <span class="material-symbols-outlined text-sm">edit</span>
                                Edit
                            </a>

                            <!-- 7-Day Protection Gate -->
                            @if($t->isDeletable())
                            <button type="button" 
                                    @click="openDeleteModal('{{ route('testimonials.destroy', $t) }}', '{{ $t->testimonial_number }}', {{ $t->posted_to_telegram && $t->telegram_message_id ? 'true' : 'false' }}, false)"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium flex items-center gap-0.5 transition"
                                    title="Dapat dihapus (Sisa {{ $t->getDaysRemainingForDeletion() }} hari)">
                                <span class="material-symbols-outlined text-sm">delete</span>
                                Hapus
                            </button>
                            @else
                            <span class="inline-flex items-center gap-1 text-[10px] font-medium text-secondary/70 dark:text-gray-500 bg-surface-container/60 dark:bg-[#252525] px-2 py-0.5 rounded" 
                                  title="Testimoni ini sudah lebih dari 1 minggu (7 hari) dan telah dikunci otomatis untuk melindungi portofolio.">
                                <span class="material-symbols-outlined text-xs">lock</span>
                                Terproteksi (>7h)
                            </span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center text-on-surface-variant dark:text-gray-400 bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a]">
            <span class="material-symbols-outlined text-4xl sm:text-5xl opacity-30 mb-3">
                {{ $search ? 'search_off' : ($status === 'trash' ? 'delete_sweep' : 'photo_library') }}
            </span>
            <p class="text-xs sm:text-sm font-medium">
                @if($search)
                    Tidak ada testimoni yang cocok dengan pencarian <strong>"{{ $search }}"</strong>.
                @elseif($status === 'trash')
                    Kotak sampah kosong. Tidak ada testimoni yang terhapus.
                @else
                    Belum ada testimoni. Upload testimoni pertama!
                @endif
            </p>
            @if($search)
            <div class="mt-3">
                <a href="{{ route('testimonials.index', $status === 'trash' ? ['status' => 'trash'] : []) }}" class="inline-flex items-center gap-1 text-xs font-semibold bg-primary-container text-on-surface px-3.5 py-1.5 rounded-lg hover:brightness-95 transition">
                    <span class="material-symbols-outlined text-sm">refresh</span>
                    Tampilkan Semua
                </a>
            </div>
            @endif
        </div>
        @endforelse
    </div>

    @if($testimonials->hasPages())
    <div class="mt-6">{{ $testimonials->links() }}</div>
    @endif

    <!-- Safe Delete Confirmation Modal (Proteksi Telegram) -->
    <div x-show="deleteModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white dark:bg-[#1e1e1e] rounded-2xl border border-border-subtle dark:border-[#2a2a2a] max-w-md w-full p-6 shadow-2xl space-y-4"
             @click.outside="deleteModalOpen = false">
            
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-950/50 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-xl">warning</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-on-surface dark:text-white" x-text="isForceDelete ? 'Hapus Testimoni Permanen?' : 'Pindahkan ke Sampah?'"></h3>
                    <p class="text-xs text-secondary dark:text-gray-400">Testimoni #<span x-text="testiNumber"></span></p>
                </div>
            </div>

            <p class="text-xs text-on-surface-variant dark:text-gray-300 leading-relaxed" x-show="!isForceDelete">
                Testimoni ini akan dipindahkan ke folder <strong>Sampah (Trash)</strong> dan dapat Anda pulihkan kapan saja.
            </p>
            <p class="text-xs text-red-600 dark:text-red-400 leading-relaxed" x-show="isForceDelete">
                Tindakan ini akan <strong>menghapus data dan file gambar secara permanen</strong> dari sistem server lokal.
            </p>

            <!-- Telegram Protection Toggle (Only if posted to telegram) -->
            <template x-if="isPostedToTelegram">
                <div class="p-3.5 rounded-xl border border-amber-200 dark:border-amber-900/40 bg-amber-50/50 dark:bg-amber-950/20 space-y-2">
                    <div class="flex items-center justify-between gap-3">
                        <label for="del-tele-check" class="text-xs font-semibold text-amber-900 dark:text-amber-300 cursor-pointer select-none">
                            Hapus juga postingan di Channel Telegram
                        </label>
                        <input id="del-tele-check" type="checkbox" x-model="deleteFromTelegram" 
                               class="w-4 h-4 rounded text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                    </div>
                    <p class="text-[11px] text-amber-800/80 dark:text-amber-400/80 leading-normal">
                        🛡️ <strong>Direkomendasikan dibiarkan mati:</strong> Postingan testimoni di Channel Telegram akan tetap aman dan tidak akan terhapus.
                    </p>
                </div>
            </template>

            <!-- Form Actions -->
            <form :action="deleteUrl" method="POST" class="flex justify-end gap-2.5 pt-2">
                @csrf
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="delete_from_telegram" :value="deleteFromTelegram ? '1' : '0'">

                <button type="button" @click="deleteModalOpen = false" 
                        class="px-4 py-2 bg-transparent border border-border-subtle dark:border-[#333] rounded-lg text-xs font-semibold text-on-surface-variant dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#252525] transition">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-xs font-semibold hover:bg-red-700 transition flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-sm" x-text="isForceDelete ? 'delete_forever' : 'delete'"></span>
                    <span x-text="isForceDelete ? 'Hapus Permanen' : 'Pindahkan ke Sampah'"></span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
