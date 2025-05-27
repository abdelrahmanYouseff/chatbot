<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        // Extract message
        $entry = $request->input('entry')[0] ?? [];
        $changes = $entry['changes'][0]['value']['messages'][0] ?? null;

        if ($changes) {
            $from = $changes['from'];
            $text = $changes['text']['body'] ?? '';

            // ممكن تربطه هنا مع ChatService وتبعت الرد
        }

        return response()->json(['status' => 'received'], 200);
    }
}
