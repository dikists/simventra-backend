<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Kirim push notification ke sopir berdasarkan token Expo
     *
     * @param string $pushToken
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public static function sendToToken(string $pushToken, string $title, string $body, array $data = []): bool
    {
        $pushToken = trim($pushToken);

        if (empty($pushToken)) {
            Log::info('[PushNotification]: Push token kosong, pengiriman dilewati.');
            return false;
        }

        try {
            $payload = [
                'to'                   => $pushToken,
                'title'                => $title,
                'body'                 => $body,
                'sound'                => 'default',
                'priority'             => 'high',              // Wajib 'high' agar Google Play Services segera menampilkan notifikasi
                'channelId'            => 'driver-tasks',      // Channel notification Android MAX importance
                '_displayInForeground' => true,
                'data'                 => $data,
            ];

            $response = Http::timeout(8)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post(self::EXPO_PUSH_URL, $payload);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('[PushNotification]: Notifikasi berhasil dikirim ke ' . $pushToken . ' - Response: ' . json_encode($result));
                return true;
            } else {
                Log::warning('[PushNotification]: Gagal mengirim ke Expo Push Service: ' . $response->body());
                return false;
            }
        } catch (\Throwable $e) {
            Log::error('[PushNotification]: Exception saat mengirim notif: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim push notification langsung ke entitas User (Sopir)
     *
     * @param User|null $user
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public static function sendToUser(?User $user, string $title, string $body, array $data = []): bool
    {
        if (!$user || empty($user->push_token)) {
            Log::info('[PushNotification]: User tidak memiliki push_token aktif.');
            return false;
        }

        return self::sendToToken($user->push_token, $title, $body, $data);
    }
}
