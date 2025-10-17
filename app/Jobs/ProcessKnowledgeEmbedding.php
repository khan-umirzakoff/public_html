<?php

namespace App\Jobs;

use App\AiKnowledge;
use App\Contracts\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessKnowledgeEmbedding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $knowledge;

    public function __construct(AiKnowledge $knowledge)
    {
        $this->knowledge = $knowledge;
    }

    public function handle(AIService $aiService)
    {
        try {
            // Agar allaqachon embedding bo'lsa, o'tkazib yuborish
            if (!empty($this->knowledge->embedding)) {
                Log::info("Knowledge #{$this->knowledge->id} already has embedding, skipping");
                return;
            }

            $text = "Kategoriya: {$this->knowledge->category}. " .
                    "Kalit: {$this->knowledge->key}. " .
                    "Qiymat: {$this->knowledge->value}. " .
                    "Izoh: {$this->knowledge->description}";

            $embedding = $aiService->embed($text);

            // Update qilish
            $this->knowledge->update([
                'embedding' => json_encode($embedding)
            ]);

            Log::info("Embedding created for AiKnowledge #{$this->knowledge->id}");

        } catch (\Exception $e) {
            Log::error("Failed to create embedding for AiKnowledge #{$this->knowledge->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Xatolik bo'lsa, keyinroq qayta urinib ko'rish uchun
            $this->release(60); // 1 daqiqadan keyin qayta
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("ProcessKnowledgeEmbedding job failed for knowledge #{$this->knowledge->id}", [
            'error' => $exception->getMessage()
        ]);
    }
}
