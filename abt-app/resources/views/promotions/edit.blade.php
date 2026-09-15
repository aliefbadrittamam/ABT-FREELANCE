@extends('layouts.app')

@section('title', 'Edit Materi Iklan — ABT-FREELANCE')
@section('header', 'Edit Materi Iklan')

@section('content')
<div class="mb-6 sm:mb-8">
    <a href="{{ route('promotions.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-secondary dark:text-gray-400 hover:text-primary dark:hover:text-primary-container mb-2">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        Kembali ke Daftar Iklan
    </a>
    <h1 class="text-2xl sm:text-[30px] font-black text-on-surface dark:text-white tracking-tight leading-tight">Edit Materi Iklan</h1>
    <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5">Perbarui teks penawaran jasa, poster promosi, atau kategori layanan.</p>
</div>

<div class="max-w-4xl bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-8 shadow-sm"
     x-data="{
        title: '{{ old('title', $promotion->title) }}',
        categoryType: '{{ old('category_type', $promotion->category_type) }}',
        tagline: '{{ old('tagline', $promotion->tagline) }}',
        bannerOption: '{{ old('banner_option', 'keep') }}',
        copywriting: '{{ old('copywriting', $promotion->copywriting) }}'
     }">

    <form action="{{ route('promotions.update', $promotion) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="space-y-4 sm:space-y-5">
            <!-- Title -->
            <div>
                <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1.5">Judul Penawaran Iklan</label>
                <input type="text" name="title" x-model="title" required
                    class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white font-medium focus:ring-2 focus:ring-primary outline-none">
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Category & Tagline -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1.5">Kategori Jasa</label>
                    <div class="space-y-2">
                        <select name="category_type" x-model="categoryType" required class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm font-medium focus:ring-2 focus:ring-primary outline-none">
                            <option value="joki" {{ $promotion->category_type === 'joki' ? 'selected' : '' }}>Joki Tugas & Skripsi</option>
                            <option value="website" {{ $promotion->category_type === 'website' ? 'selected' : '' }}>Jasa Website & Coding</option>
                            <option value="tournament" {{ $promotion->category_type === 'tournament' ? 'selected' : '' }}>Turnamen eFootball</option>
                            <option value="general" {{ $promotion->category_type === 'general' ? 'selected' : '' }}>Umum / Branding</option>
                        </select>
                        <select name="category_id" class="w-full px-3.5 py-2 bg-surface dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-secondary dark:text-gray-400 rounded-lg text-xs outline-none">
                            <option value="">-- Tautkan ke Master Kategori (Opsional) --</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $promotion->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }} ({{ $cat->prefix }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1.5">Tagline / Poin Unggulan</label>
                    <input type="text" name="tagline" x-model="tagline" placeholder="Contoh: Cepat • Bebas Plagiasi • Garansi Revisi"
                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                </div>
            </div>

            <!-- Banner Poster Option -->
            <div class="p-4 rounded-xl border border-border-subtle dark:border-[#2a2a2a] bg-surface dark:bg-[#181818] space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-[11px] font-bold text-on-surface dark:text-white uppercase tracking-wider">
                        Pengaturan Banner / Poster Iklan:
                    </label>
                    @if($promotion->banner_path)
                    <span class="text-[10px] text-emerald-500 font-bold flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">image</span> Poster Terpasang ({{ $promotion->banner_type }})
                    </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <label class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition text-xs"
                           :class="bannerOption === 'keep' ? 'bg-primary-container/20 border-primary text-on-surface dark:text-white font-bold' : 'border-border-subtle dark:border-[#333] text-secondary dark:text-gray-400'">
                        <input type="radio" name="banner_option" value="keep" x-model="bannerOption" class="text-primary focus:ring-primary">
                        <span>Tetap Pakai</span>
                    </label>

                    <label class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition text-xs"
                           :class="bannerOption === 'generate' ? 'bg-primary-container/20 border-primary text-on-surface dark:text-white font-bold' : 'border-border-subtle dark:border-[#333] text-secondary dark:text-gray-400'">
                        <input type="radio" name="banner_option" value="generate" x-model="bannerOption" class="text-primary focus:ring-primary">
                        <span>✨ Re-Generate</span>
                    </label>

                    <label class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition text-xs"
                           :class="bannerOption === 'upload' ? 'bg-primary-container/20 border-primary text-on-surface dark:text-white font-bold' : 'border-border-subtle dark:border-[#333] text-secondary dark:text-gray-400'">
                        <input type="radio" name="banner_option" value="upload" x-model="bannerOption" class="text-primary focus:ring-primary">
                        <span>📤 Upload Baru</span>
                    </label>

                    <label class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition text-xs"
                           :class="bannerOption === 'remove' ? 'bg-primary-container/20 border-primary text-on-surface dark:text-white font-bold' : 'border-border-subtle dark:border-[#333] text-secondary dark:text-gray-400'">
                        <input type="radio" name="banner_option" value="remove" x-model="bannerOption" class="text-primary focus:ring-primary">
                        <span>Hapus Poster</span>
                    </label>
                </div>

                <div x-show="bannerOption === 'upload'" x-cloak class="pt-2">
                    <input type="file" name="banner_file" accept="image/*"
                           class="w-full text-xs text-on-surface dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-container file:text-on-surface hover:file:brightness-95 cursor-pointer">
                </div>
            </div>

            <!-- Copywriting Textarea -->
            <div>
                <label class="block text-[11px] font-bold text-on-surface dark:text-gray-300 uppercase tracking-wider mb-1.5">Teks Copywriting Iklan</label>
                <textarea name="copywriting" x-model="copywriting" rows="8" required
                    class="w-full px-3.5 py-2.5 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm font-mono text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none leading-relaxed"></textarea>
                @error('copywriting') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-border-subtle dark:border-[#2a2a2a] mt-6">
            <a href="{{ route('promotions.index') }}" class="px-5 py-2.5 bg-transparent dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-secondary dark:text-gray-300 font-semibold hover:bg-surface-variant transition">Batal</a>
            <button type="submit" class="px-6 py-2.5 bg-primary-container text-on-surface font-bold text-xs sm:text-sm rounded-lg hover:brightness-95 transition shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">save</span>
                Perbarui Materi Iklan
            </button>
        </div>
    </form>
</div>
@endsection
