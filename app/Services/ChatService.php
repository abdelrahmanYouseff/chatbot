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
                    'content' => 'You are an AI assistant who answers questions based strictly on provided content. Do not guess or fabricate. If the answer is not found, respond clearly that it is not present.'
                ],
                [
                    'role' => 'user',
                    'content' => "The following content was extracted from a document:\n\n{$topChunks}\n\nNow answer the question: \"{$question}\""
                ]
            ],
        ]);

        logger()->info('GPT response', ['response' => $response]);

        return $response['choices'][0]['message']['content'] ?? 'No response from the AI model.';
    }

    protected function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = array_sum(array_map(fn ($x, $y) => $x * $y, $a, $b));
        $normA = sqrt(array_sum(array_map(fn ($x) => $x ** 2, $a)));
        $normB = sqrt(array_sum(array_map(fn ($x) => $x ** 2, $b)));
        return $dotProduct / ($normA * $normB);
    }
}
