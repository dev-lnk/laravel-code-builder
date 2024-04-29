<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodeStructure;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use DevLnk\LaravelCodeBuilder\Support\NameStr;

class CodeStructure
{
    /**
     * @var array<int, ColumnStructure>
     */
    private array $columns = [];

    private readonly NameStr $entity;

    private bool $isCreatedAt = false;

    private bool $isUpdatedAt = false;

    private bool $isDeletedAt = false;

    public function __construct(
        private readonly string $table,
        string $entity
    ) {
        $this->entity = new NameStr(str($entity)->camel()->value());
    }

    public function table(): string
    {
        return $this->table;
    }

    public function entity(): NameStr
    {
        return $this->entity;
    }

    public function addColumn(ColumnStructure $column): void
    {
        if(in_array($column, $this->columns)) {
            return;
        }

        $this->columns[] = $column;

        $this->setTimestamps($column);
    }

    private function setTimestamps(ColumnStructure $column): void
    {
        if(! $this->isCreatedAt && $column->isCreatedAt()) {
            $this->isCreatedAt = true;
            return;
        }

        if(! $this->isUpdatedAt && $column->isUpdatedAt()) {
            $this->isUpdatedAt = true;
            return;
        }

        if(! $this->isDeletedAt && $column->isDeletedAt()) {
            $this->isDeletedAt = true;
        }
    }

    public function columns(): array
    {
        return $this->columns();
    }

    public function dateColumns(): array
    {
        return [
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    public function isTimestamps(): bool
    {
        return $this->isCreatedAt && $this->isUpdatedAt;
    }

    public function isSoftDeletes(): bool
    {
        return $this->isDeletedAt;
    }

    public function columnsToModel(): string
    {
        $result = "";

        foreach ($this->columns as $column) {
            if(
                SqlTypeMap::from($column->type())->isIdType()
                || $column->isLaravelTimestamp()
            ) {
                continue;
            }

            $result .= str("'{$column->column()}'")
                ->prepend("\t\t")
                ->prepend(PHP_EOL)
                ->append(',')
                ->value()
            ;
        }

        return $result;
    }
}