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
    major: '{{ old('major', isset($fromInvoice) && $fromInvoice ? ($fromInvoice->major_name ?: ($fromInvoice->category->name ?? '')) : '') }}',
    taskTitle: '{{ old('task_title', isset($fromInvoice) && $fromInvoice ? $fromInvoice->title : '') }}',
    deliverables: '{{ old('deliverables', isset($fromInvoice) && $fromInvoice ? ($fromInvoice->sub_category_name ?? $fromInvoice->description) : '') }}',
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

            <!-- 1 to 4 Slot Upload Grid with Paste (Ctrl+V) Support -->
            <div class="mb-6 sm:mb-8" x-data="testimonialImageGrid()" @paste.window="handlePaste($event)">
                <div class="flex items-center justify-between mb-2 sm:mb-3">
                    <label class="block text-[11px] font-semibold text-on-surface dark:text-white uppercase tracking-wider">
                        Upload Bukti Gambar <span class="text-secondary dark:text-gray-400 font-normal">(1 s/d 4 Foto — Klik & Ctrl+V Paste)</span>
                    </label>
                    <span class="text-[11px] text-primary dark:text-primary-container font-medium">Minimal 1 foto</span>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    @foreach(['tugas' => '1. Tugas', 'chat' => '2. Chat', 'hasil' => '3. Hasil', 'pelunasan' => '4. Pelunasan'] as $slot => $label)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider transition-colors"
                                   :class="activeSlot === '{{ $slot }}' ? 'text-primary dark:text-primary-container font-bold' : 'text-on-surface-variant dark:text-gray-400'">
                                {{ $label }}
                            </label>
                            <span x-show="activeSlot === '{{ $slot }}'" class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-primary-container/30 text-on-surface dark:text-primary-container">
                                Active Slot
                            </span>
                        </div>

                        <div class="relative border-2 rounded-xl text-center cursor-pointer transition-all aspect-square flex items-center justify-center overflow-hidden bg-surface dark:bg-[#181818] group"
                             :class="activeSlot === '{{ $slot }}' ? 'border-primary dark:border-primary-container ring-2 ring-primary/20' : (previews['{{ $slot }}'] ? 'border-emerald-500/50' : 'border-dashed border-border-subtle dark:border-[#333] hover:border-primary/60')"
                             @click="setActive('{{ $slot }}')">
                            
                            <template x-if="previews['{{ $slot }}']">
                                <div class="w-full h-full relative">
                                    <img :src="previews['{{ $slot }}']" class="w-full h-full object-contain bg-white">
                                    <button type="button" @click.stop="clearSlot('{{ $slot }}')" class="absolute top-1.5 right-1.5 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-xs shadow-md hover:bg-red-700 transition" title="Hapus foto ini">
                                        ×
                                    </button>
                                </div>
                            </template>

                            <template x-if="!previews['{{ $slot }}']">
                                <div class="flex flex-col items-center gap-1.5 sm:gap-2 p-2 text-center" @click="$refs.input_{{ $slot }}.click()">
                                    <span class="material-symbols-outlined text-2xl transition-transform group-hover:scale-110" :class="activeSlot === '{{ $slot }}' ? 'text-primary dark:text-primary-container' : 'text-on-surface-variant/40 dark:text-gray-600'">add_photo_alternate</span>
                                    <p class="text-[10px] font-medium text-on-surface-variant dark:text-gray-400">Pilih / Ctrl+V</p>
                                </div>
                            </template>

                            <!-- Bottom Action Bar (Paste / Browse) -->
                            <div class="absolute bottom-0 inset-x-0 bg-black/60 backdrop-blur-2xs py-1 px-2 flex items-center justify-between text-[10px] text-white transition-opacity"
                                 :class="activeSlot === '{{ $slot }}' ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'"
                                 @click.stop="$refs.input_{{ $slot }}.click()">
                                <span class="flex items-center gap-1 font-semibold text-emerald-300">
                                    <span class="material-symbols-outlined text-xs">content_paste</span> Ctrl+V
                                </span>
                                <span class="text-gray-300 underline font-medium">Browse</span>
                            </div>

                            <input type="file" name="image_{{ $slot }}" accept="image/*" class="hidden"
                                   x-ref="input_{{ $slot }}"
                                   @change="onFileSelect('{{ $slot }}', $event)">
                        </div>
                        @error("image_{$slot}") <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-3 p-2.5 rounded-lg bg-surface-container/60 dark:bg-[#181818] border border-border-subtle dark:border-[#2a2a2a] text-[11px] text-secondary dark:text-gray-400 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-base shrink-0">content_paste_go</span>
                    <span><strong>Tips Super Cepat:</strong> Ambil screenshot layar (`Win + Shift + S`), klik pada slot (misal: <em>2. Chat</em>), lalu tekan <strong>Ctrl + V</strong> untuk langsung me-paste screenshot tanpa simpan file!</span>
                </div>
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

            <div class="flex flex-wrap items-center justify-end gap-2.5 sm:gap-3 pt-4 border-t border-border-subtle dark:border-[#2a2a2a]">
                <a href="{{ route('testimonials.index') }}" class="px-4 py-2 sm:py-2.5 bg-transparent dark:bg-[#252525] border border-border-subtle dark:border-[#333] rounded-lg text-xs sm:text-sm text-on-surface-variant dark:text-gray-300 hover:bg-surface-variant dark:hover:bg-[#333] transition">Batal</a>
                
                <!-- Save Draft Button -->
                <button type="submit" name="action" value="draft" class="px-4 py-2 sm:py-2.5 bg-gray-100 dark:bg-[#252525] border border-border-subtle dark:border-[#333] text-on-surface dark:text-gray-200 font-bold rounded-lg text-xs sm:text-sm hover:bg-gray-200 dark:hover:bg-[#333] transition flex items-center gap-1.5 shadow-2xs">
                    <span class="material-symbols-outlined text-base">save</span>
                    Simpan Draft (Lokal)
                </button>

                <!-- Publish Button -->
                <button type="submit" name="action" value="publish" class="bg-primary-container text-on-surface font-bold px-5 sm:px-6 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm hover:brightness-95 transition flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-base sm:text-lg">send</span>
                    Simpan & Kirim ke Telegram
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function testimonialImageGrid(initialPreviews = {}) {
    return {
        activeSlot: 'tugas',
        previews: {
            tugas: initialPreviews.tugas || null,
            chat: initialPreviews.chat || null,
            hasil: initialPreviews.hasil || null,
            pelunasan: initialPreviews.pelunasan || null,
        },
        slots: ['tugas', 'chat', 'hasil', 'pelunasan'],

        getFirstEmptySlot() {
            return this.slots.find(s => !this.previews[s]) || null;
        },

        setActive(slot) {
            this.activeSlot = slot;
        },

        onFileSelect(slot, event) {
            const file = event.target.files[0];
            if (file) {
                this.previews[slot] = URL.createObjectURL(file);
                this.activeSlot = slot;
            }
        },

        clearSlot(slot) {
            this.previews[slot] = null;
            if (this.$refs['input_' + slot]) {
                this.$refs['input_' + slot].value = '';
            }
        },

        handlePaste(event) {
            if (!event.clipboardData || !event.clipboardData.files) return;
            const file = Array.from(event.clipboardData.files).find(f => f.type.startsWith('image/'));
            if (!file) return;

            let targetSlot = this.activeSlot;
            if (!targetSlot || (this.previews[targetSlot] && this.getFirstEmptySlot())) {
                targetSlot = this.getFirstEmptySlot() || targetSlot || 'tugas';
            }

            const dt = new DataTransfer();
            dt.items.add(file);

            const inputEl = this.$refs['input_' + targetSlot];
            if (inputEl) {
                inputEl.files = dt.files;
                this.previews[targetSlot] = URL.createObjectURL(file);
                
                const nextEmpty = this.slots.find(s => s !== targetSlot && !this.previews[s]);
                if (nextEmpty) {
                    this.activeSlot = nextEmpty;
                } else {
                    this.activeSlot = targetSlot;
                }
            }
        }
    };
}
</script>
@endsection
