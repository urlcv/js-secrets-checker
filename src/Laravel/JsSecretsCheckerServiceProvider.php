<?php

declare(strict_types=1);

namespace URLCV\JsSecretsChecker\Laravel;

use Illuminate\Support\ServiceProvider;

class JsSecretsCheckerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'js-secrets-checker');
    }
}
