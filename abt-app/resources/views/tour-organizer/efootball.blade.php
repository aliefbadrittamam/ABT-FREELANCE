@extends('layouts.app')

@section('title', 'eFootball Mobile — Tour Organizer')
@section('header', 'eFootball Mobile')

@section('content')
<header class="mb-6 sm:mb-8">
    <div class="flex items-center gap-2 text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mb-2">
        <a href="{{ route('tour-organizer.index') }}" class="hover:text-on-surface dark:hover:text-white transition">Tour Organizer</a>
        <span class="material-symbols-outlined text-base">chevron_right</span>
        <span class="text-on-surface dark:text-white font-medium">eFootball Mobile</span>
    </div>
    <h1 class="text-2xl sm:text-[32px] font-bold text-on-surface dark:text-white tracking-tight leading-tight sm:leading-10">eFootball Mobile</h1>
    <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-1">Turnamen & Event eFootball Mobile.</p>
</header>

<div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-12 text-center max-w-2xl mx-auto shadow-sm">
    <div class="w-16 h-16 rounded-2xl bg-primary-container/20 text-primary dark:text-primary-container border border-primary/20 flex items-center justify-center mx-auto mb-4">
        <span class="material-symbols-outlined text-3xl">sports_esports</span>
    </div>
    <h2 class="text-lg font-bold text-on-surface dark:text-white mb-2">Modul eFootball Mobile Segera Hadir</h2>
    <p class="text-xs sm:text-sm text-secondary dark:text-gray-400 leading-relaxed max-w-md mx-auto">
        Halaman ini telah disiapkan untuk manajemen kompetisi, bracket, jadwal tanding, dan pendaftaran peserta turnamen eFootball Mobile.
    </p>
</div>
@endsection
