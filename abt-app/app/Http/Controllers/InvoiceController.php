<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Category;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('category')->orderBy('id', 'desc');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(15);
        $categories = Category::all();

        return view('invoices.index', compact('invoices', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        $nextNumbers = [];
        foreach ($categories as $cat) {
            $nextNumbers[$cat->id] = Invoice::generateInvoiceNumber($cat->id);
        }
        return view('invoices.create', compact('categories', 'nextNumbers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'deadline' => 'required|date',
            'payment_type' => 'required|in:dp,full',
            'dp_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $validated['invoice_number'] = Invoice::generateInvoiceNumber($validated['category_id']);

        if ($validated['payment_type'] === 'full') {
            $validated['dp_amount'] = null;
        }

        $validated['status'] = 'unpaid';

        Invoice::create($validated);

        return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dibuat!');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('category');
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $categories = Category::all();
        return view('invoices.edit', compact('invoice', 'categories'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'deadline' => 'required|date',
            'payment_type' => 'required|in:dp,full',
            'dp_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:unpaid,dp_paid,paid,canceled',
        ]);

        if ($validated['payment_type'] === 'full') {
            $validated['dp_amount'] = null;
            if ($validated['status'] === 'dp_paid') {
                $validated['status'] = 'unpaid';
            }
        }

        $invoice->update($validated);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice berhasil diperbarui!');
    }

    public function cancel(Invoice $invoice)
    {
        $invoice->update(['status' => 'canceled']);
        return redirect()->route('invoices.show', $invoice)->with('success', 'Status invoice berhasil diubah menjadi Dibatalkan.');
    }

    public function destroy(Invoice $invoice)
    {
        // Delete task file if exists
        if ($invoice->task_file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($invoice->task_file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($invoice->task_file_path);
        }

        // Delete generated PDF and PNG exports if exist
        $exportPdf = storage_path('app/public/invoices/exports/' . $invoice->invoice_number . '.pdf');
        $exportPng = storage_path('app/public/invoices/exports/' . $invoice->invoice_number . '.png');
        if (file_exists($exportPdf)) @unlink($exportPdf);
        if (file_exists($exportPng)) @unlink($exportPng);

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice ' . $invoice->invoice_number . ' berhasil dihapus secara permanen.');
    }

    public function uploadTaskFile(Request $request, Invoice $invoice)
    {
        $request->validate([
            'task_file' => 'required|file|max:51200', // max 50MB
        ]);

        $file = $request->file('task_file');
        $originalName = $file->getClientOriginalName();
        $storedPath = $file->store('tasks/archives', 'public');

        $invoice->update([
            'task_file_path' => $storedPath,
            'task_file_name' => $originalName,
        ]);

        return redirect()->route('invoices.show', $invoice)->with('success', 'File arsip tugas berhasil disimpan!');
    }

    public function downloadTaskFile(Invoice $invoice)
    {
        if (!$invoice->task_file_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($invoice->task_file_path)) {
            return redirect()->route('invoices.show', $invoice)->with('error', 'File tugas tidak ditemukan.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download(
            $invoice->task_file_path,
            $invoice->task_file_name ?: basename($invoice->task_file_path)
        );
    }

    public function deleteTaskFile(Invoice $invoice)
    {
        if ($invoice->task_file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($invoice->task_file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($invoice->task_file_path);
        }

        $invoice->update([
            'task_file_path' => null,
            'task_file_name' => null,
        ]);

        return redirect()->route('invoices.show', $invoice)->with('success', 'File tugas berhasil dihapus.');
    }

    public function export(Invoice $invoice, string $format)
    {
        $invoice->load('category');
        $exportDir = storage_path('app/public/invoices/exports');
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        // Render clean standalone view for 100% exact export
        $htmlContent = view('invoices.standalone', compact('invoice'))->render();
        $tempHtmlPath = storage_path('app/public/invoices/exports/temp_' . $invoice->id . '_' . time() . '.html');
        file_put_contents($tempHtmlPath, $htmlContent);

        if ($format === 'png') {
            $filename = $invoice->invoice_number . '.png';
            $filePath = $exportDir . '/' . $filename;
            $scriptPath = base_path('render_image.mjs');

            // Render HD PNG via Chrome Puppeteer directly from HTML (No deadlocks!)
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

            // Render 2-page A4 PDF template
            $pdfHtmlContent = view('invoices.export', compact('invoice'))->render();
            $tempPdfHtmlPath = storage_path('app/public/invoices/exports/temp_pdf_' . $invoice->id . '_' . time() . '.html');
            file_put_contents($tempPdfHtmlPath, $pdfHtmlContent);

            $command = "node \"{$scriptPath}\" \"{$tempPdfHtmlPath}\" \"{$filePath}\" 2>&1";
            exec($command, $output, $returnCode);
            @unlink($tempPdfHtmlPath);

            if ($returnCode === 0 && file_exists($filePath)) {
                return response()->download($filePath, $filename, ['Content-Type' => 'application/pdf']);
            }

            // Fallback to DomPDF
            $pdf = Pdf::loadView('invoices.export', compact('invoice'))
                ->setPaper('a4', 'portrait');
            $pdf->save($filePath);

            return $pdf->download($filename);
        }

        @unlink($tempHtmlPath);
        return redirect()->back()->with('error', 'Format tidak didukung.');
    }
}
