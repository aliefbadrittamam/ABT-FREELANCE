<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'testimonial_number',
        'major',
        'task_title',
        'deliverables',
        'image_tugas_path', 'image_chat_path', 'image_hasil_path',
        'image_pelunasan_path', 'composed_image_path', 'caption',
        'client_name', 'posted_to_telegram', 'telegram_message_id',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public static function getNextTestimonialNumber(): int
    {
        $max = static::withTrashed()->max('testimonial_number');
        return $max ? $max + 1 : 1;
    }

    /**
     * Check if testimonial is still eligible for deletion (<= 7 days old)
     */
    public function isDeletable(): bool
    {
        if (!$this->created_at) return true;
        return $this->created_at->diffInDays(now()) <= 7;
    }

    /**
     * Get remaining days allowed for deletion
     */
    public function getDaysRemainingForDeletion(): int
    {
        if (!$this->created_at) return 0;
        $diff = 7 - (int)$this->created_at->diffInDays(now());
        return max(0, $diff);
    }

    public function getFormattedTelegramCaption(): string
    {
        // Format: #{Nomor}. {Jurusan/Kategori} {Judul Tugas}. ({Deliverables})
        $numberPart = '#' . ($this->testimonial_number ?: '1');
        
        $bodyParts = [];
        if ($this->major) {
            $bodyParts[] = $this->major;
        }
        if ($this->task_title) {
            $bodyParts[] = $this->task_title;
        }

        $mainText = !empty($bodyParts) ? implode(' ', $bodyParts) : ($this->client_name ? "Tugas {$this->client_name}" : 'Tugas Selesai');
        
        $caption = "{$numberPart}. {$mainText}.";

        if ($this->deliverables) {
            $cleanDeliverables = trim($this->deliverables, '() ');
            $caption .= " ({$cleanDeliverables})";
        }

        if ($this->caption) {
            $caption .= "\n\n" . $this->caption;
        }

        return $caption;
    }

    protected function casts(): array
    {
        return [
            'posted_to_telegram' => 'boolean',
        ];
    }
}
