<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

class CommandRequestTest extends TestCase
{
    private string $path = '';

    private Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();

        $this->path = app_path('Http/Requests/');
    }

    #[Test]
    public function testProduct()
    {
        $this->artisan('code:build product --only=request')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'ProductRequest.php');

        $file = $this->filesystem->get($this->path . 'ProductRequest.php');
        $this->assertStringContainsString('class ProductRequest extends FormRequest', $file);
        $this->assertStringContainsString("'id' => ['int', 'nullable']", $file);
        $this->assertStringContainsString("'title' => ['string', 'nullable']", $file);
        $this->assertStringContainsString("'content' => ['string', 'nullable']", $file);
        $this->assertStringContainsString("'sort_number' => ['int', 'nullable']", $file);
        $this->assertStringContainsString("'user_id' => ['int', 'nullable']", $file);
        $this->assertStringContainsString("'category_id' => ['int', 'nullable']", $file);
    }

    #[Test]
    public function testUser()
    {
        $this->artisan('code:build user --only=request')
            ->expectsQuestion('Table', 'users')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'UserRequest.php');

        $file = $this->filesystem->get($this->path . 'UserRequest.php');
        $this->assertStringContainsString('class UserRequest extends FormRequest', $file);
        $this->assertStringContainsString("'email' => ['email', 'nullable']", $file);
        $this->assertStringContainsString("'password' => ['password', 'nullable']", $file);
    }

    #[Test]
    public function testProductCustomName()
    {
        $this->artisan('code:build foo --only=request')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'FooRequest.php');

        $file = (new Filesystem())->get($this->path . 'FooRequest.php');
        $this->assertStringContainsString('class FooRequest extends FormRequest', $file);
        $this->assertStringContainsString("'id' => ['int', 'nullable']", $file);
        $this->assertStringContainsString("'title' => ['string', 'nullable']", $file);
        $this->assertStringContainsString("'content' => ['string', 'nullable']", $file);
        $this->assertStringContainsString("'sort_number' => ['int', 'nullable']", $file);
        $this->assertStringContainsString("'user_id' => ['int', 'nullable']", $file);
        $this->assertStringContainsString("'category_id' => ['int', 'nullable']", $file);
    }

    public function tearDown(): void
    {

        $this->filesystem->delete($this->path . 'ProductRequest.php');
        $this->filesystem->delete($this->path . 'FooRequest.php');
        $this->filesystem->delete($this->path . 'UserRequest.php');

        parent::tearDown();
    }
}