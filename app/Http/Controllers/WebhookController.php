<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        \Log::info('✅ WhatsApp Webhook Triggered', $request->all());
        if ($request->isMethod('get') && $request->has('hub_mode')) {
            if (
                $request->get('hub_mode') === 'subscribe' &&
                $request->get('hub_verify_token') === env('WA_VERIFY_TOKEN')
            ) {
                return response($request->get('hub_challenge'), 200);
            } else {
                return response('Verification failed', 403);
            }
        }

        // ✅ Step 2: استقبال الرسائل (POST)
        $message = $request->input('entry.0.changes.0.value.messages.0');
        $from = $message['from'] ?? null;
        $text = $message['text']['body'] ?? null;

        if ($from && $text) {
            $reply = $this->chatbotReply($text);

            Http::withToken(env('WA_TOKEN'))->post("https://graph.facebook.com/v18.0/" . env('WA_PHONE_ID') . "/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $from,
                'type' => 'text',
                'text' => ['body' => $reply]
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
    /**
     * Generate a chatbot reply for the given message.
     *
     * @param string $message
     * @return string
     */
    private function chatbotReply($message)
    {
        return "شكرًا لرسالتك! استلمنا: \"$message\" 😊";
    }
}
