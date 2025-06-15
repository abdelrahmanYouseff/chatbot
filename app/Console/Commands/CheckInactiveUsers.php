<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-inactive-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inactiveUsers = User::whereNotNull('last_interaction_at')
            ->where('last_interaction_at', '<', now()->subMinutes(1))
            ->get();

        foreach ($inactiveUsers as $user) {
            try {
                $message = "Are you still there? 😊";

                if ($user->inactivity_attempts >= 1) {
                    $message = "I'm here if you need anything 😊";
                }

                $response = Http::withToken(env('WA_TOKEN'))->post("https://graph.facebook.com/v18.0/" . env('WA_PHONE_ID') . "/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $user->phone_number,
                    'type' => 'text',
                    'text' => ['body' => $message],
                ]);

                Log::info('🤖 Inactivity follow-up sent', [
                    'user' => $user->id,
                    'message' => $message,
                    'response' => $response->json()
                ]);

                $user->last_interaction_at = now();
                $user->inactivity_attempts = ($user->inactivity_attempts ?? 0) + 1;
                $user->save();
            } catch (\Throwable $e) {
                Log::error('❌ Failed to send inactivity message', [
                    'user' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
