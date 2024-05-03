<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodeStructure;

use DevLnk\LaravelCodeBuilder\Support\NameStr;

final class RelationStructure
{
    private NameStr $table;

    public function __construct(
        private readonly string $foreignColumn,
        string $table
    ) {
        $this->table = new NameStr($table);
    }

    public function foreignColumn(): string
    {
        return $this->foreignColumn;
    }

    public function table(): NameStr
    {
        return $this->table;
    }
}
