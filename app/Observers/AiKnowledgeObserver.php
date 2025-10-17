<?php

namespace App\Observers;

use App\AiKnowledge;
use App\Contracts\AIService;
use Illuminate\Support\Facades\Log;

class AiKnowledgeObserver
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function created(AiKnowledge $knowledge)
    {
        $this->generateEmbedding($knowledge);
    }

    public function updated(AiKnowledge $knowledge)
    {
        // Faqat value o'zgargan bo'lsa
        if ($knowledge->isDirty(['value'])) {
            $this->generateEmbedding($knowledge);
        }
    }

    protected function generateEmbedding(AiKnowledge $knowledge)
    {
        $this->retryEmbedding(function() use ($knowledge) {
            $text = "Kategoriya: {$knowledge->category}. " .
                    "Kalit: {$knowledge->key}. " .
                    "Qiymat: {$knowledge->value}. " .
                    "Izoh: {$knowledge->description}";
            
            $embedding = $this->aiService->embed($text);
            
            // Save qilmasdan to'g'ridan o'zgartiramiz (infinite loop oldini olish)
            AiKnowledge::where('id', $knowledge->id)->update([
                'embedding' => json_encode($embedding)
            ]);
            
            Log::info("Embedding yaratildi: AiKnowledge #{$knowledge->id}");
        }, "AiKnowledge #{$knowledge->id}");
    }

    protected function retryEmbedding($callback, $context)
    {
        $maxRetries = 3;
        $retryDelay = 15; // seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $callback();
                return; // Success, exit
            } catch (\Exception $e) {
                Log::warning("Embedding attempt {$attempt} failed for {$context}", [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt
                ]);

                if ($attempt < $maxRetries) {
                    sleep($retryDelay);
                } else {
                    Log::error("Embedding failed after {$maxRetries} attempts for {$context}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }
}
