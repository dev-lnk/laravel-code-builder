## DTO
```php
<?php

declare(strict_types=1);

namespace App\Generation\DTO;

use App\Generation\Models\Product;
use App\Generation\Http\Requests\ProductRequest;

readonly class ProductDTO
{
    public function __construct(
        private int $id,
        private string $content,
        private string $title = 'Default',
        private ?int $userId = null,
        private int $sortNumber = 0,
        private bool $isActive = false,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
        private ?string $deletedAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
       return new self(
            id: $data['id'],
            content: $data['content'],
            title: $data['title'] ?? 'Default',
            userId: $data['user_id'] ?? null,
            sortNumber: $data['sort_number'] ?? 0,
            isActive: $data['is_active'] ?? false,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
            deletedAt: $data['deleted_at'] ?? null,
       );
    }

    public static function fromRequest(ProductRequest $request): self
    {
        return new self(
            id: (int) $request->input('id'),
            content: $request->input('content'),
            title: $request->input('title'),
            userId: (int) $request->input('user_id'),
            sortNumber: (int) $request->input('sort_number'),
            isActive: $request->has('is_active'),
        );
    }

    public static function fromModel(Product $model): self
    {
        return new self(
            id: (int) $model->id,
            content: $model->content,
            title: $model->title,
            userId: (int) $model->user_id,
            sortNumber: (int) $model->sort_number,
            isActive: (bool) $model->is_active,
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
        );
    }

	public function id(): int
	{
		return $this->id;
	}

	public function content(): string
	{
		return $this->content;
	}

	public function title(): string
	{
		return $this->title;
	}

	public function userId(): ?int
	{
		return $this->userId;
	}

	public function sortNumber(): int
	{
		return $this->sortNumber;
	}

	public function isActive(): bool
	{
		return $this->isActive;
	}

	public function createdAt(): ?string
	{
		return $this->createdAt;
	}

	public function updatedAt(): ?string
	{
		return $this->updatedAt;
	}

	public function deletedAt(): ?string
	{
		return $this->deletedAt;
	}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'title' => $this->title,
            'user_id' => $this->userId,
            'sort_number' => $this->sortNumber,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt,
        ];
    }
}
```