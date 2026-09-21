<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CronController extends Controller
{
    /**
     * Validasi secret key cron
     */
    private function validateSecret(Request $request): bool
    {
        $expectedKey = env('CRON_SECRET', 'simventra_cron_rahasia_2026');
        $providedKey = $request->query('key') ?? $request->header('X-Cron-Key');

        return !empty($providedKey) && hash_equals($expectedKey, (string) $providedKey);
    }

    /**
     * Jalankan Laravel Scheduler (schedule:run)
     * Ini akan menjalankan semua task yang terjadwal:
     * - simventra:check-driver-heartbeat (setiap 2 menit)
     * - simventra:recall-assignments (setiap 1 menit)
     * - simventra:check-document-expiry (setiap 07:00 WIB)
     */
    public function schedule(Request $request): JsonResponse
    {
        if (!$this->validateSecret($request)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Invalid or missing cron key.',
            ], 401);
        }

        try {
            Artisan::call('schedule:run');
            $output = Artisan::output();

            Log::info('[CronController]: schedule:run berhasil dipicu via HTTP.', [
                'ip'     => $request->ip(),
                'output' => trim($output),
            ]);

            return response()->json([
                'status'    => 'success',
                'command'   => 'schedule:run',
                'output'    => trim($output) ?: 'No scheduled tasks were due to run.',
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[CronController]: schedule:run gagal via HTTP: ' . $e->getMessage());

            return response()->json([
                'status'    => 'error',
                'command'   => 'schedule:run',
                'message'   => $e->getMessage(),
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString(),
            ], 500);
        }
    }

    /**
     * Jalankan Cek Heartbeat Sopir secara spesifik (simventra:check-driver-heartbeat)
     */
    public function heartbeat(Request $request): JsonResponse
    {
        if (!$this->validateSecret($request)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Invalid or missing cron key.',
            ], 401);
        }

        try {
            Artisan::call('simventra:check-driver-heartbeat');
            $output = Artisan::output();

            Log::info('[CronController]: simventra:check-driver-heartbeat dipicu via HTTP.', [
                'ip'     => $request->ip(),
                'output' => trim($output),
            ]);

            return response()->json([
                'status'    => 'success',
                'command'   => 'simventra:check-driver-heartbeat',
                'output'    => trim($output),
                'now'       => now()->toDateTimeString(),
                'app_tz'    => config('app.timezone'),
                'php_tz'    => date_default_timezone_get(),
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[CronController]: simventra:check-driver-heartbeat gagal via HTTP: ' . $e->getMessage());

            return response()->json([
                'status'    => 'error',
                'command'   => 'simventra:check-driver-heartbeat',
                'message'   => $e->getMessage(),
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString(),
            ], 500);
        }
    }

    /**
     * Jalankan Queue Worker sekali jalan (--stop-when-empty)
     */
    public function queueWork(Request $request): JsonResponse
    {
        if (!$this->validateSecret($request)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Invalid or missing cron key.',
            ], 401);
        }

        try {
            Artisan::call('queue:work', [
                '--stop-when-empty' => true,
                '--max-time'        => 25,
            ]);
            $output = Artisan::output();

            return response()->json([
                'status'    => 'success',
                'command'   => 'queue:work --stop-when-empty',
                'output'    => trim($output) ?: 'Queue is empty, no jobs processed.',
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[CronController]: queue:work gagal via HTTP: ' . $e->getMessage());

            return response()->json([
                'status'    => 'error',
                'command'   => 'queue:work',
                'message'   => $e->getMessage(),
                'timestamp' => now()->timezone('Asia/Jakarta')->toDateTimeString(),
            ], 500);
        }
    }
}
