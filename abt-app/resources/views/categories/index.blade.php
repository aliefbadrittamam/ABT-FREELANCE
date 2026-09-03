@extends('layouts.app')

@section('title', 'Kategori Jasa — ABT-FREELANCE')
@section('header', 'Kategori Jasa')

@section('content')
<!-- Page Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8 gap-4">
    <div>
        <p class="text-secondary dark:text-gray-400 text-xs sm:text-sm mb-0.5 sm:mb-1">Manajemen Data</p>
        <h2 class="text-2xl sm:text-[32px] font-bold text-on-surface dark:text-white tracking-tight leading-tight sm:leading-10">Kategori Jasa</h2>
        <p class="text-xs text-on-surface-variant dark:text-gray-400 mt-0.5 sm:mt-1">Atur nama kategori serta Brand/Header invoice khusus per kategori jasa.</p>
    </div>
    <button @click="$dispatch('open-modal')" class="flex items-center px-4 sm:px-5 py-2.5 bg-primary-container text-on-surface rounded-lg font-semibold text-xs sm:text-sm shadow-sm hover:brightness-95 transition-all w-fit gap-2">
        <span class="material-symbols-outlined text-base sm:text-lg">add_box</span>
        Tambah Kategori
    </button>
</div>

<!-- Content Container -->
<div class="bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#2a2a2a] rounded-xl overflow-hidden shadow-sm transition-colors duration-200">
    <!-- Table Header (Filter/Search) -->
    <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-border-subtle dark:border-[#2a2a2a] bg-surface-container-low dark:bg-[#181818] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3" x-data="{ search: '' }">
        <h3 class="text-xs font-semibold text-on-surface dark:text-white uppercase tracking-wider">Daftar Kategori & Brand Invoice</h3>
        <div class="relative w-full sm:w-auto">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-secondary dark:text-gray-400 text-sm">filter_list</span>
            <input x-model="search" @input="$dispatch('filter-categories', search)" class="w-full sm:w-48 pl-9 pr-4 py-1.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-md text-xs sm:text-sm text-on-surface dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Filter kategori..." type="text"/>
        </div>
    </div>

    <!-- Table (Scrollable on mobile) -->
    <div class="overflow-x-auto w-full">
        <table class="w-full text-left border-collapse min-w-[600px]">
            <thead>
                <tr class="border-b border-border-subtle dark:border-[#2a2a2a] text-secondary dark:text-gray-400 text-[11px] uppercase tracking-wider font-semibold bg-surface-container-low/50 dark:bg-[#181818]/50">
                    <th class="py-3 px-4 sm:px-6 w-12 sm:w-16 text-center">No</th>
                    <th class="py-3 px-4 sm:px-6">Kategori & Format Nomor</th>
                    <th class="py-3 px-4 sm:px-6">Brand & Tagline Invoice</th>
                    <th class="py-3 px-4 sm:px-6">Total Invoice</th>
                    <th class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-xs sm:text-sm text-on-surface dark:text-gray-200 divide-y divide-border-subtle dark:divide-[#2a2a2a]" x-data="{ searchQuery: '' }" @filter-categories.window="searchQuery = $event.detail.toLowerCase()">
                @php
                    $icons = ['assignment', 'language', 'brush', 'code', 'analytics', 'design_services'];
                @endphp
                @forelse($categories as $index => $category)
                @php
                    $icon = $icons[$index % count($icons)];
                @endphp
                <tr class="hover:bg-surface-variant/30 dark:hover:bg-[#252525] transition-colors group" 
                    x-show="!searchQuery || '{{ strtolower($category->name . ' ' . $category->brand_name) }}'.includes(searchQuery)"
                    x-data="{ editing: false, catName: '{{ $category->name }}', brandName: '{{ $category->brand_name }}', tagline: '{{ $category->tagline }}' }">
                    <td class="py-3.5 px-4 sm:px-6 text-secondary dark:text-gray-400 text-center font-medium">{{ $index + 1 }}</td>
                    <td class="py-3.5 px-4 sm:px-6 font-medium" x-show="!editing">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded bg-surface-container dark:bg-[#2a2a2a] flex items-center justify-center text-primary dark:text-primary-container border border-border-subtle/50 dark:border-[#333] shrink-0">
                                <span class="material-symbols-outlined text-sm">{{ $icon }}</span>
                            </div>
                            <div>
                                <span class="text-on-surface dark:text-white font-semibold block">{{ $category->name }}</span>
                                <span class="text-[11px] text-secondary dark:text-gray-400">
                                    Brand: <strong class="text-on-surface dark:text-gray-200">{{ $category->brand_name ?: 'ABT-FREELANCE' }}</strong>
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 sm:px-6 text-on-surface-variant dark:text-gray-400" x-show="!editing">
                        <span class="text-xs">{{ $category->tagline ?: 'Invoice & Jasa Professional' }}</span>
                    </td>
                    <td class="py-3.5 px-4 sm:px-6" x-show="!editing">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-surface-container dark:bg-[#2a2a2a] text-on-surface dark:text-gray-200">
                            {{ $category->invoices_count }} Invoice
                        </span>
                    </td>
                    <td class="py-3.5 px-4 sm:px-6 text-right" x-show="!editing">
                        <div class="flex items-center justify-end gap-1">
                            <button @click="editing = true" class="p-1.5 text-secondary dark:text-gray-400 hover:text-primary dark:hover:text-primary-container hover:bg-surface-container dark:hover:bg-[#333] rounded-md transition-colors" title="Edit Kategori & Brand">
                                <span class="material-symbols-outlined text-lg sm:text-[20px]">edit</span>
                            </button>
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-secondary dark:text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors" title="Hapus">
                                    <span class="material-symbols-outlined text-lg sm:text-[20px]">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>

                    <!-- Inline Edit Mode -->
                    <td colspan="5" class="py-3.5 px-4 sm:px-6 bg-surface-container-low/40 dark:bg-[#181818]" x-show="editing" x-cloak>
                        <form action="{{ route('categories.update', $category) }}" method="POST" class="space-y-3">
                            @csrf @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Nama Kategori</label>
                                    <input type="text" name="name" x-model="catName" required class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Header Brand Invoice</label>
                                    <input type="text" name="brand_name" x-model="brandName" placeholder="Default: ABT-FREELANCE" class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Tagline Invoice</label>
                                    <input type="text" name="tagline" x-model="tagline" placeholder="Default: Invoice & Jasa Professional" class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="editing = false; catName = '{{ $category->name }}'; brandName = '{{ $category->brand_name }}'; tagline = '{{ $category->tagline }}'" class="text-xs sm:text-sm text-secondary dark:text-gray-400 px-3 py-1.5 hover:text-on-surface dark:hover:text-white">Batal</button>
                                <button type="submit" class="bg-primary-container text-on-surface font-semibold text-xs sm:text-sm px-4 py-1.5 rounded-lg hover:brightness-95">Simpan Perubahan</button>
                            </div>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-16 text-center text-secondary dark:text-gray-400">
                        <div class="w-14 h-14 bg-surface-container dark:bg-[#252525] rounded-full flex items-center justify-center mx-auto mb-3 border border-border-subtle dark:border-[#333]">
                            <span class="material-symbols-outlined text-secondary dark:text-gray-400 text-2xl">category</span>
                        </div>
                        <h4 class="text-sm sm:text-base font-semibold text-on-surface dark:text-white mb-1">Belum ada kategori</h4>
                        <p class="text-xs sm:text-sm text-secondary dark:text-gray-400 max-w-md mx-auto">Mulai organisir jasa Anda dengan menambahkan kategori baru.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Tambah Kategori (Mobile Optimized) -->
<div x-data="{ open: false }" @open-modal.window="open = true" x-cloak>
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="open = false" x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"></div>

        <!-- Modal Content Card -->
        <div class="relative bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#2a2a2a] rounded-xl w-full max-w-lg shadow-xl overflow-hidden flex flex-col z-10 max-h-[90vh]"
             x-show="open" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <!-- Modal Header -->
            <div class="px-5 sm:px-6 py-4 sm:py-5 border-b border-border-subtle dark:border-[#2a2a2a] flex justify-between items-center bg-surface dark:bg-[#181818]">
                <h3 class="font-semibold text-on-surface dark:text-white text-base sm:text-lg">Tambah Kategori & Setting Brand</h3>
                <button class="text-secondary dark:text-gray-400 hover:text-on-surface dark:hover:text-white transition-colors p-1" @click="open = false">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
            <!-- Modal Body -->
            <form action="{{ route('categories.store') }}" method="POST" class="overflow-y-auto">
                @csrf
                <div class="px-5 sm:px-6 py-5 sm:py-6 bg-white dark:bg-[#1e1e1e] space-y-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1" for="kategori-name">Nama Kategori</label>
                        <input class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary placeholder:text-secondary/50" 
                               id="kategori-name" name="name" required placeholder="Misal: Jasa Pembuatan Website" type="text" autofocus/>
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1" for="kategori-brand">Brand Header Invoice (Opsional)</label>
                        <input class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary placeholder:text-secondary/50" 
                               id="kategori-brand" name="brand_name" placeholder="Misal: ABT-DEV STUDIO" type="text"/>
                        <p class="text-[11px] text-secondary dark:text-gray-400 mt-1">Nama ini akan menggantikan tulisan "ABT-FREELANCE" di header invoice.</p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1" for="kategori-tagline">Tagline Invoice (Opsional)</label>
                        <input class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary placeholder:text-secondary/50" 
                               id="kategori-tagline" name="tagline" placeholder="Misal: Fullstack Web Development & Solutions" type="text"/>
                    </div>
                </div>
                <!-- Modal Footer -->
                <div class="px-5 sm:px-6 py-4 bg-surface-container-low dark:bg-[#181818] border-t border-border-subtle dark:border-[#2a2a2a] flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 sm:px-5 py-2 sm:py-2.5 bg-white dark:bg-[#252525] text-on-surface dark:text-white border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm font-semibold hover:bg-surface-variant dark:hover:bg-[#333] transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 sm:px-5 py-2 sm:py-2.5 bg-primary-container text-on-surface border border-transparent rounded-lg text-xs sm:text-sm font-semibold shadow-sm hover:brightness-95 transition-all">
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
