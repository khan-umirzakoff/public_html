<?php

namespace App\Observers;

use App\Jobs;
use App\Contracts\AIService;
use Illuminate\Support\Facades\Log;

class JobObserver
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function created(Jobs $job)
    {
        $this->generateEmbedding($job);
    }

    public function updated(Jobs $job)
    {
        // Faqat title, info, quals o'zgargan bo'lsa
        if ($job->isDirty(['title', 'info', 'quals', 'company', 'location'])) {
            $this->generateEmbedding($job);
        }
    }

    protected function generateEmbedding(Jobs $job)
    {
        $this->retryEmbedding(function() use ($job) {
            $text = "Lavozim: {$job->title}. " .
                    "Kompaniya: {$job->company}. " .
                    "Joylashuv: {$job->location}. " .
                    "Ish turi: {$job->type}. " .
                    "Ma'lumot: " . strip_tags($job->info) . ". " .
                    "Talablar: " . strip_tags($job->quals) . ". " .
                    "Imtiyozlar: " . strip_tags($job->benefits);
            
            $embedding = $this->aiService->embed($text);
            
            // Save qilmasdan to'g'ridan o'zgartiramiz (infinite loop oldini olish)
            Jobs::where('id', $job->id)->update([
                'embedding' => json_encode($embedding)
            ]);
            
            Log::info("Embedding yaratildi: Job #{$job->id}");
        }, "Job #{$job->id}");
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
