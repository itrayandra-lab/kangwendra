<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Aray\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ArayChatController extends Controller
{
    public function chat(Request $request, ChatService $chat): JsonResponse
    {
        // Public endpoint: only same-site browsers may call it.
        $origin = $request->headers->get('Origin');
        if ($origin && parse_url($origin, PHP_URL_HOST) !== $request->getHost()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:' . (int) config('aray.max_message_chars')],
            'history' => ['sometimes', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:2500'],
        ]);

        $message = trim($data['message']);
        if ($message === '') {
            return response()->json(['message' => 'Pesan tidak boleh kosong.'], 422);
        }

        $counter = 'aray:day:' . now()->format('Ymd');
        Cache::add($counter, 0, now()->endOfDay());
        if (Cache::increment($counter) > (int) config('aray.limits.global_daily')) {
            return response()->json(['message' => $this->unavailable()], 503);
        }

        try {
            return response()->json(['message' => $chat->reply($message, $data['history'] ?? [])]);
        } catch (\Throwable $e) {
            Log::error('aray unavailable', ['error' => $e->getMessage()]);

            return response()->json(['message' => $this->unavailable()], 503);
        }
    }

    private function unavailable(): string
    {
        return 'ARAY belum dapat menjawab saat ini. Silakan coba beberapa saat lagi, atau gunakan tombol Work Together untuk menghubungi Kang Wendra langsung.';
    }
}
