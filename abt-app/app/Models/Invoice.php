<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'title', 'client_name', 'category_id',
        'description', 'deadline', 'payment_type', 'dp_amount',
        'total_amount', 'status', 'paid_at',
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
        if ($this->status === 'paid') return 0;
        if ($this->payment_type === 'dp' && $this->status === 'dp_paid') {
            return (float)$this->total_amount - (float)$this->dp_amount;
        }
        return (float)$this->total_amount;
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

        // Find highest sequence number for this category prefix
        $lastInvoice = static::where('invoice_number', 'LIKE', $basePattern . '%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastInvoice) {
            $parts = explode('-', $lastInvoice->invoice_number);
            $lastSeq = (int) end($parts);
            $nextNumber = $lastSeq + 1;
        }

        return $basePattern . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    protected static function booted(): void
    {
        static::updating(function (Invoice $invoice) {
            if ($invoice->isDirty('status') && $invoice->status === 'paid') {
                $invoice->paid_at = now();
            }
        });
    }
}
