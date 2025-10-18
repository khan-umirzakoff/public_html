<?php

namespace App\Http\Controllers;

use App\Contracts\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIChatController extends Controller
{
    protected $aiService;

    // RAGService is no longer needed in the controller, it will be used by the AIService
    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:5000',
            'history' => 'nullable|array',
            'history.*.role' => 'required_with:history|in:user,model',
            'history.*.text' => 'required_with:history|string',
            'stream' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'string',
        ]);

        if (empty($request->message) && empty($request->images)) {
            return response()->json(['success' => false, 'error' => 'Matn yoki rasm yuborish kerak'], 400);
        }

        // Streaming is now the primary method, so we delegate to chatStream
        return $this->chatStream($request);
    }

    protected function chatStream(Request $request)
    {
        $message = $request->input('message');
        $history = $request->input('history', []);
        $images = $request->input('images', []);

        // Log incoming message and history for debugging duplication issues
        Log::info('Chat Request Received', [
            'message' => $message,
            'message_length' => mb_strlen($message),
            'history_count' => count($history),
            'last_history_item' => !empty($history) ? end($history) : null
        ]);

        return response()->stream(function () use ($message, $history, $images) {
            try {
                // Delegate the entire function calling and streaming logic to the AIService
                $stream = $this->aiService->streamChat($message, $history, $images);

                foreach ($stream as $event) {
                    echo "data: " . json_encode($event) . "\n\n";
                    ob_flush();
                    flush();
                }

            } catch (\Exception $e) {
                Log::error('AI Chat Stream Error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                echo "data: " . json_encode(['error' => 'Xatolik yuz berdi: ' . $e->getMessage()]) . "\n\n";
                ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function embed(Request $request)
    {
        $request->validate(['text' => 'required|string|max:5000']);
        try {
            $embedding = $this->aiService->embed($request->input('text'));
            return response()->json(['success' => true, 'embedding' => $embedding]);
        } catch (\Exception $e) {
            Log::error('Embedding Error', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Failed to generate embedding'], 500);
        }
    }
}