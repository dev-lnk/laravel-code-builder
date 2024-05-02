<?php

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
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
        $this->assertStringContainsString('use Illuminate\Database\Eloquent\Relations\BelongsTo;', $file);
        $this->assertStringContainsString("return \$this->belongsTo(User::class, 'user_id');", $file);
        $this->assertStringContainsString("return \$this->belongsTo(Category::class, 'category_id');", $file);
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
    public function testProductWithoutBelongsTo()
    {
        Config::set('code_builder.belongs_to');

        $this->artisan('code:build product --only=model')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Generate BelongsTo relations from foreign keys?', false)
            ->expectsQuestion('Where to generate the result?', '_default')
        ;

        $this->assertFileExists($this->path . 'Product.php');

        $file = $this->filesystem->get($this->path . 'Product.php');
        $this->assertStringNotContainsString('use Illuminate\Database\Eloquent\Relations\BelongsTo;', $file);
        $this->assertStringNotContainsString("return \$this->belongsTo(User::class, 'user_id');", $file);
        $this->assertStringNotContainsString("return \$this->belongsTo(Category::class, 'category_id');", $file);
    }

    #[Test]
    public function testProductHasMany()
    {
        $this->artisan('code:build product --only=model --has-many=comments')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default')
        ;

        $this->assertFileExists($this->path . 'Product.php');

        $file = $this->filesystem->get($this->path . 'Product.php');
        $this->assertStringContainsString('use Illuminate\\Database\\Eloquent\\Relations\\HasMany;', $file);
        $this->assertStringContainsString('return $this->hasMany(Comment::class, \'product_id\');', $file);
        $this->assertStringNotContainsString("\t\t'comments',", $file);
    }

    #[Test]
    public function testProductHasOne()
    {
        $this->artisan('code:build product --only=model --has-one=comments')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default')
        ;

        $this->assertFileExists($this->path . 'Product.php');

        $file = $this->filesystem->get($this->path . 'Product.php');
        $this->assertStringContainsString('use Illuminate\\Database\\Eloquent\\Relations\\HasOne;', $file);
        $this->assertStringContainsString('return $this->hasOne(Comment::class, \'product_id\');', $file);
        $this->assertStringNotContainsString("\t\t'comment',", $file);
    }

    #[Test]
    public function testProductBelongsToMany()
    {
        $this->artisan('code:build product --only=model --belongs-to-many=properties')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default')
        ;

        $this->assertFileExists($this->path . 'Product.php');

        $file = $this->filesystem->get($this->path . 'Product.php');
        $this->assertStringContainsString('use Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany;', $file);
        $this->assertStringContainsString('return $this->belongsToMany(Property::class);', $file);
        $this->assertStringNotContainsString("\t\t'properties',", $file);
        $this->assertStringNotContainsString("\t\t'property',", $file);
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
