<?php

namespace DevLnk\LaravelCodeBuilder\Providers;

use DevLnk\LaravelCodeBuilder\Commands\LaravelCodeBuildCommand;
use Illuminate\Support\ServiceProvider;

class LaravelCodeBuilderProvider extends ServiceProvider
{
    /**
     * @var array<int, string>
     */
    protected array $commands = [
        LaravelCodeBuildCommand::class,
    ];

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }

        $this->publishes([
            __DIR__.'/../../config/code_builder.php' => config_path('code_builder.php'),
        ], 'laravel-code-builder');

        $this->publishes([
            __DIR__.'/../../code_stubs' => base_path('code_stubs'),
        ], 'laravel-code-builder-stubs');

        $this->mergeConfigFrom(
            __DIR__.'/../../config/code_builder.php',
            'code_builder'
        );
    }
}
