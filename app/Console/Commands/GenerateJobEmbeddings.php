<?php

namespace App\Console\Commands;

use App\Contracts\AIService;
use App\Jobs;
use Illuminate\Console\Command;

class GenerateJobEmbeddings extends Command
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        parent::__construct();
        $this->aiService = $aiService;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:generate-embeddings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for all jobs (one-time or update)';

    public function handle()
    {
        $this->info('Jobs embedding jarayoni boshlandi...');

        $jobs = Jobs::whereNull('embedding')->get();
        $total = $jobs->count();

        if ($total === 0) {
            $this->info('Barcha job larda embedding mavjud!');
            return;
        }

        $this->info("Jami {$total}ta job embedding yaratiladi...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($jobs as $job) {
            try {
                $text = "{$job->title} {$job->company} {$job->location} {$job->type} " .
                        strip_tags($job->info) . " " . strip_tags($job->quals);
                
                $embedding = $this->aiService->embed($text);
                $job->embedding = json_encode($embedding);
                $job->save();

                $success++;
            } catch (\Exception $e) {
                $failed++;
                $this->error("\nJob ID {$job->id} embedding xato: {$e->getMessage()}");
            }

            $bar->advance();
            sleep(1); // API rate limit uchun
        }

        $bar->finish();

        $this->info("\n\nTugadi!");
        $this->info("Muvaffaqiyatli: {$success}");
        if ($failed > 0) {
            $this->warn("Xatolik: {$failed}");
        }
    }
}
