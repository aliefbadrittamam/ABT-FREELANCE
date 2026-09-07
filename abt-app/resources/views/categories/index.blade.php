@extends('layouts.app')

@section('title', 'Kategori & Sub-Kategori — ABT-FREELANCE')
@section('header', 'Kategori & Master Data')

@section('content')
<!-- Page Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 sm:mb-8 gap-4">
    <div>
        <h1 class="text-2xl sm:text-[30px] font-black text-on-surface dark:text-white tracking-tight leading-tight">Kategori & Master Data</h1>
        <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5">Kelola Kategori Utama, Sub-Kategori (Jenis Tugas/Output), dan Master Jurusan Klien.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <button @click="$dispatch('open-subcat-modal')" class="flex items-center px-3.5 py-2 bg-surface-container dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg font-bold text-xs shadow-2xs hover:bg-gray-100 dark:hover:bg-[#333] transition-all gap-1.5">
            <span class="material-symbols-outlined text-base">alt_route</span>
            + Sub-Kategori
        </button>
        <button @click="$dispatch('open-major-modal')" class="flex items-center px-3.5 py-2 bg-surface-container dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg font-bold text-xs shadow-2xs hover:bg-gray-100 dark:hover:bg-[#333] transition-all gap-1.5">
            <span class="material-symbols-outlined text-base">school</span>
            + Jurusan
        </button>
        <button @click="$dispatch('open-modal')" class="flex items-center px-4 py-2 bg-primary-container text-on-surface rounded-lg font-bold text-xs shadow-sm hover:brightness-95 transition-all gap-1.5">
            <span class="material-symbols-outlined text-base">add_box</span>
            + Kategori Utama
        </button>
    </div>
</div>

<!-- 1. Section: Kategori Utama & Sub-Kategori -->
<div class="bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#2a2a2a] rounded-xl overflow-hidden shadow-sm transition-colors duration-200 mb-8">
    <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-border-subtle dark:border-[#2a2a2a] bg-surface-container-low dark:bg-[#181818] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3" x-data="{ search: '' }">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">category</span>
            <h3 class="text-xs font-bold text-on-surface dark:text-white uppercase tracking-wider">Kategori Utama & Sub-Kategori Layanan</h3>
        </div>
        <div class="relative w-full sm:w-auto">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-secondary dark:text-gray-400 text-sm">filter_list</span>
            <input x-model="search" @input="$dispatch('filter-categories', search)" class="w-full sm:w-56 pl-9 pr-4 py-1.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-md text-xs sm:text-sm text-on-surface dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Cari kategori / sub-kategori..." type="text"/>
        </div>
    </div>

    <div class="overflow-x-auto w-full">
        <table class="w-full text-left border-collapse min-w-[700px]">
            <thead>
                <tr class="border-b border-border-subtle dark:border-[#2a2a2a] text-secondary dark:text-gray-400 text-[11px] uppercase tracking-wider font-semibold bg-surface-container-low/50 dark:bg-[#181818]/50">
                    <th class="py-3 px-4 sm:px-6 w-12 sm:w-16 text-center">No</th>
                    <th class="py-3 px-4 sm:px-6">Kategori Utama & Kode</th>
                    <th class="py-3 px-4 sm:px-6">Sub-Kategori (Jenis Tugas/Output)</th>
                    <th class="py-3 px-4 sm:px-6">Brand & Tagline</th>
                    <th class="py-3 px-4 sm:px-6">Order</th>
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
                    $subCatNames = implode(' ', $category->subCategories->pluck('name')->toArray());
                @endphp
                <tr class="hover:bg-surface-variant/30 dark:hover:bg-[#252525] transition-colors group" 
                    x-show="!searchQuery || '{{ strtolower($category->name . ' ' . $category->prefix . ' ' . $category->brand_name . ' ' . $subCatNames) }}'.includes(searchQuery)"
                    x-data="{ editing: false, catName: '{{ $category->name }}', prefix: '{{ $category->invoice_prefix }}', brandName: '{{ $category->brand_name }}', tagline: '{{ $category->tagline }}' }">
                    <td class="py-3.5 px-4 sm:px-6 text-secondary dark:text-gray-400 text-center font-medium">{{ $index + 1 }}</td>
                    <td class="py-3.5 px-4 sm:px-6 font-medium" x-show="!editing">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-surface-container dark:bg-[#2a2a2a] flex items-center justify-center text-primary dark:text-primary-container border border-border-subtle/50 dark:border-[#333] shrink-0">
                                <span class="material-symbols-outlined text-base">{{ $icon }}</span>
                            </div>
                            <div>
                                <span class="text-on-surface dark:text-white font-bold block">{{ $category->name }}</span>
                                <span class="inline-flex items-center gap-1 font-mono text-[11px] font-bold text-primary dark:text-primary-container bg-primary-container/20 px-2 py-0.5 rounded mt-0.5">
                                    INV-{{ $category->prefix }}-xxx
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4 sm:px-6" x-show="!editing">
                        <div class="flex flex-wrap gap-1.5 items-center">
                            @forelse($category->subCategories as $subCat)
                            <span class="inline-flex items-center gap-1 bg-surface-container dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-gray-300 text-[11px] px-2 py-0.5 rounded-md font-medium">
                                {{ $subCat->name }}
                                <form action="{{ route('categories.sub-category.destroy', $subCat) }}" method="POST" class="inline" onsubmit="return confirm('Hapus sub-kategori {{ $subCat->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-500 font-bold ml-0.5">×</button>
                                </form>
                            </span>
                            @empty
                            <span class="text-xs text-secondary/60 dark:text-gray-500 italic">Belum ada sub-kategori</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="py-3.5 px-4 sm:px-6 text-on-surface-variant dark:text-gray-400" x-show="!editing">
                        <p class="font-semibold text-on-surface dark:text-gray-200 text-xs">{{ $category->brand_name ?: 'ABT-FREELANCE (Default)' }}</p>
                        <p class="text-[11px] text-secondary dark:text-gray-400">{{ $category->tagline ?: 'Invoice & Jasa Professional' }}</p>
                    </td>
                    <td class="py-3.5 px-4 sm:px-6" x-show="!editing">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-surface-container dark:bg-[#2a2a2a] text-on-surface dark:text-gray-200">
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
                    <td colspan="6" class="py-4 px-4 sm:px-6 bg-surface-container-low/40 dark:bg-[#181818]" x-show="editing" x-cloak>
                        <form action="{{ route('categories.update', $category) }}" method="POST" class="space-y-3">
                            @csrf @method('PUT')
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Nama Kategori</label>
                                    <input type="text" name="name" x-model="catName" required class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none font-medium">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Prefix Kode (contoh: JOKI)</label>
                                    <input type="text" name="invoice_prefix" x-model="prefix" placeholder="Misal: JOKI" class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none font-mono font-bold uppercase">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Brand Header Invoice</label>
                                    <input type="text" name="brand_name" x-model="brandName" placeholder="Default: ABT-FREELANCE" class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Tagline Invoice</label>
                                    <input type="text" name="tagline" x-model="tagline" placeholder="Default: Invoice & Jasa Professional" class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="editing = false; catName = '{{ $category->name }}'; prefix = '{{ $category->invoice_prefix }}'; brandName = '{{ $category->brand_name }}'; tagline = '{{ $category->tagline }}'" class="text-xs text-secondary dark:text-gray-400 px-3 py-1.5 hover:text-on-surface dark:hover:text-white">Batal</button>
                                <button type="submit" class="bg-primary-container text-on-surface font-bold text-xs px-4 py-1.5 rounded-lg hover:brightness-95 shadow-sm">Simpan Perubahan</button>
                            </div>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-secondary dark:text-gray-400">
                        <p class="text-sm font-semibold">Belum ada kategori utama.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- 2. Section: Master Jurusan / Spesialisasi -->
<div class="bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#2a2a2a] rounded-xl overflow-hidden shadow-sm p-4 sm:p-6 transition-colors duration-200">
    <div class="flex items-center justify-between mb-4 border-b border-border-subtle dark:border-[#2a2a2a] pb-3">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-xl">school</span>
            <h3 class="text-xs sm:text-sm font-bold text-on-surface dark:text-white uppercase tracking-wider">Master Data Jurusan / Spesialisasi Klien</h3>
        </div>
        <button @click="$dispatch('open-major-modal')" class="text-xs font-bold text-primary dark:text-primary-container hover:underline flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">add</span> Tambah Jurusan
        </button>
    </div>

    <div class="flex flex-wrap gap-2">
        @forelse($majors as $major)
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-surface-container dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-xs text-on-surface dark:text-gray-200">
            <span class="font-medium">{{ $major->name }}</span>
            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-gray-200 dark:bg-[#333] font-bold text-secondary dark:text-gray-400">
                {{ $major->invoices_count }}
            </span>
            <form action="{{ route('categories.major.destroy', $major) }}" method="POST" class="inline" onsubmit="return confirm('Hapus jurusan {{ $major->name }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-gray-400 hover:text-red-500 font-bold ml-1">×</button>
            </form>
        </div>
        @empty
        <p class="text-xs text-secondary/60 dark:text-gray-500 italic">Belum ada data jurusan tersimpan.</p>
        @endforelse
    </div>
</div>

<!-- Modal: Tambah Kategori Utama -->
<div x-data="{ open: false }" @open-modal.window="open = true" x-cloak>
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="open = false" x-show="open"></div>
        <div class="relative bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#2a2a2a] rounded-xl w-full max-w-lg shadow-xl overflow-hidden flex flex-col z-10 max-h-[90vh]" x-show="open">
            <div class="px-5 sm:px-6 py-4 border-b border-border-subtle dark:border-[#2a2a2a] flex justify-between items-center bg-surface dark:bg-[#181818]">
                <h3 class="font-bold text-on-surface dark:text-white text-base">Tambah Kategori Utama</h3>
                <button class="text-secondary hover:text-on-surface dark:hover:text-white p-1" @click="open = false">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="px-5 sm:px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1">Nama Kategori</label>
                        <input class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:outline-none focus:border-primary font-medium" 
                               name="name" required placeholder="Misal: Jasa Pembuatan Website" type="text" autofocus/>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1">Kode Prefix Invoice</label>
                        <input class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white font-mono font-bold uppercase" 
                               name="invoice_prefix" placeholder="Misal: WEB" type="text"/>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1">Brand Header Invoice</label>
                        <input class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white" 
                               name="brand_name" placeholder="Misal: ABT-DEV STUDIO" type="text"/>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1">Tagline Invoice</label>
                        <input class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white" 
                               name="tagline" placeholder="Misal: Fullstack Web Solutions" type="text"/>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-4 bg-surface-container-low dark:bg-[#181818] border-t border-border-subtle dark:border-[#2a2a2a] flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-primary-container text-on-surface rounded-lg text-xs font-bold shadow-sm">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Tambah Sub-Kategori -->
<div x-data="{ open: false }" @open-subcat-modal.window="open = true" x-cloak>
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="open = false" x-show="open"></div>
        <div class="relative bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#2a2a2a] rounded-xl w-full max-w-md shadow-xl overflow-hidden flex flex-col z-10" x-show="open">
            <div class="px-5 sm:px-6 py-4 border-b border-border-subtle dark:border-[#2a2a2a] flex justify-between items-center bg-surface dark:bg-[#181818]">
                <h3 class="font-bold text-on-surface dark:text-white text-base">Tambah Sub-Kategori Baru</h3>
                <button class="text-secondary hover:text-on-surface dark:hover:text-white p-1" @click="open = false">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
            <form action="{{ route('categories.sub-category.store') }}" method="POST">
                @csrf
                <div class="px-5 sm:px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1">Kategori Utama</label>
                        <select name="category_id" required class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white font-medium">
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1">Nama Sub-Kategori (Jenis Output/Tugas)</label>
                        <input class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white font-medium" 
                               name="name" required placeholder="Misal: Skripsi, Web Landing Page, Design Logo" type="text"/>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-4 bg-surface-container-low dark:bg-[#181818] border-t border-border-subtle dark:border-[#2a2a2a] flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-primary-container text-on-surface rounded-lg text-xs font-bold shadow-sm">Tambah Sub-Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Tambah Jurusan -->
<div x-data="{ open: false }" @open-major-modal.window="open = true" x-cloak>
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="open = false" x-show="open"></div>
        <div class="relative bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#2a2a2a] rounded-xl w-full max-w-md shadow-xl overflow-hidden flex flex-col z-10" x-show="open">
            <div class="px-5 sm:px-6 py-4 border-b border-border-subtle dark:border-[#2a2a2a] flex justify-between items-center bg-surface dark:bg-[#181818]">
                <h3 class="font-bold text-on-surface dark:text-white text-base">Tambah Jurusan / Spesialisasi Baru</h3>
                <button class="text-secondary hover:text-on-surface dark:hover:text-white p-1" @click="open = false">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
            <form action="{{ route('categories.major.store') }}" method="POST">
                @csrf
                <div class="px-5 sm:px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1">Nama Jurusan / Spesialisasi</label>
                        <input class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white font-medium" 
                               name="name" required placeholder="Misal: Teknik Elektro, Farmasi, D3 Sistem Informasi" type="text"/>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-4 bg-surface-container-low dark:bg-[#181818] border-t border-border-subtle dark:border-[#2a2a2a] flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-primary-container text-on-surface rounded-lg text-xs font-bold shadow-sm">Tambah Jurusan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@endsection
