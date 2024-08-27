<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

final class CommandTypeScriptTest extends TestCase
{
    private string $path = '';

    private Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();

        $this->path = base_path('resources/ts/');
    }

    #[Test]
    public function testProduct()
    {
        $this->artisan('code:build product --typeScript')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'product/product.ts');

        $file = $this->filesystem->get($this->path . 'product/product.ts');
        $this->assertStringContainsString("interface Product {", $file);
        $this->assertStringContainsString('id: number', $file);
        $this->assertStringContainsString('userId: number | null', $file);
        $this->assertStringContainsString('createdAt: string | null', $file);
    }

    #[Test]
    public function testProductHasMany()
    {
        $this->artisan('code:build product --typeScript --has-many=comments')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default')
        ;

        $this->assertFileExists($this->path . 'product/product.ts');

        $file = $this->filesystem->get($this->path . 'product/product.ts');
        $this->assertStringContainsString('comments: Comment[] // TODO The object must be imported', $file);
    }

    #[Test]
    public function testUser()
    {
        $this->artisan('code:build user --typeScript')
            ->expectsQuestion('Table', 'users')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'user/user.ts');

        $file = $this->filesystem->get($this->path . 'user/user.ts');
        $this->assertStringContainsString("interface User {", $file);
        $this->assertStringContainsString('email: string', $file);
        $this->assertStringContainsString('password: string', $file);
    }

    #[Test]
    public function testProductCustomName()
    {
        $this->artisan('code:build foo --typeScript --has-many=comments')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'foo/foo.ts');
        $file = $this->filesystem->get($this->path . 'foo/foo.ts');
        $this->assertStringContainsString("interface Foo {", $file);
        $this->assertStringContainsString('id: number', $file);
        $this->assertStringContainsString('userId: number | null', $file);
        $this->assertStringContainsString('createdAt: string | null', $file);
        $this->assertStringContainsString('comments: Comment[] // TODO The object must be imported', $file);
    }

    public function tearDown(): void
    {
        $this->filesystem->delete($this->path . 'product/product.ts');
        $this->filesystem->delete($this->path . 'user/user.ts');
        $this->filesystem->delete($this->path . 'foo/foo.ts');
        parent::tearDown();
    }
}
