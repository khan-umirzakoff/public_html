<?php

namespace App\Observers;

use App\Trainings;
use App\Contracts\AIService;
use Illuminate\Support\Facades\Log;

class TrainingsObserver
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function created(Trainings $training)
    {
        $this->generateEmbedding($training);
    }

    public function updated(Trainings $training)
    {
        // Faqat title o'zgargan bo'lsa
        if ($training->isDirty(['title'])) {
            $this->generateEmbedding($training);
        }
    }

    protected function generateEmbedding(Trainings $training)
    {
        $this->retryEmbedding(function() use ($training) {
            $text = "Trening nomi: {$training->title}";
            
            $embedding = $this->aiService->embed($text);
            
            // Save qilmasdan to'g'ridan o'zgartiramiz (infinite loop oldini olish)
            Trainings::where('id', $training->id)->update([
                'embedding' => json_encode($embedding)
            ]);
            
            Log::info("Embedding yaratildi: Training #{$training->id}");
        }, "Training #{$training->id}");
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
