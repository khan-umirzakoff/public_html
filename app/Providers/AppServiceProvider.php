<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Observers temporarily disabled - use batch embedding instead
        // Auto-embedding on create/update can cause timeouts in sync mode
        // Use "Barcha Embeddinglarni Yaratish" button in AI Knowledge page

        // \App\Jobs::observe(\App\Observers\JobObserver::class);
        // \App\News::observe(\App\Observers\NewsObserver::class);
        // \App\Trainings::observe(\App\Observers\TrainingsObserver::class);
        // \App\AiKnowledge::observe(\App\Observers\AiKnowledgeObserver::class);

        // Share $category variable globally with all views
        view()->composer('*', function ($view) {
            $category = \DB::select("SELECT * FROM category");
            $view->with('category', $category);
        });
    }
}
