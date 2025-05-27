<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use App\Models\Chunk;
use App\Models\Document;
use App\Services\EmbeddingService;

class PdfProcessor
{
    public static function handle(Document $document)
    {
        $parser = new Parser();
        $text = $parser->parseFile(storage_path("app/{$document->file_path}"))->getText();

        $chunks = str_split($text, 1000);
        foreach ($chunks as $chunk) {
            $embedding = app(EmbeddingService::class)->generate($chunk);
            Chunk::create([
                'document_id' => $document->id,
                'chunk_text' => $chunk,
                'embedding' => json_encode($embedding),
            ]);
        }
    }
}
