<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

class CommandEditActionTest extends TestCase
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
        $this->artisan('code:build product --editAction')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'EditProductAction.php');

        $file = $this->filesystem->get($this->path . 'EditProductAction.php');

        $this->assertStringContainsString('use App\Models\Product;', $file);
        $this->assertStringContainsString('final class EditProductAction', $file);
        $this->assertStringContainsString('public function handle(int $id, array $data): Product', $file);
        $this->assertStringContainsString("\$model = Product::query()->where('id', \$id)->first();", $file);
    }

    #[Test]
    public function testProductCustomName()
    {
        $this->artisan('code:build foo --editAction')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'EditFooAction.php');

        $file = $this->filesystem->get($this->path . 'EditFooAction.php');

        $this->assertStringContainsString('use App\Models\Foo;', $file);
        $this->assertStringContainsString('final class EditFooAction', $file);
        $this->assertStringContainsString('public function handle(int $id, array $data): Foo', $file);
        $this->assertStringContainsString("\$model = Foo::query()->where('id', \$id)->first();", $file);
    }

    public function tearDown(): void
    {
        $this->filesystem->delete($this->path . 'EditProductAction.php');
        $this->filesystem->delete($this->path . 'EditFooAction.php');

        parent::tearDown();
    }
}
