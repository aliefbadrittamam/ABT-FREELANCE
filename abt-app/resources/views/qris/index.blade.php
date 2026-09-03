@extends('layouts.app')

@section('title', 'QRIS — ABT-FREELANCE')
@section('header', 'QRIS')

@section('content')
<header class="mb-8">
    <h1 class="text-[32px] font-bold text-on-surface tracking-tight leading-10">Pengaturan QRIS</h1>
    <p class="text-sm text-on-surface-variant mt-1">Upload gambar QRIS untuk ditampilkan di setiap invoice.</p>
</header>

<div class="max-w-lg">
    <div class="bg-white rounded-xl border border-border-subtle p-8">
        @if($hasQris)
        <div class="mb-6 text-center">
            <img src="{{ asset('storage/assets/qris.png') }}?v={{ time() }}" alt="QRIS" class="max-w-[240px] mx-auto rounded-xl border border-border-subtle">
            <div class="flex items-center justify-center gap-1 mt-3 text-status-lunas text-xs font-semibold">
                <span class="material-symbols-outlined text-sm">check_circle</span>
                QRIS sudah diupload
            </div>
        </div>
        @endif

        <form action="{{ route('qris.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-6">
                <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-2">{{ $hasQris ? 'Ganti Gambar QRIS' : 'Upload Gambar QRIS' }}</label>
                <div class="border-2 border-dashed border-border-subtle rounded-xl p-8 text-center hover:border-primary-container transition-colors cursor-pointer" onclick="document.getElementById('qris_input').click()">
                    <span class="material-symbols-outlined text-4xl text-on-surface-variant/30 mb-2">qr_code_2</span>
                    <p class="text-sm text-on-surface-variant">Klik untuk pilih gambar</p>
                    <input type="file" name="qris_image" id="qris_input" accept="image/*" required class="hidden">
                </div>
                @error('qris_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full bg-primary-container text-on-surface font-semibold py-2.5 rounded-lg text-sm hover:brightness-95 transition">
                {{ $hasQris ? 'Update QRIS' : 'Upload QRIS' }}
            </button>
        </form>
    </div>
</div>
@endsection
