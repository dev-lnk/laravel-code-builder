<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Tests\Feature;

use DevLnk\LaravelCodeBuilder\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;

class CommandDtoTest extends TestCase
{
    private string $path = '';

    private Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();

        $this->path = app_path('DTO/');
    }

    #[Test]
    public function testProduct()
    {
        $this->artisan('code:build product --has-one=images --has-many=comments --belongs-to-many=properties --only=DTO')
            ->expectsQuestion('Table', 'products')
            ->expectsQuestion('Where to generate the result?', '_default');

        $this->assertFileExists($this->path . 'ProductDTO.php');

        $file = $this->filesystem->get($this->path . 'ProductDTO.php');
        $this->assertStringContainsString('use App\Models\Product;', $file);
        $this->assertStringContainsString('use App\Http\Requests\ProductRequest;', $file);
        $this->assertStringContainsString('private int $id', $file);
        $this->assertStringContainsString("private string \$title,\n\t\tprivate string \$content = 'Default',", $file);
        $this->assertStringContainsString('private int $sortNumber = 0,', $file);
        $this->assertStringContainsString('private int $sortNumber = 0,', $file);
        $this->assertStringContainsString('private array $comments = [],', $file);
        $this->assertStringContainsString('private ?ImageDTO $image = null,', $file);
        $this->assertStringContainsString('private array $properties = [],', $file);
        $this->assertStringContainsString('private ?string $createdAt = null,', $file);

        $this->assertStringContainsString('public static function fromArray(array $data): self', $file);
        $this->assertStringContainsString("id: \$data['id'],", $file);
        $this->assertStringContainsString("content: \$data['content'] ?? 'Default',", $file);
        $this->assertStringContainsString("sortNumber: \$data['sort_number'] ?? 0,", $file);
        $this->assertStringContainsString("isActive: \$data['is_active'] ?? false,", $file);
        $this->assertStringContainsString("comments: \$data['comments'] ?? [],", $file);
        $this->assertStringContainsString("properties: \$data['properties'] ?? [],", $file);
        $this->assertStringContainsString("createdAt: \$data['created_at'] ?? null,", $file);

        $this->assertStringContainsString("public static function fromRequest(ProductRequest \$request): self", $file);
        $this->assertStringContainsString("id: (int) \$request->input('id'),", $file);
        $this->assertStringContainsString("title: \$request->input('title'),", $file);
        $this->assertStringContainsString("content: \$request->input('content'),", $file);
        $this->assertStringContainsString("sortNumber: (int) \$request->input('sort_number'),", $file);
        $this->assertStringContainsString("isActive: \$request->has('is_active'),", $file);

        $this->assertStringContainsString("public static function fromModel(Product \$model): self", $file);
        $this->assertStringContainsString("id: (int) \$model->id,", $file);
        $this->assertStringContainsString("title: \$model->title,", $file);
        $this->assertStringContainsString("sortNumber: (int) \$model->sort_number,", $file);
        $this->assertStringContainsString("isActive: (bool) \$model->is_active,", $file);
        $this->assertStringContainsString("comments: \$model->comments->toArray(),", $file);
        $this->assertStringContainsString("image: \$model->image ? ImageDTO::fromModel(\$model->image) : null,", $file);
        $this->assertStringContainsString("properties: \$model->properties->toArray(),", $file);
        $this->assertStringContainsString("createdAt: \$model->created_at?->format('Y-m-d H:i:s'),", $file);

        $this->assertStringContainsString("public function toArray(): array", $file);
        $this->assertStringContainsString("'id' => \$this->id,", $file);
    }

    public function tearDown(): void
    {
        $this->filesystem->delete($this->path . 'ProductDTO.php');

        parent::tearDown();
    }
}