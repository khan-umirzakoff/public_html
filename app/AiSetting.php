<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AiSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    /**
     * Get a setting value by key with caching
     */
    public static function get($key, $default = null)
    {
        return Cache::remember("ai_setting_{$key}", 3600, function () use ($key, $default) {
            return self::where('key', $key)->value('value') ?? $default;
        });
    }

    /**
     * Set a setting value by key
     */
    public static function set($key, $value)
    {
        Cache::forget("ai_setting_{$key}");
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all settings as key-value pairs
     */
    public static function getAll()
    {
        return self::pluck('value', 'key')->toArray();
    }

    /**
     * Get AI Provider (openai or gemini)
     */
    public static function getProvider()
    {
        return self::get('ai_provider', 'gemini');
    }

    /**
     * Get System Prompt
     */
    public static function getSystemPrompt()
    {
        return self::get('system_prompt', 'You are a helpful assistant.');
    }

    /**
     * Set System Prompt
     */
    public static function setSystemPrompt($prompt)
    {
        return self::set('system_prompt', $prompt);
    }

    /**
     * Get API Configuration for current provider
     */
    public static function getApiConfig()
    {
        $provider = self::getProvider();

        if ($provider === 'openai') {
            return [
                'provider' => 'openai',
                'api_key' => self::get('openai_api_key'),
                'model' => self::get('openai_model', 'gpt-4o'),
                'embedding_model' => self::get('openai_embedding_model', 'text-embedding-3-small'),
            ];
        } else {
            return [
                'provider' => 'gemini',
                'api_key' => self::get('gemini_api_key'),
                'model' => self::get('gemini_model', 'gemini-2.0-flash-exp'),
                'embedding_model' => self::get('gemini_embedding_model', 'gemini-embedding-001'),
            ];
        }
    }
}
