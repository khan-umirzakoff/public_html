<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Contracts\AIService;

class ProcessDocumentEmbedding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $documentId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($documentId)
    {
        $this->documentId = $documentId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(AIService $aiService)
    {
        $document = DB::table('ai_documents')->where('id', $this->documentId)->first();
        if (!$document) {
            return;
        }

        $content = $document->content;
        $contentLength = mb_strlen($content);

        // PROFESSIONAL DYNAMIC CHUNKING STRATEGY
        // Automatically adjusts based on document size

        if ($contentLength < 10000) {
            // Small documents (< 10KB): 1 chunk, full content
            $chunkSize = $contentLength;
            $maxChunks = 1;
        } elseif ($contentLength < 50000) {
            // Medium documents (10-50KB): ~10 chunks of 5KB
            $chunkSize = 5000;
            $maxChunks = 10;
        } elseif ($contentLength < 200000) {
            // Large documents (50-200KB): ~40 chunks of 5KB
            $chunkSize = 5000;
            $maxChunks = 40;
        } elseif ($contentLength < 500000) {
            // Very large documents (200-500KB): ~50 chunks of 10KB
            $chunkSize = 10000;
            $maxChunks = 50;
        } else {
            // Huge books (>500KB): ~100 chunks of 10KB (1MB max)
            $chunkSize = 10000;
            $maxChunks = 100;
        }

        $chunks = str_split($content, $chunkSize);
        $embeddings = [];

        // Limit chunks based on document size
        $chunks = array_slice($chunks, 0, $maxChunks);
        $totalChunks = count($chunks);

        Log::info('Document chunking strategy', [
            'document_id' => $this->documentId,
            'content_length' => $contentLength,
            'chunk_size' => $chunkSize,
            'total_chunks' => $totalChunks,
            'coverage' => min(100, round(($totalChunks * $chunkSize / $contentLength) * 100, 2)) . '%'
        ]);

        foreach ($chunks as $index => $chunk) {
            if (trim($chunk)) {
                $embeddings[] = $this->retryEmbed($aiService, $chunk, $index);
            }

            // Update progress
            $progress = intval((($index + 1) / $totalChunks) * 100);
            Cache::put('document_progress_' . $this->documentId, [
                'progress' => $progress,
                'chunks_processed' => $index + 1,
                'total_chunks' => $totalChunks
            ], 3600); // 1 hour

            // Small delay to avoid API rate limits (reduced from 2s to 0.3s)
            usleep(300000); // 0.3 seconds
        }

        // Update document with embeddings
        DB::table('ai_documents')->where('id', $this->documentId)->update([
            'embedding' => json_encode($embeddings),
            'updated_at' => now(),
        ]);

        // Clear progress cache
        Cache::forget('document_progress_' . $this->documentId);
    }

    protected function retryEmbed($aiService, $chunk, $index)
    {
        $maxRetries = 3;
        $retryDelay = 15; // seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                return $aiService->embed($chunk);
            } catch (\Exception $e) {
                Log::warning("Embedding attempt {$attempt} failed for chunk {$index}", [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt
                ]);

                if ($attempt < $maxRetries) {
                    sleep($retryDelay);
                } else {
                    Log::error("Embedding failed after {$maxRetries} attempts for chunk {$index}", [
                        'error' => $e->getMessage()
                    ]);
                    // Return empty array or skip
                    return [];
                }
            }
        }
    }
}
