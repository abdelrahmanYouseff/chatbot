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
            try {
                // استخدم أول وثيقة موجودة (أو عدل حسب الحاجة)
                $document = \App\Models\Document::first();
                $chatService = app(\App\Services\ChatService::class);
                $answer = $chatService->ask($text, $document->id ?? null);

                $whatsappResponse = Http::withToken(env('WA_TOKEN'))->post("https://graph.facebook.com/v18.0/" . env('WA_PHONE_ID') . "/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $from,
                    'type' => 'text',
                    'text' => ['body' => $answer],
                ]);

                \Log::info('📩 WhatsApp API response', ['response' => $whatsappResponse->json()]);

                // حفظ آخر وقت تفاعل أو إنشاء المستخدم إذا لم يكن موجودًا
                $user = \App\Models\User::firstOrCreate(
                    ['phone_number' => $from],
                    [
                        'name' => 'WhatsApp User',
                        'email' => 'user_' . $from . '@whatsapp.local',
                        'password' => bcrypt(\Illuminate\Support\Str::random(10)),
                        'last_interaction_at' => now(),
                        'inactivity_attempts' => 0,
                        'phone_number' => $from,
                    ]
                );

                // تحديث وقت التفاعل عند كل رسالة
                $user->last_interaction_at = now();
                $user->inactivity_attempts = 0;
                $user->save();

            } catch (\Throwable $e) {
                \Log::error('❌ Error in WhatsApp reply logic', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
