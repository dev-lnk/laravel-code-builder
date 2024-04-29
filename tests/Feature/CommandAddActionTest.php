<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

class CommandAddActionTest extends TestCase
{
    private string $path = '';

    private Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();

        $this->path = app_path('Actions/');
    }

    #[Test]
    public function testProduct()
    {
        $this->artisan('code:build product --only=addAction')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'AddProductAction.php');

        $file = $this->filesystem->get($this->path . 'AddProductAction.php');

        $this->assertStringContainsString('use App\Models\Product;', $file);
        $this->assertStringContainsString('final class AddProductAction', $file);
        $this->assertStringContainsString('public function handle(array $data): Product', $file);
        $this->assertStringContainsString('$model = new Product()', $file);
    }

    #[Test]
    public function testProductCustomName()
    {
        $this->artisan('code:build foo --only=addAction')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'AddFooAction.php');

        $file = $this->filesystem->get($this->path . 'AddFooAction.php');

        $this->assertStringContainsString('use App\Models\Foo;', $file);
        $this->assertStringContainsString('final class AddFooAction', $file);
        $this->assertStringContainsString('public function handle(array $data): Foo', $file);
        $this->assertStringContainsString('$model = new Foo()', $file);
    }

    public function tearDown(): void
    {
        $this->filesystem->delete($this->path . 'AddProductAction.php');
        $this->filesystem->delete($this->path . 'AddFooAction.php');

        parent::tearDown();
    }
}