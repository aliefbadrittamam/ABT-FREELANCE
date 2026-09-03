<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ClientInvoiceController extends Controller
{
    /**
     * Display the public invoice page for customer.
     */
    public function show(string $token)
    {
        $invoice = Invoice::with('category')->where('access_token', $token)->firstOrFail();
        $settings = PaymentSetting::getSettings();

        // Assets base64 for reliable rendering
        $logoPath = storage_path('app/public/assets/logo.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        $hasQris = !empty($settings['qris_image_path']) && file_exists(storage_path('app/public/' . $settings['qris_image_path']));
        $qrisPath = $hasQris ? storage_path('app/public/' . $settings['qris_image_path']) : public_path('images/qris-dummy.png');
        $qrisBase64 = file_exists($qrisPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($qrisPath)) : null;

        $bcaPath = storage_path('app/public/assets/banks/bca.png');
        $bcaBase64 = file_exists($bcaPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bcaPath)) : null;

        $danaPath = storage_path('app/public/assets/banks/dana.png');
        $danaBase64 = file_exists($danaPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($danaPath)) : null;

        $seaPath = storage_path('app/public/assets/banks/seabank.png');
        $seaBase64 = file_exists($seaPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($seaPath)) : null;

        return view('client.invoices.show', compact(
            'invoice',
            'settings',
            'logoBase64',
            'qrisBase64',
            'bcaBase64',
            'danaBase64',
            'seaBase64'
        ));
    }

    /**
     * Download Invoice as PDF or PNG directly by customer.
     */
    public function export(string $token, string $format)
    {
        $invoice = Invoice::with('category')->where('access_token', $token)->firstOrFail();

        $exportDir = storage_path('app/public/invoices/exports');
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        // Render clean standalone view
        $htmlContent = view('invoices.standalone', compact('invoice'))->render();
        $tempHtmlPath = storage_path('app/public/invoices/exports/client_temp_' . $invoice->id . '_' . time() . '.html');
        file_put_contents($tempHtmlPath, $htmlContent);

        if ($format === 'png') {
            $filename = $invoice->invoice_number . '.png';
            $filePath = $exportDir . '/' . $filename;
            $scriptPath = base_path('render_image.mjs');

            $command = "node \"{$scriptPath}\" \"{$tempHtmlPath}\" \"{$filePath}\" 2>&1";
            exec($command, $output, $returnCode);
            @unlink($tempHtmlPath);

            if ($returnCode === 0 && file_exists($filePath)) {
                return response()->download($filePath, $filename, ['Content-Type' => 'image/png']);
            }
        }

        if ($format === 'pdf') {
            $filename = $invoice->invoice_number . '.pdf';
            $filePath = $exportDir . '/' . $filename;
            $scriptPath = base_path('render_pdf.mjs');

            $pdfHtmlContent = view('invoices.export', compact('invoice'))->render();
            $tempPdfHtmlPath = storage_path('app/public/invoices/exports/client_temp_pdf_' . $invoice->id . '_' . time() . '.html');
            file_put_contents($tempPdfHtmlPath, $pdfHtmlContent);

            $command = "node \"{$scriptPath}\" \"{$tempPdfHtmlPath}\" \"{$filePath}\" 2>&1";
            exec($command, $output, $returnCode);
            @unlink($tempPdfHtmlPath);

            if ($returnCode === 0 && file_exists($filePath)) {
                return response()->download($filePath, $filename, ['Content-Type' => 'application/pdf']);
            }

            $pdf = Pdf::loadView('invoices.export', compact('invoice'))
                ->setPaper('a4', 'portrait');
            $pdf->save($filePath);

            return $pdf->download($filename);
        }

        @unlink($tempHtmlPath);
        return redirect()->back()->with('error', 'Format tidak didukung.');
    }

    /**
     * Download completed task file by customer.
     */
    public function downloadTaskFile(string $token)
    {
        $invoice = Invoice::where('access_token', $token)->firstOrFail();

        if (!$invoice->task_file_path || !Storage::disk('public')->exists($invoice->task_file_path)) {
            return redirect()->back()->with('error', 'File tugas belum tersedia untuk diunduh.');
        }

        return Storage::disk('public')->download($invoice->task_file_path, $invoice->task_file_name);
    }
}
