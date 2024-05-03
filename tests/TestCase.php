<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Tests;

use DevLnk\LaravelCodeBuilder\Providers\LaravelCodeBuilderProvider;
use DevLnk\LaravelCodeBuilder\Tests\Fixtures\TestServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->performApplication();
    }

    protected function performApplication(): void
    {
        $this->artisan('vendor:publish --tag=laravel-code-builder');

        Config::set('code_builder.belongs_to', true);
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelCodeBuilderProvider::class,
            TestServiceProvider::class,
        ];
    }
}
