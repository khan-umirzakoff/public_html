<?php

namespace App\Providers;

use App\Contracts\AIService;
use App\Services\GeminiAIService;
use App\Services\OpenAIService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AIServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     * This is crucial for fixing setup issues where artisan commands fail
     * because they try to instantiate services before the .env file is loaded.
     * @var bool
     */
    protected $defer = true;

    /**
     * Register services.
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AIService::class, function ($app) {
            // This closure is deferred and will only run when AIService is resolved.
            $provider = 'gemini'; // Default provider

            try {
                // Check if the 'ai_settings' table exists before querying it.
                // This prevents errors during initial migrations (php artisan migrate).
                if (Schema::hasTable('ai_settings')) {
                    $providerSetting = DB::table('ai_settings')->where('key', 'provider')->value('value');
                    if ($providerSetting) {
                        $provider = $providerSetting;
                    } else {
                        // If DB is there but setting is not, use config file as fallback.
                        $provider = Config::get('ai.provider', 'gemini');
                    }
                } else {
                    // If table doesn't exist, use config file.
                    $provider = Config::get('ai.provider', 'gemini');
                }
            } catch (\Exception $e) {
                // If any DB error occurs, fall back to the config file.
                // This makes the setup process much more resilient.
                $provider = Config::get('ai.provider', 'gemini');
            }

            switch (strtolower($provider)) {
                case 'openai':
                case 'chatgpt':
                case 'gpt':
                    return new OpenAIService();

                case 'gemini':
                case 'google':
                default:
                    return new GeminiAIService();
            }
        });
    }

    /**
     * Bootstrap services.
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     * Required for deferred providers.
     * @return array
     */
    public function provides()
    {
        return [AIService::class];
    }
}