<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodeStructure;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\Traits\DTOColumns;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\Traits\FormColumns;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\Traits\ModelColumns;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\Traits\RequestColumns;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\Traits\TableColumns;
use DevLnk\LaravelCodeBuilder\Support\NameStr;
use DevLnk\LaravelCodeBuilder\Support\Traits\DataTrait;

class CodeStructure
{
    use ModelColumns;
    use RequestColumns;
    use FormColumns;
    use DTOColumns;
    use TableColumns;
    use DataTrait;

    /**
     * @var array<int, ColumnStructure>
     */
    private array $columns = [];

    private readonly NameStr $entity;

    private string $stubDir;

    private bool $isCreatedAt = false;

    private bool $isUpdatedAt = false;

    private bool $isDeletedAt = false;

    private bool $hasBelongsTo = false;

    private bool $hasHasMany = false;

    private bool $hasHasOne = false;

    private bool $hasBelongsToMany = false;

    public function __construct(
        private readonly string $table,
        string $entity
    ) {
        $this->entity = new NameStr(str($entity)->camel()->value());
    }

    public function setStubDir(string $stubDir): void
    {
        $this->stubDir = str_replace('//', '/', $stubDir);
    }

    /**
     * @return  array<int, ColumnStructure>
     */
    public function columns(): array
    {
        return $this->columns;
    }

    public function stubDir(): string
    {
        return $this->stubDir;
    }

    public function table(): string
    {
        return $this->table;
    }

    public function entity(): NameStr
    {
        return $this->entity;
    }

    public function hasBelongsTo(): bool
    {
        return $this->hasBelongsTo;
    }

    public function hasHasMany(): bool
    {
        return $this->hasHasMany;
    }

    public function hasHasOne(): bool
    {
        return $this->hasHasOne;
    }

    public function hasBelongsToMany(): bool
    {
        return $this->hasBelongsToMany;
    }

    public function addColumn(ColumnStructure $column): void
    {
        if(in_array($column, $this->columns)) {
            return;
        }

        $this->columns[] = $column;

        $this->setTimestamps($column);

        if($column->type() === SqlTypeMap::BELONGS_TO) {
            $this->hasBelongsTo = true;
        }

        if($column->type() === SqlTypeMap::HAS_MANY) {
            $this->hasHasMany = true;
        }

        if($column->type() === SqlTypeMap::HAS_ONE) {
            $this->hasHasOne = true;
        }

        if($column->type() === SqlTypeMap::BELONGS_TO_MANY) {
            $this->hasBelongsToMany = true;
        }
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

    public function isTimestamps(): bool
    {
        return $this->isCreatedAt && $this->isUpdatedAt;
    }

    public function isSoftDeletes(): bool
    {
        return $this->isDeletedAt;
    }

    /**
     * @return array<int, string>
     */
    public function dateColumns(): array
    {
        return [
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    /**
     * @return array<int, SqlTypeMap>
     */
    public function noInputType(): array
    {
        return [
            SqlTypeMap::HAS_MANY,
            SqlTypeMap::HAS_ONE,
        ];
    }

    /**
     * @return array<int, SqlTypeMap>
     */
    public function noFillableType(): array
    {
        return [
            SqlTypeMap::HAS_MANY,
            SqlTypeMap::HAS_ONE,
            SqlTypeMap::BELONGS_TO_MANY,
        ];
    }
}
