<?php

namespace App\Console\Commands;

use App\Contracts\AIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateAllEmbeddings extends Command
{
    protected $signature = 'embeddings:generate-all';
    protected $description = 'Generate embeddings for all content (Jobs, News, Trainings)';
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        parent::__construct();
        $this->aiService = $aiService;
    }

    public function handle()
    {
        $this->info('Barcha ma\'lumotlarni embedding qilish boshlandi...');
        $this->line('');

        $this->embedJobs();
        $this->embedNews();
        $this->embedTrainings();

        $this->info('');
        $this->info('Barcha embedding yaratish tugadi!');
    }

    protected function embedJobs()
    {
        $this->info('1. JOBS embedding...');
        
        $jobs = DB::table('jobs')->whereNull('embedding')->get();
        $total = $jobs->count();

        if ($total === 0) {
            $this->line('   Barcha job embedding qilingan');
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $success = 0;

        foreach ($jobs as $job) {
            try {
                $text = "{$job->title} {$job->company} {$job->location} {$job->type} " .
                        strip_tags($job->info) . " " . strip_tags($job->quals) . " " . strip_tags($job->benefits);
                
                $embedding = $this->aiService->embed($text);
                
                DB::table('jobs')->where('id', $job->id)->update([
                    'embedding' => json_encode($embedding)
                ]);

                $success++;
                $bar->advance();
                sleep(1);
            } catch (\Exception $e) {
                $this->error("\n   Xato: Job #{$job->id}");
                $bar->advance();
            }
        }

        $bar->finish();
        $this->line("\n   Tayyor: {$success}/{$total}");
    }

    protected function embedNews()
    {
        $this->info('2. NEWS embedding...');
        
        $items = DB::table('news')->whereNull('embedding')->get();
        $total = $items->count();

        if ($total === 0) {
            $this->line('   Barcha news embedding qilingan');
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $success = 0;

        foreach ($items as $item) {
            try {
                $text = ($item->title ?? '') . " " . strip_tags($item->desc ?? '');
                
                $embedding = $this->aiService->embed($text);
                
                DB::table('news')->where('id', $item->id)->update([
                    'embedding' => json_encode($embedding)
                ]);

                $success++;
                $bar->advance();
                sleep(1);
            } catch (\Exception $e) {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->line("\n   Tayyor: {$success}/{$total}");
    }

    protected function embedTrainings()
    {
        $this->info('3. TRAININGS embedding...');
        
        $items = DB::table('trainings')->whereNull('embedding')->get();
        $total = $items->count();

        if ($total === 0) {
            $this->line('   Barcha trainings embedding qilingan');
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $success = 0;

        foreach ($items as $item) {
            try {
                $text = ($item->title ?? '') . " " . strip_tags($item->desc ?? '');
                
                $embedding = $this->aiService->embed($text);
                
                DB::table('trainings')->where('id', $item->id)->update([
                    'embedding' => json_encode($embedding)
                ]);

                $success++;
                $bar->advance();
                sleep(1);
            } catch (\Exception $e) {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->line("\n   Tayyor: {$success}/{$total}");
    }
}
