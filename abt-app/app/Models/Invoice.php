<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'title', 'client_name', 'category_id',
        'description', 'deadline', 'payment_type', 'dp_amount',
        'total_amount', 'status', 'access_token', 'paid_at',
        'task_file_path', 'task_file_name',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'paid_at' => 'datetime',
            'dp_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getRemainingAmountAttribute(): float
    {
        if ($this->status === 'paid' || $this->status === 'canceled') return 0;
        if ($this->payment_type === 'dp') {
            return max(0, (float)$this->total_amount - (float)$this->dp_amount);
        }
        return (float)$this->total_amount;
    }

    public function getClientViewUrl(): string
    {
        return route('client.invoices.show', $this->access_token ?? 'unknown');
    }

    public function getWhatsAppConfirmationUrl(): string
    {
        $phone = '6288989504780'; // fallback default / dynamic

        $brand = $this->category->brand_name ?? 'ABT-FREELANCE';
        $text = "Halo {$brand}, saya *{$this->client_name}* ingin konfirmasi pembayaran untuk *Invoice {$this->invoice_number}* (Proyek: {$this->title}).\n\nLink Invoice: " . $this->getClientViewUrl();

        return "https://api.whatsapp.com/send?phone={$phone}&text=" . urlencode($text);
    }

    public static function generateInvoiceNumber(?int $categoryId = null): string
    {
        $prefix = 'JOKI';
        if ($categoryId) {
            $cat = Category::find($categoryId);
            if ($cat) {
                $prefix = $cat->prefix;
            }
        }

        $basePattern = 'INV-' . $prefix . '-';

        // Find highest sequence number numerically for this category prefix
        $maxSeq = static::where('invoice_number', 'LIKE', $basePattern . '%')
            ->get()
            ->map(function ($inv) {
                $parts = explode('-', $inv->invoice_number);
                return (int) end($parts);
            })
            ->max();

        $nextNumber = ($maxSeq ?: 0) + 1;

        return $basePattern . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->access_token)) {
                $invoice->access_token = Str::random(32);
            }
        });

        static::updating(function (Invoice $invoice) {
            if ($invoice->isDirty('status') && $invoice->status === 'paid') {
                $invoice->paid_at = now();
            }
        });
    }
}
