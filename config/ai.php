<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Service Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI service provider that will be used
    | by the application. You may change this value to any of the providers
    | configured below.
    |
    | Supported: "gemini", "openai"
    |
    */

    'provider' => env('AI_PROVIDER', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | AI Service Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the credentials for each of the AI service
    | providers used by your application.
    |
    */

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'embedding_model' => env('GEMINI_EMBEDDING_MODEL', 'text-embedding-004'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
    ],
];