<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'category_id',
        'category_type',
        'title',
        'tagline',
        'banner_path',
        'banner_type',
        'copywriting',
        'target_platform',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        if ($this->category) {
            return $this->category->name;
        }

        return match ($this->category_type) {
            'tournament' => 'Turnamen eFootball',
            'website' => 'Jasa Website & Coding',
            'joki' => 'Joki Tugas & Skripsi',
            default => 'Promosi Umum',
        };
    }

    public function getBannerUrlAttribute(): ?string
    {
        if (!$this->banner_path) {
            return null;
        }

        return asset('storage/' . $this->banner_path);
    }
}
