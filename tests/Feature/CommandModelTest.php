<?php

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

class CommandModelTest extends TestCase
{
    private string $modelPath = '';

    public function setUp(): void
    {
        parent::setUp();

        $this->modelPath = app_path('Models/');
    }

    #[Test]
    public function testProduct()
    {
        $this->artisan('code:build product --only=model')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default')
        ;

        $this->assertFileExists($this->modelPath . 'Product.php');

        $file = (new Filesystem())->get($this->modelPath . 'Product.php');

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

        $this->assertFileExists($this->modelPath . 'Foo.php');

        $file = (new Filesystem())->get($this->modelPath . 'Foo.php');

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

        $this->assertFileExists($this->modelPath . 'Category.php');

        $file = (new Filesystem())->get($this->modelPath . 'Category.php');

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
        $file = new Filesystem();
        $file->delete($this->modelPath . 'Product.php');
        $file->delete($this->modelPath . 'Foo.php');
        $file->delete($this->modelPath . 'Category.php');

        parent::tearDown();
    }
}
