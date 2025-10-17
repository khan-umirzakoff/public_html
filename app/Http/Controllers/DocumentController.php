<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Contracts\AIService;
use App\Jobs\ProcessDocumentEmbedding;

class DocumentController extends Controller
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

        $documents = DB::table('ai_documents')
            ->select(['id', 'title', 'category', 'description', 'file_name', 'file_size', 'embedding', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $categories = DB::table('ai_documents')->whereNotNull('category')->where('category', '!=', '')->select('category')->distinct()->pluck('category');

        return view('admin.pages.ai_documents', compact('documents', 'categories'));
    }

    public function upload(Request $request)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            } else {
                return redirect()->route("login2");
            }
        }

        $request->validate([
            'file' => 'required|file|mimes:txt,pdf,doc,docx,md,epub|max:102400', // 100MB max (for books)
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $file = $request->file('file');
            $content = $this->extractTextFromFile($file);

            if (empty($content)) {
                $error = 'Could not extract text from file';
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'error' => $error
                    ], 400);
                } else {
                    return redirect()->back()->with('error', $error);
                }
            }

            // Save to database without embedding
            $documentId = DB::table('ai_documents')->insertGetId([
                'title' => $request->title,
                'category' => $request->category ?? 'general', // Default to general if empty
                'description' => $request->description,
                'content' => $content,
                'embedding' => null,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Process embeddings immediately (sync mode)
            // This ensures chunking happens right away instead of waiting for queue worker
            try {
                $job = new ProcessDocumentEmbedding($documentId);
                $job->handle($this->aiService);
            } catch (\Exception $e) {
                Log::error('Document embedding processing error', [
                    'document_id' => $documentId,
                    'error' => $e->getMessage()
                ]);
                // Continue anyway - embedding can be regenerated later
            }

            $message = 'Document uploaded and processed successfully.';
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'document_id' => $documentId
                ]);
            } else {
                return redirect()->back()->with('success', $message);
            }

        } catch (\Exception $e) {
            Log::error('Document upload error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $error = 'Failed to process document';
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => $error
                ], 500);
            } else {
                return redirect()->back()->with('error', $error);
            }
        }
    }

    public function list()
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $documents = DB::table('ai_documents')
                ->select(['id', 'title', 'category', 'description', 'file_name', 'file_size', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'documents' => $documents
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch documents'
            ], 500);
        }
    }

    public function progress($id)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $progress = \Illuminate\Support\Facades\Cache::get('document_progress_' . $id, null);

        if ($progress) {
            return response()->json([
                'success' => true,
                'progress' => $progress
            ]);
        } else {
            // Check if embedding is done
            $document = DB::table('ai_documents')->where('id', $id)->select('embedding')->first();
            if ($document && $document->embedding) {
                return response()->json([
                    'success' => true,
                    'progress' => ['progress' => 100, 'chunks_processed' => 0, 'total_chunks' => 0]
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'progress' => ['progress' => 0, 'chunks_processed' => 0, 'total_chunks' => 0]
                ]);
            }
        }
    }

    public function delete($id)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $deleted = DB::table('ai_documents')->where('id', $id)->delete();
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Document not found'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete document'
            ], 500);
        }
    }

    protected function extractTextFromFile($file): string
    {
        // Increase memory limit and execution time for large files
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        $extension = strtolower($file->getClientOriginalExtension());
        $content = '';

        try {
            switch ($extension) {
                case 'txt':
                case 'md':
                    $content = file_get_contents($file->getPathname());
                    break;

                case 'pdf':
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($file->getPathname());
                    $content = $pdf->getText();
                    break;

                case 'doc':
                case 'docx':
                    $phpWord = \PhpOffice\PhpWord\IOFactory::load($file->getPathname());
                    $content = '';
                    foreach ($phpWord->getSections() as $section) {
                        foreach ($section->getElements() as $element) {
                            if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                                foreach ($element->getElements() as $textElement) {
                                    if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                                        $content .= $textElement->getText() . ' ';
                                    }
                                }
                            } elseif ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                                $content .= $element->getText() . ' ';
                            }
                        }
                    }
                    break;

                default:
                    $content = '';
            }

            // Clean and normalize text
            $content = trim($content);
            $content = preg_replace('/\s+/', ' ', $content); // Normalize whitespace

            return $content;

        } catch (\Exception $e) {
            Log::error('File text extraction error', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }
}