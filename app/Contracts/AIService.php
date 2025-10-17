<?php

namespace App\Contracts;

interface AIService
{
    /**
     * Handles a simple, non-streaming chat request.
     */
    public function chat(string $prompt, array $history = []): string;

    /**
     * Handles a streaming chat request, supporting function calling and yielding events.
     *
     * @return \Generator<array>
     */
    public function streamChat(string $prompt, array $history = [], array $images = []): \Generator;

    /**
     * Handles a chat request with a single image (non-streaming).
     */
    public function chatWithImage(string $prompt, string $imageBase64, array $history = []): string;

    /**
     * Handles a chat request with multiple images (non-streaming).
     */
    public function chatWithImages(string $prompt, array $images, array $history = []): string;

    /**
     * Generates an embedding for a given text.
     */
    public function embed(string $text): array;

    /**
     * Gets the system prompt for the AI.
     */
    public function getSystemPrompt(): string;
}