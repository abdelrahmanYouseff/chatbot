<?php

namespace App\Services;

use App\Models\Chunk;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Str;

class ChatService
{
    protected EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    public function ask(string $question, int $documentId): string
    {
        logger()->info('GPT question received', ['question' => $question, 'document_id' => $documentId]);

        $questionEmbedding = $this->embeddingService->generate($question);

        // احسب التشابه (cosine similarity)
        $chunks = Chunk::where('document_id', $documentId)->get();

        $scoredChunks = $chunks->map(function ($chunk) use ($questionEmbedding) {
            $score = $this->cosineSimilarity(
                $questionEmbedding,
                json_decode($chunk->embedding, true)
            );
            return ['score' => $score, 'text' => $chunk->chunk_text];
        });

        // اختار أعلى 3 قطع
        $topChunks = $scoredChunks->sortByDesc('score')->take(3)->pluck('text')->implode("\n");

        logger()->info('Top chunks used for GPT', ['chunks' => $topChunks]);

        // أرسلها لـ GPT
        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a professional customer support agent at Luxuria Car Rental in Dubai. Use the internal company knowledge provided to answer naturally and professionally. Never mention documents or sources. Every response must be in two parts:\n\n1. Answer the customer's question naturally and helpfully.\n2. Then provide a SEPARATE short follow-up question related to the conversation, starting with something like: 'Would you like...', 'Can I help you with...', or 'Do you want me to...'."
                ],
                [
                    'role' => 'user',
                    'content' => "Customer asked: \"{$question}\". Use the following internal knowledge to help answer them:\n\n{$topChunks}"
                ]
            ],
        ]);

        logger()->info('GPT response', ['response' => $response]);

        $answer = $response['choices'][0]['message']['content'] ?? 'No response from the AI model.';
        return preg_replace('/^[0-9]+\.\s+|^•\s+/', '', trim($answer));
    }

    protected function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = array_sum(array_map(fn ($x, $y) => $x * $y, $a, $b));
        $normA = sqrt(array_sum(array_map(fn ($x) => $x ** 2, $a)));
        $normB = sqrt(array_sum(array_map(fn ($x) => $x ** 2, $b)));
        return $dotProduct / ($normA * $normB);
    }
}
