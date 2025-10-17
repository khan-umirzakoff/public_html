<?php

namespace App\Observers;

use App\News;
use App\Contracts\AIService;
use Illuminate\Support\Facades\Log;

class NewsObserver
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function created(News $news)
    {
        $this->generateEmbedding($news);
    }

    public function updated(News $news)
    {
        // Faqat title, about, info o'zgargan bo'lsa
        if ($news->isDirty(['title', 'about', 'info'])) {
            $this->generateEmbedding($news);
        }
    }

    protected function generateEmbedding(News $news)
    {
        $this->retryEmbedding(function() use ($news) {
            $text = "Sarlavha: {$news->title}. " .
                    "Qisqacha: {$news->about}. " .
                    "To'liq ma'lumot: " . strip_tags($news->info);
            
            $embedding = $this->aiService->embed($text);
            
            // Save qilmasdan to'g'ridan o'zgartiramiz (infinite loop oldini olish)
            News::where('id', $news->id)->update([
                'embedding' => json_encode($embedding)
            ]);
            
            Log::info("Embedding yaratildi: News #{$news->id}");
        }, "News #{$news->id}");
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
