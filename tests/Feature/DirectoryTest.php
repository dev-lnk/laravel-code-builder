<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

final class DirectoryTest extends TestCase
{
    private string $app_path = '';

    private string $base_path = '';

    private Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();

        $this->app_path = app_path('generation_dir/DTO/');

        $this->base_path = base_path('generation_dir/DTO/');
    }

    #[Test]
    public function testAppGenerationDir()
    {
        $this->artisan('code:build product --DTO')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', 'app/generation_dir');

        $this->assertFileExists($this->app_path . 'ProductDTO.php');

        $file = $this->filesystem->get($this->app_path . 'ProductDTO.php');
        $this->assertStringContainsString('namespace App\GenerationDir\DTO;', $file);
        $this->assertStringContainsString('use App\GenerationDir\Models\Product;', $file);
        $this->assertStringContainsString('use App\GenerationDir\Http\Requests\ProductRequest;', $file);
    }

    #[Test]
    public function testBaseGenerationDir()
    {
        $this->artisan('code:build product --DTO')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', 'generation_dir');

        $this->assertFileExists($this->base_path . 'ProductDTO.php');

        $file = $this->filesystem->get($this->base_path . 'ProductDTO.php');
        $this->assertStringContainsString('namespace GenerationDir\DTO;', $file);
        $this->assertStringContainsString('use GenerationDir\Models\Product;', $file);
        $this->assertStringContainsString('use GenerationDir\Http\Requests\ProductRequest;', $file);
    }

    public function tearDown(): void
    {
        $this->filesystem->delete($this->app_path . 'ProductDTO.php');

        $this->filesystem->delete($this->base_path . 'ProductDTO.php');

        parent::tearDown();
    }
}
