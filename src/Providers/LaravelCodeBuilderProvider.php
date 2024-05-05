<?php

namespace DevLnk\LaravelCodeBuilder\Providers;

use DevLnk\LaravelCodeBuilder\Commands\LaravelCodeBuildCommand;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\AddActionBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\AddActionBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\ControllerBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\DTOBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\EditActionBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\FormBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\ModelBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\RequestBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\RouteBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\ControllerBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\DTOBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\EditActionBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\FormBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\ModelBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\RequestBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\RouteBuilder;
use Illuminate\Support\ServiceProvider;

class LaravelCodeBuilderProvider extends ServiceProvider
{
    /**
     * @var array<int, string>
     */
    protected array $commands = [
        LaravelCodeBuildCommand::class,
    ];

    public function register(): void
    {
        $this->app->bind(AddActionBuilderContract::class, AddActionBuilder::class);
        $this->app->bind(ControllerBuilderContract::class, ControllerBuilder::class);
        $this->app->bind(DTOBuilderContract::class, DTOBuilder::class);
        $this->app->bind(EditActionBuilderContract::class, EditActionBuilder::class);
        $this->app->bind(FormBuilderContract::class, FormBuilder::class);
        $this->app->bind(ModelBuilderContract::class, ModelBuilder::class);
        $this->app->bind(RequestBuilderContract::class, RequestBuilder::class);
        $this->app->bind(RouteBuilderContract::class, RouteBuilder::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }

        $this->publishes([
            __DIR__ . '/../../config/code_builder.php' => config_path('code_builder.php'),
        ], 'laravel-code-builder');

        $this->publishes([
            __DIR__ . '/../../code_stubs' => base_path('code_stubs'),
        ], 'laravel-code-builder-stubs');

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/code_builder.php',
            'code_builder'
        );
    }
}
