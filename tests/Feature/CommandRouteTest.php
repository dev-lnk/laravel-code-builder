<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

final class CommandRouteTest extends TestCase
{
    private string $path = '';

    private Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();

        $this->path = base_path('routes/');
    }

    #[Test]
    public function testProduct()
    {
        $this->artisan('code:build product --route')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'product.php');
        $file = $this->filesystem->get($this->path . 'product.php');
        $this->assertStringContainsString('use App\Http\Controllers\ProductController;', $file);
        $this->assertStringContainsString("Route::prefix('products')->controller(ProductController::class)->group(function (): void {", $file);
        $this->assertStringContainsString("Route::get('/', 'index')->name('products.index');", $file);
        $this->assertStringContainsString("Route::get('/create', 'create')->name('products.create');", $file);
        $this->assertStringContainsString("Route::post('/', 'store')->name('products.store');", $file);
        $this->assertStringContainsString("Route::get('/{id}/edit', 'edit')->name('products.edit');", $file);
        $this->assertStringContainsString("Route::put('/{id}', 'update')->name('products.update');", $file);
        $this->assertStringContainsString("Route::delete('/{id}', 'destroy')->name('products.destroy');", $file);
    }

    #[Test]
    public function testProductCustomName()
    {
        $this->artisan('code:build foo --route')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'foo.php');
        $file = $this->filesystem->get($this->path . 'foo.php');
        $this->assertStringContainsString('use App\Http\Controllers\FooController;', $file);
        $this->assertStringContainsString("Route::prefix('foos')->controller(FooController::class)->group(function (): void {", $file);
        $this->assertStringContainsString("Route::post('/', 'store')->name('foos.store');", $file);
        $this->assertStringContainsString("Route::put('/{id}', 'update')->name('foos.update');", $file);
    }

    public function tearDown(): void
    {
        $this->filesystem->delete($this->path . 'product.php');
        $this->filesystem->delete($this->path . 'foo.php');

        parent::tearDown();
    }
}
