<?php

namespace App\Http\Controllers\admin;

use App\AiKnowledge;
use App\AiSetting;
use App\Contracts\AIService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AIKnowledgeController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }
    public function index()
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        $knowledge = AiKnowledge::orderBy('category')
            ->orderBy('priority', 'desc')
            ->paginate(20);

        $categories = AiKnowledge::select('category')->distinct()->pluck('category');

        // Embedding statistics
        $stats = [
            'ai_knowledge' => [
                'total' => AiKnowledge::count(),
                'with_embedding' => AiKnowledge::whereNotNull('embedding')->where('embedding', '!=', '[]')->where('embedding', '!=', '')->count(),
            ],
            'jobs' => [
                'total' => \App\Jobs::count(),
                'with_embedding' => \App\Jobs::whereNotNull('embedding')->count(),
            ],
            'news' => [
                'total' => \App\News::count(),
                'with_embedding' => \App\News::whereNotNull('embedding')->count(),
            ],
            'trainings' => [
                'total' => \App\Trainings::count(),
                'with_embedding' => \App\Trainings::whereNotNull('embedding')->count(),
            ],
            'ai_documents' => [
                'total' => DB::table('ai_documents')->count(),
                'with_embedding' => DB::table('ai_documents')->whereNotNull('embedding')->count(),
            ],
        ];

        return view('admin.pages.ai_knowledge', compact('knowledge', 'categories', 'stats'));
    }

    public function store(Request $request)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        $request->validate([
            'category' => 'required|string|max:50',
            'key' => 'required|string|max:100',
            'value' => 'required|string',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer|min:0|max:5',
        ]);

        $data = $request->all();

        $knowledge = AiKnowledge::create($data);

        // Generate embedding after creation
        try {
            $text = "Category: {$knowledge->category}. " .
                    "Key: {$knowledge->key}. " .
                    "Value: {$knowledge->value}. " .
                    "Description: {$knowledge->description}";

            $embedding = $this->aiService->embed($text);

            $knowledge->update(['embedding' => json_encode($embedding)]);

            return redirect()->back()->with('success', 'Knowledge item successfully added and embedded.');

        } catch (\Exception $e) {
            Log::error('Embedding generation failed on store', [
                'knowledge_id' => $knowledge->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('success', 'Knowledge item added, but failed to generate embedding: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        $knowledge = AiKnowledge::findOrFail($id);

        $request->validate([
            'category' => 'required|string|max:50',
            'key' => 'required|string|max:100',
            'value' => 'required|string',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer|min:0|max:5',
        ]);

        $data = $request->all();

        $knowledge->update($data);

        // Re-generate embedding after update
        try {
            $text = "Category: {$knowledge->category}. " .
                    "Key: {$knowledge->key}. " .
                    "Value: {$knowledge->value}. " .
                    "Description: {$knowledge->description}";

            $embedding = $this->aiService->embed($text);

            $knowledge->update(['embedding' => json_encode($embedding)]);

            return redirect()->back()->with('success', 'Knowledge item and embedding updated successfully.');

        } catch (\Exception $e) {
            Log::error('Embedding generation failed on update', [
                'knowledge_id' => $knowledge->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('success', 'Knowledge item updated, but failed to regenerate embedding: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        AiKnowledge::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Knowledge item deleted.');
    }

    public function generateEmbedding($id)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        $knowledge = AiKnowledge::findOrFail($id);

        try {
            $text = "Category: {$knowledge->category}. " .
                    "Key: {$knowledge->key}. " .
                    "Value: {$knowledge->value}. " .
                    "Description: {$knowledge->description}";

            $embedding = $this->aiService->embed($text);

            $knowledge->update(['embedding' => json_encode($embedding)]);

            return redirect()->back()->with('success', 'Embedding generated successfully.');

        } catch (\Exception $e) {
            Log::error('Manual embedding generation failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Embedding generation failed: ' . $e->getMessage());
        }
    }

    public function generateAllEmbeddings()
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        try {
            set_time_limit(600);
            ini_set('max_execution_time', '600');

            $jobsCount = \App\Jobs::whereNull('embedding')->count();
            $newsCount = \App\News::whereNull('embedding')->count();
            $trainingsCount = \App\Trainings::whereNull('embedding')->count();
            $knowledgeCount = AiKnowledge::whereNull('embedding')->orWhere('embedding', '')->count();
            $documentsCount = DB::table('ai_documents')->whereNull('embedding')->count();

            $totalCount = $jobsCount + $newsCount + $trainingsCount + $knowledgeCount + $documentsCount;

            if ($totalCount === 0) {
                return redirect()->back()->with('info', 'All items are already embedded!');
            }

            Log::info('Batch embedding started', ['total' => $totalCount]);

            $processed = 0;
            $failed = 0;

            // Process Jobs (10 at a time)
            $jobs = \App\Jobs::whereNull('embedding')->take(10)->get();
            foreach ($jobs as $job) {
                try {
                    $text = "Lavozim: {$job->title}. Kompaniya: {$job->company}. Joylashuv: {$job->location}. " .
                            "Ma'lumot: " . strip_tags($job->info);
                    $embedding = $this->aiService->embed($text);
                    \App\Jobs::where('id', $job->id)->update(['embedding' => json_encode($embedding)]);
                    $processed++;
                    usleep(300000); // 0.3 second delay
                } catch (\Exception $e) {
                    Log::error("Job embedding failed", ['id' => $job->id, 'error' => $e->getMessage()]);
                    $failed++;
                }
            }

            // Process News (10 at a time)
            $news = \App\News::whereNull('embedding')->take(10)->get();
            foreach ($news as $item) {
                try {
                    $text = "Sarlavha: {$item->title}. Mazmun: " . strip_tags($item->desc);
                    $embedding = $this->aiService->embed($text);
                    \App\News::where('id', $item->id)->update(['embedding' => json_encode($embedding)]);
                    $processed++;
                    usleep(300000);
                } catch (\Exception $e) {
                    Log::error("News embedding failed", ['id' => $item->id, 'error' => $e->getMessage()]);
                    $failed++;
                }
            }

            // Process Trainings (10 at a time)
            $trainings = \App\Trainings::whereNull('embedding')->take(10)->get();
            foreach ($trainings as $training) {
                try {
                    $text = "Trening: {$training->title}. Tavsif: " . strip_tags($training->description);
                    $embedding = $this->aiService->embed($text);
                    \App\Trainings::where('id', $training->id)->update(['embedding' => json_encode($embedding)]);
                    $processed++;
                    usleep(300000);
                } catch (\Exception $e) {
                    Log::error("Training embedding failed", ['id' => $training->id, 'error' => $e->getMessage()]);
                    $failed++;
                }
            }

            // Process AI Knowledge (10 at a time)
            $knowledge = AiKnowledge::where(function($q) {
                $q->whereNull('embedding')->orWhere('embedding', '');
            })->take(10)->get();
            foreach ($knowledge as $item) {
                try {
                    $text = "Kategoriya: {$item->category}. Kalit: {$item->key}. Qiymat: {$item->value}. Tavsif: {$item->description}";
                    $embedding = $this->aiService->embed($text);
                    $item->update(['embedding' => json_encode($embedding)]);
                    $processed++;
                    usleep(300000);
                } catch (\Exception $e) {
                    Log::error("Knowledge embedding failed", ['id' => $item->id, 'error' => $e->getMessage()]);
                    $failed++;
                }
            }

            // Process AI Documents (10 at a time)
            $documents = DB::table('ai_documents')->whereNull('embedding')->take(10)->get();
            foreach ($documents as $doc) {
                try {
                    $text = "Hujjat: {$doc->title}. Kategoriya: {$doc->category}. Mazmun: " . strip_tags($doc->content);
                    $embedding = $this->aiService->embed($text);
                    DB::table('ai_documents')->where('id', $doc->id)->update(['embedding' => json_encode($embedding)]);
                    $processed++;
                    usleep(300000);
                } catch (\Exception $e) {
                    Log::error("Document embedding failed", ['id' => $doc->id, 'error' => $e->getMessage()]);
                    $failed++;
                }
            }

            Log::info("Batch embedding completed", ['processed' => $processed, 'failed' => $failed]);

            $remaining = $totalCount - $processed;
            $message = "Success! {$processed} embeddings were generated.";
            if ($failed > 0) {
                $message .= " {$failed} items failed.";
            }
            if ($remaining > 0) {
                $message .= " {$remaining} items remaining. Press the button again to continue.";
            } else {
                $message .= " All items are now processed!";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Batch embedding failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function seedDefault()
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        $defaultKnowledge = [
            [
                'category' => 'contact',
                'key' => 'Phone Number',
                'value' => '+998 71 123 45 67',
                'description' => 'Main contact number',
                'priority' => 5,
            ],
            [
                'category' => 'contact',
                'key' => 'Email',
                'value' => 'info@jobcare.uz',
                'description' => 'Email address',
                'priority' => 5,
            ],
            [
                'category' => 'contact',
                'key' => 'Working Hours',
                'value' => 'Monday-Friday: 9:00 AM - 6:00 PM',
                'description' => 'Office working hours',
                'priority' => 4,
            ],
            [
                'category' => 'about',
                'key' => 'About the Platform',
                'value' => 'JobCare is the largest job-seeking platform in Uzbekistan. We connect job seekers and employers.',
                'description' => 'Brief description',
                'priority' => 5,
            ],
            [
                'category' => 'service',
                'key' => 'Free Services',
                'value' => 'Viewing job postings, uploading a CV, and applying for vacancies are completely free!',
                'description' => 'Free services',
                'priority' => 4,
            ],
            [
                'category' => 'faq',
                'key' => 'How to Register',
                'value' => 'Click the "Sign Up" button at the top of the site and enter your information.',
                'description' => 'Registration process',
                'priority' => 3,
            ],
        ];

        foreach ($defaultKnowledge as $item) {
            $knowledge = AiKnowledge::updateOrCreate(
                ['category' => $item['category'], 'key' => $item['key']],
                $item
            );

            try {
                $text = "Category: {$knowledge->category}. " .
                        "Key: {$knowledge->key}. " .
                        "Value: {$knowledge->value}. " .
                        "Description: {$knowledge->description}";

                $embedding = $this->aiService->embed($text);
                $knowledge->update(['embedding' => json_encode($embedding)]);
                usleep(300000);
            } catch (\Exception $e) {
                Log::error('Embedding generation failed on seed', [
                    'knowledge_id' => $knowledge->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()->back()->with('success', 'Default data has been seeded and embedded.');
    }
}