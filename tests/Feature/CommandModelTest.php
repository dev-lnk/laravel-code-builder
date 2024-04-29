<?php

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

class CommandModelTest extends TestCase
{
    private string $path = '';

    private Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();

        $this->path = app_path('Models/');
    }

    #[Test]
    public function testProduct()
    {
        $this->artisan('code:build product --only=model')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default')
        ;

        $this->assertFileExists($this->path . 'Product.php');

        $file = $this->filesystem->get($this->path . 'Product.php');

        $this->assertStringContainsString('namespace App\Models;', $file);
        $this->assertStringContainsString('use Illuminate\Database\Eloquent\Model;', $file);
        $this->assertStringContainsString('protected $fillable = [', $file);
        $this->assertStringContainsString('class Product extends Model', $file);
        $this->assertStringContainsString('use SoftDeletes;', $file);
        $this->assertStringNotContainsString('public $timestamps = false;', $file);

        $this->assertStringContainsString('title', $file);
        $this->assertStringContainsString('content', $file);
        $this->assertStringContainsString('sort_number', $file);
        $this->assertStringContainsString('user_id', $file);
        $this->assertStringContainsString('category_id', $file);
        $this->assertStringContainsString('is_active', $file);
        $this->assertStringContainsString('sort_number', $file);
    }

    #[Test]
    public function testProductCustomName()
    {
        $this->artisan('code:build foo --only=model')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default')
        ;

        $this->assertFileExists($this->path . 'Foo.php');

        $file = $this->filesystem->get($this->path . 'Foo.php');

        $this->assertStringContainsString('namespace App\Models;', $file);
        $this->assertStringContainsString('use Illuminate\Database\Eloquent\Model;', $file);
        $this->assertStringContainsString('protected $fillable = [', $file);
        $this->assertStringContainsString('class Foo extends Model', $file);
        $this->assertStringContainsString('use SoftDeletes;', $file);
        $this->assertStringNotContainsString('public $timestamps = false;', $file);
        $this->assertStringNotContainsString("protected \$table = 'products';", $file);

        $this->assertStringContainsString('title', $file);
        $this->assertStringContainsString('content', $file);
        $this->assertStringContainsString('sort_number', $file);
        $this->assertStringContainsString('user_id', $file);
        $this->assertStringContainsString('category_id', $file);
        $this->assertStringContainsString('is_active', $file);
        $this->assertStringContainsString('sort_number', $file);
    }

    public function testCategory()
    {
        $this->artisan('code:build category --only=model')
            ->expectsQuestion('Table', 'categories')
            ->expectsQuestion('Where to generate the result?', '_default')
        ;

        $this->assertFileExists($this->path . 'Category.php');

        $file = $this->filesystem->get($this->path . 'Category.php');

        $this->assertStringContainsString('namespace App\Models;', $file);
        $this->assertStringContainsString('use Illuminate\Database\Eloquent\Model;', $file);
        $this->assertStringContainsString('protected $fillable = [', $file);
        $this->assertStringContainsString('class Category extends Model', $file);
        $this->assertStringContainsString('public $timestamps = false;', $file);
        $this->assertStringNotContainsString('use SoftDeletes;', $file);

        $this->assertStringContainsString('title', $file);
    }

    public function tearDown(): void
    {
        $this->filesystem->delete($this->path . 'Product.php');
        $this->filesystem->delete($this->path . 'Foo.php');
        $this->filesystem->delete($this->path . 'Category.php');

        parent::tearDown();
    }
}
