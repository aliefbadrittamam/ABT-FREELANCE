@extends('layouts.app')

@section('title', 'Edit Testimoni — ABT-FREELANCE')
@section('header', 'Testimoni')

@section('content')
<div class="flex items-center gap-2 text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mb-4 sm:mb-6">
    <a href="{{ route('testimonials.index') }}" class="hover:text-on-surface dark:hover:text-white transition flex items-center gap-1">
        <span class="material-symbols-outlined text-base sm:text-lg">arrow_back</span>
        Kembali
    </a>
</div>

<header class="mb-6 sm:mb-8">
    <h1 class="text-2xl sm:text-[32px] font-bold text-on-surface dark:text-white tracking-tight leading-tight sm:leading-10">Edit Testimoni #{{ $testimonial->testimonial_number }}</h1>
    <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5 sm:mt-1">Ganti gambar per slot atau perbarui format caption Telegram.</p>
</header>

<div class="max-w-5xl" x-data="{
    number: '{{ old('testimonial_number', $testimonial->testimonial_number) }}',
    major: '{{ old('major', $testimonial->major) }}',
    taskTitle: '{{ old('task_title', $testimonial->task_title) }}',
    deliverables: '{{ old('deliverables', $testimonial->deliverables) }}',
    notes: '{{ old('caption', $testimonial->caption) }}',
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
    <form action="{{ route('testimonials.update', $testimonial) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
            <!-- Left: 4-Slot Images -->
            <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-6 shadow-sm transition-colors duration-200">
                <label class="block text-[11px] font-semibold text-on-surface dark:text-white uppercase tracking-wider mb-2.5 sm:mb-3">Ganti Gambar per Slot (Opsional)</label>
                <div class="grid grid-cols-2 gap-2.5 sm:gap-3 mb-4">
                    @foreach(['tugas' => '1. Tugas', 'chat' => '2. Chat Customer', 'hasil' => '3. Hasil', 'pelunasan' => '4. Pelunasan'] as $slot => $label)
                    @php $pathField = "image_{$slot}_path"; @endphp
                    <div x-data="{ preview: '{{ asset('storage/' . $testimonial->$pathField) }}', changed: false }">
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">{{ $label }}</label>
                        <div class="relative aspect-square rounded-xl overflow-hidden border-2 cursor-pointer transition-colors group bg-surface dark:bg-[#181818]"
                             :class="changed ? 'border-primary-container' : 'border-border-subtle dark:border-[#333] hover:border-primary-container/50'"
                             @click="$refs.edit_{{ $slot }}.click()">
                            <img :src="preview" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="text-white text-[11px] font-semibold bg-black/60 px-2.5 py-1 rounded-full flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">swap_horiz</span>
                                    Ganti
                                </span>
                            </div>
                            <input type="file" name="image_{{ $slot }}" accept="image/*" class="hidden"
                                   x-ref="edit_{{ $slot }}"
                                   @change="preview = URL.createObjectURL($event.target.files[0]); changed = true">
                        </div>
                    </div>
                    @endforeach
                </div>
                <p class="text-[11px] text-secondary dark:text-gray-400">Klik pada kotak mana saja untuk mengganti gambar pada slot tersebut.</p>
            </div>

            <!-- Right: Caption Details & Live Preview -->
            <div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] p-5 sm:p-6 space-y-4 shadow-sm transition-colors duration-200">
                <h3 class="text-xs font-bold text-on-surface dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary dark:text-primary-container text-base">edit_note</span>
                    Detail Caption Telegram
                </h3>

                <!-- Number + Major + Task -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Nomor Testi</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-secondary dark:text-gray-400 font-bold">#</span>
                            <input type="number" name="testimonial_number" x-model="number" required min="1"
                                class="w-full pl-8 pr-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm font-semibold text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Jurusan</label>
                        <input type="text" name="major" x-model="major" placeholder="Misal: Sistem Informasi"
                            class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Detail Tugas</label>
                        <input type="text" name="task_title" x-model="taskTitle" placeholder="Misal: UAS 2 dan 3"
                            class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                    </div>
                </div>

                <!-- Deliverables -->
                <div>
                    <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Output / Hasil Tugas</label>
                    <input type="text" name="deliverables" x-model="deliverables" placeholder="Misal: Makalah, Jurnal, Proposal kegiatan dan PPT"
                        class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                </div>

                <!-- Client Name & Notes -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Nama Klien (Opsional)</label>
                        <input type="text" name="client_name" value="{{ old('client_name', $testimonial->client_name) }}"
                            class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider mb-1">Catatan Tambahan (Opsional)</label>
                        <input type="text" name="caption" x-model="notes"
                            class="w-full px-3 py-2 bg-white dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface dark:text-white focus:ring-2 focus:ring-primary outline-none">
                    </div>
                </div>

                <!-- Live Preview -->
                <div class="pt-3 border-t border-border-subtle dark:border-[#2a2a2a]">
                    <p class="text-[11px] font-semibold text-secondary dark:text-gray-400 uppercase tracking-wider mb-1.5">Live Preview Caption:</p>
                    <div class="bg-surface dark:bg-[#181818] p-3 rounded-lg border border-border-subtle dark:border-[#333] font-mono text-xs text-on-surface dark:text-white whitespace-pre-wrap select-all shadow-inner"
                         x-text="telegramPreview">
                    </div>
                </div>

                <div class="pt-4 border-t border-border-subtle dark:border-[#2a2a2a] flex justify-end gap-2.5 sm:gap-3">
                    <a href="{{ route('testimonials.index') }}" class="px-4 sm:px-5 py-2 sm:py-2.5 bg-transparent dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface-variant dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#333] transition">Batal</a>
                    <button type="submit" class="bg-primary-container text-on-surface font-semibold px-5 sm:px-6 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm hover:brightness-95 transition flex items-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-base sm:text-lg">save</span>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
