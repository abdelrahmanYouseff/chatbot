<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Services\ChatService;

class ChatController extends Controller
{
    public function ask(Request $request, Document $document, ChatService $chatService)
    {
        logger()->info('✅ ChatController reached: ask method called');

        $request->validate(['question' => 'required|string']);

        try {
            $answer = $chatService->ask($request->input('question'), $document->id);
            logger()->info('🧠 Answer from ChatService', ['answer' => $answer]);
            return response()->json(['answer' => $answer ?? '🔴 No answer returned from ChatService']);
        } catch (\Throwable $e) {
            logger()->error('❌ Exception in ChatController::ask', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}
