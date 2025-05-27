<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Services\PdfProcessor;

class DocumentController extends Controller
{
    public function upload(Request $request)
{
    $request->validate([
        'pdf' => 'required|mimes:pdf|max:10000',
    ]);

    $path = $request->file('pdf')->store('pdfs');

    $document = Document::create([
        'title' => $request->file('pdf')->getClientOriginalName(),
        'file_path' => $path,
    ]);

    // أرسل الملف للمعالجة
    PdfProcessor::handle($document); // مؤقتًا بدون Queue

    return back()->with('success', 'PDF uploaded and processing started.');
}
}
