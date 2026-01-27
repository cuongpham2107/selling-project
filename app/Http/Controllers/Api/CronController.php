<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CronController extends Controller
{
    /**
     * Run the scheduled transaction commands.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function run(Request $request)
    {
        $token = $request->header('X-Cron-Token') ?: $request->query('token');

        if (!$token || $token !== config('app.cron_token')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 401);
        }

        try {
            Log::info('Triggering cron tasks via API...');

            // 1. Complete shop transactions
            Artisan::call('app:complete-shop-transactions');
            $completeOutput = Artisan::output();

            // 2. Cancel overdue transactions
            Artisan::call('app:cancel-overdue-transactions');
            $cancelOutput = Artisan::output();

            return response()->json([
                'status' => 'success',
                'timestamp' => now()->toDateTimeString(),
                'results' => [
                    'complete_shop_transactions' => trim($completeOutput),
                    'cancel_overdue_transactions' => trim($cancelOutput),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Cron API Error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Process failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
