<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Services\ChatService;
use Illuminate\Support\Facades\Http;
class ChatController extends Controller
{
    public function ask(Request $request, Document $document, ChatService $chatService)
    {
        logger()->info('✅ ChatController reached: ask method called');

        $request->validate(['question' => 'required|string']);

        try {
            $answer = $chatService->ask($request->input('question'), $document->id);
            logger()->info('🧠 Answer from ChatService', ['answer' => $answer]);

            // Send message to WhatsApp after getting answer
            $whatsappResponse = Http::withHeaders([
                'Authorization' => 'Bearer EAAJX3SovpD8B0218V2812oZC7rEwEhv8IU91lE1kvvyYgXLKpK2xdHFTUApLIZBcL2P2QunIwibUWJMaUtR1q4R2YF4qgTBZshxwbrzMfT3x0CE6ZAjZhA2AP7oC7EwTlACS3oqLZAgk5CDth14OwTk14urCqZABkfSCYqfds9ZA5WD1q9WkdKQ6AZCzYAdgn3TFtX5LceV6c88JkuzZAWiYPlGH8H2WBGciB',
                'Content-Type' => 'application/json',
            ])->post('https://graph.facebook.com/v18.0/659357663923770/messages', [
                'messaging_product' => 'whatsapp',
                'to' => '966535815072', // ← استبدل برقم المستخدم الحقيقي بصيغة دولية
                'type' => 'text',
                'text' => [
                    'body' => $answer,
                ],
            ]);

            logger()->info('📩 WhatsApp API response', ['response' => $whatsappResponse->json()]);

            return response()->json(['answer' => $answer ?? '🔴 No answer returned from ChatService']);
        } catch (\Throwable $e) {
            logger()->error('❌ Exception in ChatController::ask', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}
