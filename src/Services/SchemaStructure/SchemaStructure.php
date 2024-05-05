<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\SchemaStructure;

use DevLnk\LaravelCodeBuilder\Support\Traits\DataTrait;

final class SchemaStructure
{
    use DataTrait;

    /**
     * @var array<int, SchemaColumn>
     */
    private array $columns = [];

    public function __construct(
        private readonly string $table
    ) {
    }

    public function addColumn(SchemaColumn $column): void
    {
        $this->columns[] = $column;
    }

    /**
     * @return array<int, SchemaColumn>
     */
    public function columns(): array
    {
        return $this->columns;
    }

    public function table(): string
    {
        return $this->table;
    }
}