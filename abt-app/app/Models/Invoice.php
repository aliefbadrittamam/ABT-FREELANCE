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
        'has_worker', 'my_role', 'payment_flow', 'partner_name', 'partner_phone',
        'worker_percentage', 'hunter_percentage', 'my_share_amount', 'partner_share_amount',
        'payout_status', 'payout_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'paid_at' => 'datetime',
            'payout_at' => 'datetime',
            'dp_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'worker_percentage' => 'decimal:2',
            'hunter_percentage' => 'decimal:2',
            'my_share_amount' => 'decimal:2',
            'partner_share_amount' => 'decimal:2',
            'has_worker' => 'boolean',
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

    public function calculateShares(): void
    {
        if (!$this->has_worker) {
            $this->my_share_amount = (float)$this->total_amount;
            $this->partner_share_amount = 0;
            $this->my_role = 'none';
            return;
        }

        $wPercent = (float)($this->worker_percentage ?: 80.00);
        $hPercent = 100.00 - $wPercent;
        $this->worker_percentage = $wPercent;
        $this->hunter_percentage = $hPercent;

        $total = (float)$this->total_amount;

        if ($this->my_role === 'worker') {
            // Anda sebagai Worker: Anda dapat worker_percentage (80%), Hunter luar dapat hunter_percentage (20%)
            $this->my_share_amount = round($total * ($wPercent / 100));
            $this->partner_share_amount = $total - $this->my_share_amount;
        } else {
            // Default: Anda sebagai Hunter (Admin): Anda dapat hunter_percentage (20%), Worker luar dapat worker_percentage (80%)
            $this->my_role = 'hunter';
            $this->partner_share_amount = round($total * ($wPercent / 100));
            $this->my_share_amount = $total - $this->partner_share_amount;
        }
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
            $invoice->calculateShares();
        });

        static::updating(function (Invoice $invoice) {
            if ($invoice->isDirty('status') && $invoice->status === 'paid') {
                $invoice->paid_at = now();
            }
            if ($invoice->isDirty(['has_worker', 'my_role', 'worker_percentage', 'total_amount'])) {
                $invoice->calculateShares();
            }
        });
    }
}
