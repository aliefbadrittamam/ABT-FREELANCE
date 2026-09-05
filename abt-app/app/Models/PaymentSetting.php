<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = [
        'bank_info',
        'qris_image_path',
        'default_tournament_live_link',
    ];

    public static function getDefaultTournamentLiveLink(): string
    {
        $settings = static::getSettings();
        return !empty($settings->default_tournament_live_link) 
            ? $settings->default_tournament_live_link 
            : url('/turnamen/efootball/live');
    }

    public static function getSettings(): self
    {
        $setting = static::first();
        if (!$setting) {
            $defaultBankInfo = "🏦 BCA : 1921252558   |   💳 DANA : 0823 3336 2651   |   🏦 SEABANK : 9010 9905 3997\nSemua Pembayaran a.n. ALIEF BADRIT TAMAM\n\n📌 Mohon konfirmasi bukti transfer setelah melakukan pembayaran. Terima kasih 🙏";
            
            $setting = static::create([
                'bank_info' => $defaultBankInfo,
                'qris_image_path' => 'assets/qris.png',
            ]);
        }
        return $setting;
    }
}
