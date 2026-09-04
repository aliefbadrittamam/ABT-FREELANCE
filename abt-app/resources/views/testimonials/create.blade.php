@extends('layouts.app')

@section('title', 'Upload Testimoni — ABT-FREELANCE')
@section('header', 'Testimoni')

@section('content')
<header class="mb-6 sm:mb-8">
    <h1 class="text-2xl sm:text-[32px] font-bold text-on-surface dark:text-white tracking-tight leading-tight sm:leading-10">Upload Testimoni Baru</h1>
    <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5 sm:mt-1">Upload 1 sampai 4 gambar bukti tugas dan sesuaikan format caption Telegram.</p>
</header>

@if(isset($fromInvoice) && $fromInvoice)
<!-- Auto-fill from Invoice Banner -->
<div class="max-w-3xl mb-6 p-4 rounded-xl bg-primary-container/20 border border-primary-container/40 dark:border-primary-container/30 flex items-start gap-3 shadow-xs">
    <div class="w-8 h-8 rounded-lg bg-primary-container text-on-surface flex items-center justify-center shrink-0 font-bold">
        <span class="material-symbols-outlined text-lg">receipt_long</span>
    </div>
    <div class="flex-1 text-xs">
        <strong class="text-sm font-bold text-on-surface dark:text-white block mb-0.5">
            Auto-Fill dari Invoice: {{ $fromInvoice->invoice_number }} ({{ $fromInvoice->client_name }})
        </strong>
        <p class="text-secondary dark:text-gray-300 leading-relaxed">
            Data judul proyek, kategori/jurusan, deskripsi, dan nama klien telah diisi secara otomatis. Anda hanya perlu mengunggah 1 s/d 4 foto bukti untuk diposting ke Telegram.
        </p>
    </div>
</div>
@endif

<div class="max-w-3xl" x-data="{
    number: '{{ old('testimonial_number', $nextNumber) }}',
    major: '{{ old('major', isset($fromInvoice) && $fromInvoice ? ($fromInvoice->category->name ?? '') : '') }}',
    taskTitle: '{{ old('task_title', isset($fromInvoice) && $fromInvoice ? $fromInvoice->title : '') }}',
    deliverables: '{{ old('deliverables', isset($fromInvoice) && $fromInvoice ? $fromInvoice->description : '') }}',
    notes: '{{ old('caption', '') }}',
    get telegramPreview() {
        let n = this.number || '1';
        let body = [this.major, this.taskTitle].filter(Boolean).join(' ');
        let main = body ? body : 'Tugas Selesai';
        let res = '#' + n + '. ' + main + '.';
        if (this.deliverables) {
            let cleanDeliv = this.deliverables.replace(/^\(+|\)+$/g, '').trim();
            res += ' (' + cleanDeliv + ')';
        }
        if (this.notes) {
            res += '\n\n' + this.notes;
        }
        return res;
    }
}">
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-8 shadow-sm transition-colors duration-200">
        <form action="{{ route('testimonials.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @if(isset($fromInvoice) && $fromInvoice)
            <input type="hidden" name="invoice_id" value="{{ $fromInvoice->id }}">
            @endif

            <!-- 1 to 4 Slot Upload Grid -->
            <div class="mb-6 sm:mb-8">
                <div class="flex items-center justify-between mb-2 sm:mb-3">
                    <label class="block text-[11px] font-semibold text-on-surface dark:text-white uppercase tracking-wider">
                        Upload Bukti Gambar <span class="text-secondary dark:text-gray-400 font-normal">(Bebas 1 s/d 4 Foto)</span>
                    </label>
                    <span class="text-[11px] text-primary dark:text-primary-container font-medium">Minimal 1 foto</span>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    @foreach(['tugas' => '1. Tugas', 'chat' => '2. Chat', 'hasil' => '3. Hasil', 'pelunasan' => '4. Pelunasan'] as $slot => $label)
                    <div x-data="{ preview: null }">
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1.5">{{ $label }}</label>
                        <div class="relative border-2 border-dashed border-border-subtle dark:border-[#333] rounded-xl text-center hover:border-primary-container cursor-pointer transition-colors aspect-square flex items-center justify-center overflow-hidden bg-surface dark:bg-[#181818]"
                             @click="$refs.input_{{ $slot }}.click()">
                            <template x-if="preview">
                                <img :src="preview" class="w-full h-full object-cover rounded-lg">
                            </template>
                            <template x-if="!preview">
                                <div class="flex flex-col items-center gap-1.5 sm:gap-2 p-2 text-center">
                                    <span class="material-symbols-outlined text-2xl text-on-surface-variant/30 dark:text-gray-600">add_photo_alternate</span>
                                    <p class="text-[10px] text-on-surface-variant dark:text-gray-400">Pilih Foto</p>
                                </div>
                            </template>
                            <input type="file" name="image_{{ $slot }}" accept="image/*" class="hidden"
                                   x-ref="input_{{ $slot }}"
                                   @change="preview = URL.createObjectURL($event.target.files[0])">
                        </div>
                        @error("image_{$slot}") <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @endforeach
                </div>
                <p class="text-[11px] text-secondary dark:text-gray-400 mt-2.5">
                    💡 <em>Tips:</em> Jika hanya 1 foto, gambar akan langsung ditampilkan utuh. Jika 2-4 foto, sistem akan otomatis menyusunnya menjadi kolase rapi.
                </p>
            </div>

            <!-- Structured Telegram Caption Builder -->
            <div class="mb-6 sm:mb-8 p-4 sm:p-6 bg-surface dark:bg-[#181818] rounded-xl border border-border-subtle dark:border-[#2a2a2a] space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-on-surface dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary dark:text-primary-container text-base">send</span>
                        Template Caption Telegram
                    </h3>
                    <span class="text-[10px] sm:text-[11px] text-secondary dark:text-gray-400">Format otomatis</span>
                </div>

                <!-- Number + Major + Task -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Nomor Testi</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-secondary dark:text-gray-400 font-bold">#</span>
                            <input type="number" name="testimonial_number" x-model="number" required min="1"
                                class="w-full pl-8 pr-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm font-semibold text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Jurusan / Kategori</label>
                        <input type="text" name="major" x-model="major" placeholder="Misal: Sistem Informasi"
                            class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Judul / Detail Tugas</label>
                        <input type="text" name="task_title" x-model="taskTitle" placeholder="Misal: UAS 2 dan 3"
                            class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                    </div>
                </div>

                <!-- Deliverables (Output) -->
                <div>
                    <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Output / Hasil Tugas (dalam kurung)</label>
                    <input type="text" name="deliverables" x-model="deliverables" placeholder="Misal: Makalah, Jurnal, Proposal kegiatan dan PPT"
                        class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                </div>

                <!-- Optional: Client Name & Notes -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Nama Klien (Opsional/Internal)</label>
                        <input type="text" name="client_name" value="{{ old('client_name', isset($fromInvoice) && $fromInvoice ? $fromInvoice->client_name : '') }}" placeholder="Misal: Kak Sarah"
                            class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Catatan Tambahan (Opsional)</label>
                        <input type="text" name="caption" x-model="notes" placeholder="Tambahan catatan..."
                            class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                    </div>
                </div>

                <!-- Live Preview Box -->
                <div class="mt-3 pt-3 border-t border-border-subtle dark:border-[#2a2a2a]">
                    <p class="text-[11px] font-semibold text-secondary dark:text-gray-400 uppercase tracking-wider mb-1.5">Live Preview Caption Telegram:</p>
                    <div class="bg-white dark:bg-[#252525] p-3 sm:p-4 rounded-lg border border-border-subtle dark:border-[#333] font-mono text-xs text-on-surface dark:text-white whitespace-pre-wrap select-all shadow-inner"
                         x-text="telegramPreview">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2.5 sm:gap-3 pt-4 border-t border-border-subtle dark:border-[#2a2a2a]">
                <a href="{{ route('testimonials.index') }}" class="px-4 sm:px-6 py-2 sm:py-2.5 bg-transparent dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface-variant dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#333] transition">Batal</a>
                <button type="submit" class="bg-primary-container text-on-surface font-semibold px-5 sm:px-6 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm hover:brightness-95 transition flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-base sm:text-lg">send</span>
                    Buat & Kirim ke Telegram
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
