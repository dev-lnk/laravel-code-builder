<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\SchemaStructure;

final class SchemaForeign
{
    public function __construct(
        private string $table,
        private string $column,
    ) {

    }

    public function table(): string
    {
        return $this->table;
    }

    public function column(): string
    {
        return $this->column;
    }
}