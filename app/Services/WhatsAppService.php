<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk mengirim pesan WhatsApp via Wablas API
 *
 * Konfigurasi di .env:
 *   WABLAS_TOKEN=your_token_here
 *   WABLAS_BASE_URL=https://solo.wablas.com  (sesuai server Wablas kamu)
 */
class WhatsAppService
{
    private static function baseUrl(): string
    {
        return rtrim(config('services.wablas.base_url', 'https://solo.wablas.com'), '/');
    }

    private static function token(): string
    {
        return config('services.wablas.token', '');
    }

    /**
     * Kirim pesan teks via Wablas ke satu nomor
     *
     * @param string $phone  Nomor HP tujuan (format: 628xxx atau 08xxx)
     * @param string $message  Isi pesan
     * @return bool
     */
    public static function send(string $phone, string $message): bool
    {
        $token = self::token();

        if (empty($token)) {
            Log::warning('[WhatsApp]: WABLAS_TOKEN belum dikonfigurasi di .env, pesan dilewati.');
            return false;
        }

        // Normalisasi nomor: 08xxx → 628xxx
        $phone = self::normalizePhone($phone);

        if (empty($phone)) {
            Log::warning('[WhatsApp]: Nomor HP kosong atau tidak valid, pesan dilewati.');
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => $token,
                    'Content-Type'  => 'application/json',
                ])
                ->post(self::baseUrl() . '/api/send-message', [
                    'phone'   => $phone,
                    'message' => $message,
                ]);

            $body = $response->json();

            if ($response->successful() && ($body['status'] ?? false)) {
                Log::info('[WhatsApp]: Pesan berhasil dikirim ke ' . $phone);
                return true;
            }

            Log::warning('[WhatsApp]: Wablas mengembalikan respons gagal ke ' . $phone . ' – ' . json_encode($body));
            return false;

        } catch (\Throwable $e) {
            Log::error('[WhatsApp]: Exception saat mengirim ke ' . $phone . ' – ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Normalisasi nomor HP Indonesia ke format internasional (628xxx)
     */
    public static function normalizePhone(string $phone): string
    {
        // Hapus spasi, tanda hubung, tanda kurung
        $phone = preg_replace('/[\s\-\(\)\+]/', '', $phone);

        if (empty($phone)) return '';

        // 08xxx → 628xxx
        if (str_starts_with($phone, '08')) {
            return '62' . substr($phone, 1);
        }

        // 8xxx (tanpa 0) → 628xxx
        if (str_starts_with($phone, '8') && strlen($phone) >= 9) {
            return '62' . $phone;
        }

        // Sudah 62xxx
        if (str_starts_with($phone, '62')) {
            return $phone;
        }

        return $phone;
    }
}
