<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $message = $request->input('entry.0.changes.0.value.messages.0');
        $from = $message['from'] ?? null;
        $text = $message['text']['body'] ?? null;

        if ($from && $text) {
            // 👇 هنا بترسل الرسالة لـ GPT أو أي منطق شات بوت عندك
            $reply = $this->chatbotReply($text);

            // 👇 ترجع الرد لواتساب
            Http::withToken(env('WA_TOKEN'))->post("https://graph.facebook.com/v18.0/" . env('WA_PHONE_ID') . "/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $from,
                'type' => 'text',
                'text' => [
                    'body' => $reply
                ]
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    // 👇 ده كود الرد البسيط، ممكن تعدله بـ ChatGPT
    public function chatbotReply($message)
    {
        return "🤖 شكرًا لرسالتك: \"$message\". هنرد عليك حالًا.";
    }
}
