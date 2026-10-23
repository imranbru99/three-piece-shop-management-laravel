<?php

namespace App\Providers;

use App\Modules\ModuleRegistry;
use App\Support\Language;
use App\Support\PublicContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use ImranDevBd\AiHub\Facades\AIHub;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('*', function ($view): void {
            $registry = app(ModuleRegistry::class);
            $view->with('t', Language::text(...));
            $view->with('modules', $registry->all());
            $view->with('presets', $registry->presets());
            $view->with('faqs', PublicContent::faqs());
            $view->with('money', fn (int|float $amount): string => '৳ '.number_format($amount / 100, 2));
        });
        Model::preventLazyLoading(! $this->app->isProduction());
        AIHub::auth(fn (): bool => false);

        if ($this->app->runningUnitTests()) {
            Http::preventStrayRequests();
        }
    }
}
