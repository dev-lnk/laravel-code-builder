<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

final class CommandFormTest extends TestCase
{
    private string $path = '';

    private Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();

        $this->path = base_path('resources/views/');
    }

    #[Test]
    public function testProduct()
    {
        $this->artisan('code:build product --only=form')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'product.blade.php');

        $file = $this->filesystem->get($this->path . 'product.blade.php');
        $this->assertStringContainsString("<form action=\"{{ route('product.store') }}\" method=\"POST\">", $file);
        $this->assertStringContainsString('<input id="content" name="content"/>', $file);
        $this->assertStringContainsString('<input id="sort_number" name="sort_number" type="number"/>', $file);
    }

    #[Test]
    public function testUser()
    {
        $this->artisan('code:build user --only=form')
            ->expectsQuestion('Table', 'users')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'user.blade.php');

        $file = $this->filesystem->get($this->path . 'user.blade.php');
        $this->assertStringContainsString("<form action=\"{{ route('user.store') }}\" method=\"POST\">", $file);
        $this->assertStringContainsString('<input id="email" name="email" type="email"/>', $file);
        $this->assertStringContainsString('<input id="password" name="password" type="password"/>', $file);
    }

    #[Test]
    public function testProductCustomName()
    {
        $this->artisan('code:build foo --only=form')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'foo.blade.php');
        $file = $this->filesystem->get($this->path . 'foo.blade.php');
        $this->assertStringContainsString("<form action=\"{{ route('foo.store') }}\" method=\"POST\">", $file);
    }

    public function tearDown(): void
    {
        $this->filesystem->delete($this->path . 'product.blade.php');
        $this->filesystem->delete($this->path . 'foo.blade.php');
        $this->filesystem->delete($this->path . 'user.blade.php');
        parent::tearDown();
    }
}