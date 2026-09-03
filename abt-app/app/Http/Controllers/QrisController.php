<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QrisController extends Controller
{
    public function index()
    {
        $hasQris = Storage::disk('public')->exists('assets/qris.png');
        return view('qris.index', compact('hasQris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'qris_image' => 'required|image|max:5120',
        ]);

        $request->file('qris_image')->storeAs('assets', 'qris.png', 'public');

        return redirect()->route('qris.index')->with('success', 'QRIS berhasil diupload!');
    }
}
