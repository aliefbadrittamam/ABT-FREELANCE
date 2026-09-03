@extends('layouts.app')

@section('title', 'Invoice — ABT-FREELANCE')
@section('header', 'Invoice')

@section('content')
<header class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-[32px] font-bold text-on-surface dark:text-white tracking-tight leading-tight sm:leading-10">Daftar Invoice</h1>
        <p class="text-xs sm:text-sm text-on-surface-variant dark:text-gray-400 mt-0.5 sm:mt-1">Kelola semua invoice klien Anda.</p>
    </div>
    <a href="{{ route('invoices.create') }}" class="bg-primary-container text-on-surface font-semibold text-xs sm:text-sm px-4 sm:px-5 py-2.5 rounded-lg hover:brightness-95 transition flex items-center gap-2 shadow-sm w-fit">
        <span class="material-symbols-outlined text-base sm:text-lg">add</span>
        Buat Invoice Baru
    </a>
</header>

<!-- Filters (Scrollable on mobile) -->
<div class="mb-6">
    <form method="GET" action="{{ route('invoices.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <!-- Status Tabs (Horizontal Scrollable) -->
        <div class="flex bg-surface-container dark:bg-[#1e1e1e] border border-transparent dark:border-[#2a2a2a] rounded-lg p-1 gap-1 overflow-x-auto scrollbar-none">
            <a href="{{ route('invoices.index', array_merge(request()->except('status','page'), [])) }}" 
               class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-md text-xs sm:text-sm font-medium transition-colors shrink-0 {{ !request('status') ? 'bg-primary-container text-on-surface shadow-sm font-bold' : 'text-on-surface-variant dark:text-gray-400 hover:text-on-surface dark:hover:text-white' }}">
                Semua
            </a>
            <a href="{{ route('invoices.index', array_merge(request()->except('page'), ['status' => 'unpaid'])) }}" 
               class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-md text-xs sm:text-sm font-medium transition-colors shrink-0 {{ request('status') === 'unpaid' ? 'bg-primary-container text-on-surface shadow-sm font-bold' : 'text-on-surface-variant dark:text-gray-400 hover:text-on-surface dark:hover:text-white' }}">
                Belum Bayar
            </a>
            <a href="{{ route('invoices.index', array_merge(request()->except('page'), ['status' => 'dp_paid'])) }}" 
               class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-md text-xs sm:text-sm font-medium transition-colors shrink-0 {{ request('status') === 'dp_paid' ? 'bg-primary-container text-on-surface shadow-sm font-bold' : 'text-on-surface-variant dark:text-gray-400 hover:text-on-surface dark:hover:text-white' }}">
                DP Terbayar
            </a>
            <a href="{{ route('invoices.index', array_merge(request()->except('page'), ['status' => 'paid'])) }}" 
               class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-md text-xs sm:text-sm font-medium transition-colors shrink-0 {{ request('status') === 'paid' ? 'bg-primary-container text-on-surface shadow-sm font-bold' : 'text-on-surface-variant dark:text-gray-400 hover:text-on-surface dark:hover:text-white' }}">
                Lunas
            </a>
            <a href="{{ route('invoices.index', array_merge(request()->except('page'), ['status' => 'canceled'])) }}" 
               class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-md text-xs sm:text-sm font-medium transition-colors shrink-0 {{ request('status') === 'canceled' ? 'bg-primary-container text-on-surface shadow-sm font-bold' : 'text-on-surface-variant dark:text-gray-400 hover:text-on-surface dark:hover:text-white' }}">
                Dibatalkan
            </a>
        </div>
        <select name="category_id" onchange="this.form.submit()" class="px-3 sm:px-4 py-2 bg-white dark:bg-[#1e1e1e] border border-border-subtle dark:border-[#2a2a2a] text-on-surface dark:text-white rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-primary outline-none">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </form>
</div>

<!-- Invoice Table (Responsive Horizontal Scroll) -->
<div class="bg-white dark:bg-[#1e1e1e] rounded-xl border border-border-subtle dark:border-[#2a2a2a] overflow-hidden shadow-sm transition-colors duration-200">
    <div class="overflow-x-auto w-full">
        <table class="w-full text-left border-collapse min-w-[640px]">
            <thead>
                <tr class="border-b border-border-subtle dark:border-[#2a2a2a] bg-surface-container-low dark:bg-[#181818]">
                    <th class="py-3 px-4 sm:px-6 text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Nomor Invoice</th>
                    <th class="py-3 px-4 sm:px-6 text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Nama Klien</th>
                    <th class="py-3 px-4 sm:px-6 text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                    <th class="py-3 px-4 sm:px-6 text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider text-right">Total</th>
                    <th class="py-3 px-4 sm:px-6 text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider text-center">Status</th>
                    <th class="py-3 px-4 sm:px-6 text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider">Deadline</th>
                    <th class="py-3 px-4 sm:px-6 text-[11px] font-semibold text-on-surface-variant dark:text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-subtle dark:divide-[#2a2a2a] text-xs sm:text-sm">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-surface-variant/30 dark:hover:bg-[#252525] transition-colors {{ $invoice->status === 'canceled' ? 'opacity-60 bg-gray-50/50 dark:bg-black/20' : '' }}">
                    <td class="py-3.5 px-4 sm:px-6">
                        <span class="font-mono text-xs text-on-surface-variant dark:text-gray-400 font-bold">{{ $invoice->invoice_number }}</span>
                    </td>
                    <td class="py-3.5 px-4 sm:px-6 font-medium text-on-surface dark:text-white">{{ $invoice->client_name }}</td>
                    <td class="py-3.5 px-4 sm:px-6 text-on-surface-variant dark:text-gray-400">{{ $invoice->category->name ?? '-' }}</td>
                    <td class="py-3.5 px-4 sm:px-6 text-right font-semibold text-on-surface dark:text-white">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                    <td class="py-3.5 px-4 sm:px-6 text-center">
                        @if($invoice->status === 'paid')
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-status-lunas/10 text-status-lunas px-2.5 py-0.5 rounded-full">Lunas</span>
                        @elseif($invoice->status === 'dp_paid')
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-status-dp/10 text-status-dp px-2.5 py-0.5 rounded-full">DP Terbayar</span>
                        @elseif($invoice->status === 'canceled')
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-gray-200 dark:bg-[#333] text-gray-700 dark:text-gray-300 px-2.5 py-0.5 rounded-full">Dibatalkan</span>
                        @else
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-status-pending/10 text-status-pending px-2.5 py-0.5 rounded-full">Belum Bayar</span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4 sm:px-6 text-on-surface-variant dark:text-gray-400">{{ $invoice->deadline->format('d M Y') }}</td>
                    <td class="py-3.5 px-4 sm:px-6 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('invoices.show', $invoice) }}" class="p-1 rounded-md text-primary dark:text-primary-container hover:bg-surface-variant dark:hover:bg-[#333] transition" title="Lihat">
                                <span class="material-symbols-outlined text-lg">visibility</span>
                            </a>
                            <a href="{{ route('invoices.edit', $invoice) }}" class="p-1 rounded-md text-on-surface-variant dark:text-gray-400 hover:text-on-surface dark:hover:text-white hover:bg-surface-variant dark:hover:bg-[#333] transition" title="Edit">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </a>
                            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus invoice {{ $invoice->invoice_number }} secara permanen? Tindakan ini tidak dapat dibatalkan.')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1 rounded-md text-secondary dark:text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Hapus Permanen">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-on-surface-variant dark:text-gray-400">Belum ada invoice. Mulai buat invoice pertama!</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($invoices->hasPages())
<div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs sm:text-sm text-on-surface-variant dark:text-gray-400">
    <span>Menampilkan {{ $invoices->firstItem() }}-{{ $invoices->lastItem() }} dari {{ $invoices->total() }} invoice</span>
    <div>{{ $invoices->appends(request()->query())->links() }}</div>
</div>
@endif
@endsection
