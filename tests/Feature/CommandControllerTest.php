<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

final class CommandControllerTest extends TestCase
{
    private string $path = '';

    private Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->path = app_path('Http/Controllers/');

        $this->filesystem = new Filesystem();
    }

    #[Test]
    public function testProduct()
    {
        $this->artisan('code:build product --only=controller')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'ProductController.php');

        $file = $this->filesystem->get($this->path . 'ProductController.php');
        $this->assertStringContainsString('public function store(ProductRequest $request, AddProductAction $action): RedirectResponse', $file);
        $this->assertStringContainsString('ProductRequest $request, EditProductAction $action', $file);
    }

    #[Test]
    public function testProductCustomName()
    {
        $this->artisan('code:build foo --only=controller')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'FooController.php');

        $file = $this->filesystem->get($this->path . 'FooController.php');
        $this->assertStringContainsString('public function store(FooRequest $request, AddFooAction $action): RedirectResponse', $file);
        $this->assertStringContainsString('FooRequest $request, EditFooAction $action', $file);
    }

    public function tearDown(): void
    {
        $this->filesystem->delete($this->path . 'ProductController.php');
        $this->filesystem->delete($this->path . 'FooController.php');

        parent::tearDown();
    }
}