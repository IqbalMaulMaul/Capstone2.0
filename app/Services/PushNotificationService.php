<?php

namespace App\Services;

use App\Models\PushToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Expo Push API endpoint
     */
    protected static string $expoPushUrl = 'https://exp.host/--/api/v2/push/send';

    /**
     * Send push notification to all users with a specific role.
     */
    public static function sendToRole(string $role, string $title, string $body, array $data = []): void
    {
        $tokens = PushToken::whereHas('user', function ($q) use ($role) {
            $q->where('role', $role);
        })->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        static::sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send push notification to multiple roles.
     */
    public static function sendToRoles(array $roles, string $title, string $body, array $data = []): void
    {
        $tokens = PushToken::whereHas('user', function ($q) use ($roles) {
            $q->whereIn('role', $roles);
        })->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        static::sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send push notification to a specific user.
     */
    public static function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        $tokens = $user->pushTokens()->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        static::sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send push notifications to specific Expo push tokens.
     */
    protected static function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        $messages = [];

        foreach ($tokens as $token) {
            $messages[] = [
                'to' => $token,
                'sound' => 'default',
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'priority' => 'high',
                'channelId' => 'default',
            ];
        }

        // Expo supports batch sending (up to 100 messages per request)
        $chunks = array_chunk($messages, 100);

        foreach ($chunks as $chunk) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Content-Type' => 'application/json',
                ])->post(static::$expoPushUrl, $chunk);

                if ($response->failed()) {
                    Log::error('Expo Push Error: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('Push Notification Error: ' . $e->getMessage());
            }
        }
    }
}
