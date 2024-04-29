<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

class CommandAddActionTest extends TestCase
{
    private string $path = '';

    public function setUp(): void
    {
        parent::setUp();

        $this->path = app_path('Actions/');
    }

    #[Test]
    public function testProduct()
    {
        $this->artisan('code:build product --only=addAction')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'AddProductAction.php');

        $file = (new Filesystem())->get($this->path . 'AddProductAction.php');

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

        $file = (new Filesystem())->get($this->path . 'AddFooAction.php');

        $this->assertStringContainsString('use App\Models\Foo;', $file);
        $this->assertStringContainsString('final class AddFooAction', $file);
        $this->assertStringContainsString('public function handle(array $data): Foo', $file);
        $this->assertStringContainsString('$model = new Foo()', $file);
    }

    public function tearDown(): void
    {
        $file = new Filesystem();
        $file->delete($this->path . 'AddProductAction.php');
        $file->delete($this->path . 'AddFooAction.php');

        parent::tearDown();
    }
}