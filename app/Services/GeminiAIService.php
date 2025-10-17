<?php

namespace App\Services;

use App\Contracts\AIService;
use App\Services\RAGService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class GeminiAIService implements AIService
{
    protected $client;
    protected $apiKey;
    protected $model;
    protected $embeddingModel;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->client = new Client(['timeout' => 120]);

        // Fetch settings from the dedicated config file (config/ai.php)
        // This is the correct Laravel practice and is reliable during console commands.
        $this->apiKey = Config::get('ai.gemini.key');
        $this->model = Config::get('ai.gemini.model', 'gemini-flash-latest');
        $this->embeddingModel = Config::get('ai.gemini.embedding_model', 'text-embedding-004');

        // The exception is thrown here if the key is not found after loading config.
        // This is the correct place for this check.
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Gemini API Key is not configured. Please check your .env file and config/ai.php.');
        }
    }

    public function streamChat(string $prompt, array $history = [], array $images = []): \Generator
    {
        $ragService = app(RAGService::class);
        $tools = $ragService->getToolDeclarations();
        
        $contents = $this->buildContents($prompt, $history, $images);

        $payload = [
            'contents' => $contents,
            'tools' => $tools,
            'systemInstruction' => [
                'parts' => [['text' => $this->getSystemPrompt()]]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 4096,
            ],
        ];

        yield ['thinking' => true];

        $responseStream = $this->makeStreamingRequest($payload);

        $functionCallParts = [];
        $sources = [];

        foreach ($responseStream as $event) {
            if (!empty($event['candidates'][0]['content']['parts'])) {
                $part = $event['candidates'][0]['content']['parts'][0];

                if (isset($part['functionCall'])) {
                    // Accumulate function call parts
                    $functionCallParts[] = $part['functionCall'];
                } elseif (isset($part['text'])) {
                    yield ['thinking' => false];
                    yield ['chunk' => $part['text']];
                }
            }
        }

        if (!empty($functionCallParts)) {
            yield ['thinking' => false]; // Stop initial thinking

            $fullFunctionCall = $this->assembleFunctionCall($functionCallParts);
            
            yield ['tool_start' => ['name' => $fullFunctionCall['name'], 'args' => $fullFunctionCall['args']]];

            $toolResult = $ragService->executeTool($fullFunctionCall['name'], $fullFunctionCall['args']);
            
            // Collect sources from the tool result if they exist
            if (isset($toolResult['sources']) && is_array($toolResult['sources'])) {
                $sources = array_merge($sources, $toolResult['sources']);
            }

            $contents[] = [
                'role' => 'model',
                'parts' => [['functionCall' => $fullFunctionCall]]
            ];
            $contents[] = [
                'role' => 'user',
                'parts' => [['functionResponse' => [
                    'name' => $fullFunctionCall['name'],
                    'response' => $toolResult
                ]]]
            ];

            $payload['contents'] = $contents;

            $finalResponseStream = $this->makeStreamingRequest($payload);

            foreach ($finalResponseStream as $finalEvent) {
                 if (!empty($finalEvent['candidates'][0]['content']['parts'][0]['text'])) {
                    yield ['chunk' => $finalEvent['candidates'][0]['content']['parts'][0]['text']];
                }
            }
        }

        yield ['done' => true, 'sources' => array_values(array_unique($sources, SORT_REGULAR))];
    }

    private function makeStreamingRequest(array $payload)
    {
        $url = "{$this->baseUrl}/models/{$this->model}:streamGenerateContent?key={$this->apiKey}&alt=sse";

        // Debug logging
        Log::info('Gemini API Request', [
            'model' => $this->model,
            'url' => str_replace($this->apiKey, 'REDACTED', $url),
            'payload_structure' => [
                'has_contents' => isset($payload['contents']),
                'has_tools' => isset($payload['tools']),
                'has_systemInstruction' => isset($payload['systemInstruction']),
                'tools_count' => isset($payload['tools'][0]['functionDeclarations']) ? count($payload['tools'][0]['functionDeclarations']) : 0
            ]
        ]);

        $response = $this->client->post($url, ['json' => $payload, 'stream' => true]);
        $body = $response->getBody();

        while (!$body->eof()) {
            $line = $this->readStreamLine($body);
            if (strpos($line, 'data: ') === 0) {
                $json = trim(substr($line, 6));
                yield json_decode($json, true);
            }
        }
    }

    private function readStreamLine($stream) {
        $buffer = '';
        while (strpos($buffer, "\n") === false) {
            if ($stream->eof()) {
                return $buffer;
            }
            $buffer .= $stream->read(1);
        }
        return $buffer;
    }

    private function assembleFunctionCall(array $parts): array
    {
        $name = '';
        $argsJson = '';
        foreach ($parts as $part) {
            if (!empty($part['name'])) {
                $name = $part['name'];
            }
            if (!empty($part['args'])) {
                $argsJson .= json_encode($part['args']);
            }
        }
        // This is a simplified assembly. A more robust solution might need to merge JSON objects.
        // For now, we assume args come in one complete chunk.
        $decodedArgs = json_decode(str_replace('}{', ',', $argsJson), true);
        return ['name' => $name, 'args' => $decodedArgs ?? []];
    }

    private function buildContents(string $prompt, array $history, array $images): array
    {
        $contents = [];
        foreach ($history as $message) {
            $contents[] = [
                'role' => $message['role'] ?? 'user',
                'parts' => [['text' => $message['text']]]
            ];
        }

        $userParts = [];
        if (!empty($prompt)) {
            $userParts[] = ['text' => $prompt];
        }
        foreach ($images as $imageBase64) {
            $userParts[] = ['inlineData' => ['mimeType' => 'image/jpeg', 'data' => $imageBase64]];
        }

        if (!empty($userParts)) {
            $contents[] = ['role' => 'user', 'parts' => $userParts];
        }

        // Log final contents structure for debugging
        Log::info('Built Gemini Contents', [
            'total_messages' => count($contents),
            'current_prompt' => mb_substr($prompt, 0, 100),
            'last_content' => !empty($contents) ? [
                'role' => $contents[count($contents) - 1]['role'],
                'text' => mb_substr($contents[count($contents) - 1]['parts'][0]['text'] ?? '', 0, 100)
            ] : null
        ]);

        return $contents;
    }

    public function embed(string $text): array
    {
        $url = "{$this->baseUrl}/models/{$this->embeddingModel}:embedContent?key={$this->apiKey}";

        try {
            $response = $this->client->post($url, [
                'json' => ['model' => "models/{$this->embeddingModel}", 'content' => ['parts' => [['text' => $text]]]]
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['embedding']['values'] ?? [];
        } catch (RequestException $e) {
            Log::error('Gemini Embed Request Failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Embedding service is currently unavailable');
        }
    }

    public function getSystemPrompt(): string
    {
        return \App\AiSetting::getSystemPrompt();
    }

    // The following methods are now deprecated in favor of streamChat and will be removed.
    public function chat(string $prompt, array $history = []): string {
        // This method should ideally not be called directly anymore.
        // For compatibility, we can simulate a non-streaming call.
        $generator = $this->streamChat($prompt, $history);
        $fullResponse = '';
        foreach($generator as $event) {
            if(isset($event['chunk'])) {
                $fullResponse .= $event['chunk'];
            }
        }
        return $fullResponse;
    }
    public function chatWithImage(string $prompt, string $imageBase64, array $history = []): string {
        return $this->chatWithImages($prompt, [$imageBase64], $history);
    }
    public function chatWithImages(string $prompt, array $images, array $history = []): string {
        $generator = $this->streamChat($prompt, $history, $images);
        $fullResponse = '';
        foreach($generator as $event) {
            if(isset($event['chunk'])) {
                $fullResponse .= $event['chunk'];
            }
        }
        return $fullResponse;
    }
}