<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    public function verify(Request $request)
    {
        $verify_token = 'my_custom_token'; // نفس التوكن اللي هتحطه في Meta

        if (
            $request->has('hub_mode') &&
            $request->input('hub_mode') === 'subscribe' &&
            $request->input('hub_verify_token') === $verify_token
        ) {
            return response($request->input('hub_challenge'), 200);
        }

        return response('Verification failed', 403);
    }

    // Handle incoming messages
    public function receive(Request $request)
    {
        logger()->info('📩 Received message from WhatsApp Webhook:', $request->all());

        try {
            $entry = $request->input('entry')[0] ?? [];
            $changes = $entry['changes'][0]['value'] ?? null;

            if (!$changes || !isset($changes['messages'][0])) {
                return response()->json(['status' => 'no message'], 200);
            }

            $messageData = $changes['messages'][0];
            $from = $messageData['from']; // رقم المستخدم
            $text = $messageData['text']['body'] ?? null;

            if ($from && $text) {
                // استخدم ChatService للإجابة
                $chatService = app(\App\Services\ChatService::class);
                $answer = $chatService->ask($text, null); // لو عندك معرف ملف، ضيفه هنا

                // إرسال الرد عبر WhatsApp
                $token = 'EAAJX3SovpD8B0218V2812oZC7rEwEhv8IU91lE1kvvyYgXLKpK2xdHFTUApLIZBcL2P2QunIwibUWJMaUtR1q4R2YF4qgTBZshxwbrzMfT3x0CE6ZAjZhA2AP7oC7EwTlACS3oqLZAgk5CDth14OwTk14urCqZABkfSCYqfds9ZA5WD1q9WkdKQ6AZCzYAdgn3TFtX5LceV6c88JkuzZAWiYPlGH8H2WBGciB';

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ])->post('https://graph.facebook.com/v18.0/659357663923770/messages', [
                    'messaging_product' => 'whatsapp',
                    'to' => $from,
                    'type' => 'text',
                    'text' => [
                        'body' => $answer,
                    ],
                ]);

                logger()->info('📤 WhatsApp message sent', ['response' => $response->json()]);
            }

            return response()->json(['status' => 'received'], 200);
        } catch (\Throwable $e) {
            logger()->error('❌ Error in Webhook receive', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error'], 500);
        }
    }
}
