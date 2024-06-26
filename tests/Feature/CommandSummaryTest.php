<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

class CommandSummaryTest extends TestCase
{
    private string $actionPath = '';

    private string $controllerPath = '';

    private string $formPath = '';

    private string $tablePath = '';

    private string $modelPath = '';

    private string $requestPath = '';

    private string $routePath = '';

    private string $DTOPath = '';

    private Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();

        $this->actionPath = app_path('Actions/');
        $this->controllerPath = app_path('Http/Controllers/');
        $this->formPath = base_path('resources/views/product/');
        $this->tablePath = base_path('resources/views/product/');
        $this->modelPath = app_path('Models/');
        $this->requestPath = app_path('Http/Requests/');
        $this->routePath = base_path('routes/');
        $this->DTOPath = app_path('DTO/');
    }

    #[Test]
    public function testProduct()
    {
        $this->artisan('code:build product')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        //dd($this->actionPath . 'AddProductAction.php');

        $this->assertFileExists($this->actionPath . 'AddProductAction.php');
        $this->assertFileExists($this->actionPath . 'EditProductAction.php');
        $this->assertFileExists($this->controllerPath . 'ProductController.php');
        $this->assertFileExists($this->formPath . 'form.blade.php');
        $this->assertFileExists($this->formPath . 'table.blade.php');
        $this->assertFileExists($this->modelPath . 'Product.php');
        $this->assertFileExists($this->requestPath . 'ProductRequest.php');
        $this->assertFileExists($this->routePath . 'product.php');
        $this->assertFileExists($this->DTOPath . 'ProductDTO.php');
    }

    #[Test]
    public function testProductGenerationPath()
    {
        $this->artisan('code:build product')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', 'app/Generation');

        $this->actionPath = app_path('Generation/Actions/');
        $this->controllerPath = app_path('Generation/Http/Controllers/');
        $this->formPath = app_path('Generation/resources/views/product/');
        $this->tablePath = app_path('Generation/resources/views/product/');
        $this->modelPath = app_path('Generation/Models/');
        $this->requestPath = app_path('Generation/Http/Requests/');
        $this->routePath = app_path('Generation/routes/');
        $this->DTOPath = app_path('Generation/DTO/');

        $controller = $this->filesystem->get($this->controllerPath . 'ProductController.php');
        $this->assertStringContainsString('namespace App\Generation\Http\Controllers;', $controller);
        $this->assertStringContainsString('use App\Generation\Http\Requests\ProductRequest;', $controller);

        $dto = $this->filesystem->get($this->DTOPath . 'ProductDTO.php');
        $this->assertStringContainsString('namespace App\Generation\DTO;', $dto);
        $this->assertStringContainsString('use App\Generation\Models\Product;', $dto);
        $this->assertStringContainsString('use App\Generation\Http\Requests\ProductRequest;', $dto);

        $this->assertFileExists($this->actionPath . 'AddProductAction.php');
        $this->assertFileExists($this->actionPath . 'EditProductAction.php');
        $this->assertFileExists($this->controllerPath . 'ProductController.php');
        $this->assertFileExists($this->formPath . 'form.blade.php');
        $this->assertFileExists($this->tablePath . 'table.blade.php');
        $this->assertFileExists($this->modelPath . 'Product.php');
        $this->assertFileExists($this->requestPath . 'ProductRequest.php');
        $this->assertFileExists($this->routePath . 'product.php');
        $this->assertFileExists($this->DTOPath . 'ProductDTO.php');
    }

    public function tearDown(): void
    {
        $this->filesystem->delete($this->actionPath . 'AddProductAction.php');
        $this->filesystem->delete($this->actionPath . 'EditProductAction.php');
        $this->filesystem->delete($this->controllerPath . 'ProductController.php');
        $this->filesystem->delete($this->formPath . 'form.blade.php');
        $this->filesystem->delete($this->tablePath . 'table.blade.php');
        $this->filesystem->delete($this->modelPath . 'Product.php');
        $this->filesystem->delete($this->requestPath . 'ProductRequest.php');
        $this->filesystem->delete($this->routePath . 'product.php');
        $this->filesystem->delete($this->DTOPath . 'ProductDTO.php');

        parent::tearDown();
    }
}
