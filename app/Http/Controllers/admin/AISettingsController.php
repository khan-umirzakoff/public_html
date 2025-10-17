<?php

namespace App\Http\Controllers\admin;

use App\AiSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AISettingsController extends Controller
{
    public function index()
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        $settings = AiSetting::orderBy('key')->get()->groupBy(function($setting) {
            if (str_starts_with($setting->key, 'openai_')) return 'openai';
            if (str_starts_with($setting->key, 'gemini_')) return 'gemini';
            if ($setting->key === 'ai_provider') return 'general';
            if ($setting->key === 'system_prompt') return 'system';
            return 'other';
        });

        $currentProvider = AiSetting::getProvider();
        $systemPrompt = AiSetting::getSystemPrompt();

        return view('admin.pages.ai_settings', compact('settings', 'currentProvider', 'systemPrompt'));
    }

    public function update(Request $request)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return redirect()->route("login2");
        }

        $validated = $request->validate([
            'ai_provider' => 'required|in:openai,gemini',
            'openai_api_key' => 'nullable|string',
            'openai_model' => 'nullable|string',
            'openai_embedding_model' => 'nullable|string',
            'gemini_api_key' => 'nullable|string',
            'gemini_model' => 'nullable|string',
            'gemini_embedding_model' => 'nullable|string',
            'system_prompt' => 'required|string',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                AiSetting::set($key, $value);
            }
        }

        // Sync critical settings to .env file for flexibility
        $this->syncToEnv([
            'AI_PROVIDER' => $validated['ai_provider'] ?? null,
            'GEMINI_API_KEY' => $validated['gemini_api_key'] ?? null,
            'GEMINI_MODEL' => $validated['gemini_model'] ?? null,
            'GEMINI_EMBEDDING_MODEL' => $validated['gemini_embedding_model'] ?? null,
            'OPENAI_API_KEY' => $validated['openai_api_key'] ?? null,
            'OPENAI_MODEL' => $validated['openai_model'] ?? null,
            'OPENAI_EMBEDDING_MODEL' => $validated['openai_embedding_model'] ?? null,
        ]);

        // Clear all AI-related caches
        Cache::flush();

        return redirect()->back()->with('success', 'AI Settings updated successfully and synced to .env');
    }

    public function testConnection(Request $request)
    {
        session_start();
        if (!isset($_SESSION['company_id'])){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $provider = $request->input('provider');
        $apiKey = $request->input('api_key');

        try {
            // Test API connection based on provider
            if ($provider === 'openai') {
                // Simple test request to OpenAI
                $ch = curl_init('https://api.openai.com/v1/models');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $apiKey,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    return response()->json(['success' => true, 'message' => 'OpenAI connection successful']);
                } else {
                    return response()->json(['success' => false, 'message' => 'Invalid OpenAI API key'], 400);
                }
            } else {
                // Test Gemini connection
                $url = "https://generativelanguage.googleapis.com/v1/models?key={$apiKey}";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    return response()->json(['success' => true, 'message' => 'Gemini connection successful']);
                } else {
                    return response()->json(['success' => false, 'message' => 'Invalid Gemini API key'], 400);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Sync database settings to .env file
     * This allows both admin panel and .env configuration
     */
    private function syncToEnv(array $settings)
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return false;
        }

        try {
            $envContent = file_get_contents($envPath);

            foreach ($settings as $key => $value) {
                if ($value === null) continue;

                // Escape special characters in value
                $escapedValue = $this->escapeEnvValue($value);

                // Check if key exists in .env
                $pattern = "/^{$key}=.*$/m";

                if (preg_match($pattern, $envContent)) {
                    // Update existing key
                    $envContent = preg_replace($pattern, "{$key}={$escapedValue}", $envContent);
                } else {
                    // Add new key at the end of AI section
                    $aiSectionPattern = "/(# Google Gemini.*?(?=\n# |$))/s";
                    if (preg_match($aiSectionPattern, $envContent, $matches)) {
                        $aiSection = $matches[1];
                        $newAiSection = $aiSection . "\n{$key}={$escapedValue}";
                        $envContent = str_replace($aiSection, $newAiSection, $envContent);
                    } else {
                        // If AI section not found, append at end
                        $envContent .= "\n{$key}={$escapedValue}";
                    }
                }
            }

            file_put_contents($envPath, $envContent);

            // Reload environment variables
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to sync settings to .env', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Escape value for .env file
     */
    private function escapeEnvValue($value)
    {
        // If value contains spaces or special characters, wrap in quotes
        if (preg_match('/[\s#"\'\\\\]/', $value)) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }
        return $value;
    }
}
