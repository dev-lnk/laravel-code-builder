<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

final class CommandTableTest extends TestCase
{
    private string $path = '';

    private Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();

        $this->path = base_path('resources/views/product/');
    }

    #[Test]
    public function testProduct()
    {
        $this->artisan('code:build product --table')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'table.blade.php');

        $file = $this->filesystem->get($this->path . 'table.blade.php');
        $this->assertStringContainsString("<th>id</th>", $file);
        $this->assertStringContainsString("<th>user_id</th>", $file);
        $this->assertStringContainsString("<th>created_at</th>", $file);
        $this->assertStringContainsString("@foreach(\$products as \$product)", $file);
        $this->assertStringContainsString("<td>{{ \$product->id }}</td>", $file);
        $this->assertStringContainsString("<td>{{ \$product->user_id }}</td>", $file);
        $this->assertStringContainsString("<td>{{ \$product->created_at }}</td>", $file);
    }

    public function tearDown(): void
    {
        $this->filesystem->delete($this->path . 'table.blade.php');
        parent::tearDown();
    }
}
