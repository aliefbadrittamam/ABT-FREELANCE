<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'invoice_prefix', 'brand_name', 'tagline'];

    public function getPrefixAttribute(): string
    {
        if ($this->invoice_prefix) {
            return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $this->invoice_prefix));
        }

        // Auto-generate prefix from category name (e.g. "Joki Tugas" -> "JOKI", "Jasa Website" -> "WEB")
        $words = explode(' ', trim($this->name));
        if (count($words) > 1 && strtolower($words[0]) === 'jasa') {
            return strtoupper(substr($words[1], 0, 4));
        }
        return strtoupper(substr($words[0], 0, 4));
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
