<?php

namespace App\Http\Controllers;

use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index()
    {
        $setting = PaymentSetting::getSettings();
        $hasQris = $setting->qris_image_path && Storage::disk('public')->exists($setting->qris_image_path);
        return view('payment.index', compact('setting', 'hasQris'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'bank_info' => 'nullable|string|max:2000',
            'qris_image' => 'nullable|image|max:5120',
        ]);

        $setting = PaymentSetting::getSettings();

        $data = [
            'bank_info' => $request->bank_info,
        ];

        if ($request->hasFile('qris_image')) {
            $path = $request->file('qris_image')->storeAs('assets', 'qris.png', 'public');
            $data['qris_image_path'] = $path;
        }

        $setting->update($data);

        return redirect()->route('payment.index')->with('success', 'Pengaturan pembayaran berhasil diperbarui!');
    }
}
