<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodeStructure;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;

final class ColumnStructure
{
    private SqlTypeMap $type;

    private ?string $inputType = null;

    private ?RelationStructure $relation = null;

    public function __construct(
        private readonly string $column,
        private string $name = ''
    ) {
        if(empty($this->name)) {
            $this->name = str($this->column)->camel()->ucfirst()->value();
        }
    }

    public function type(): SqlTypeMap
    {
        return $this->type;
    }

    public function column(): string
    {
        return $this->column;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function relation(): ?RelationStructure
    {
        return $this->relation;
    }

    public function setType(SqlTypeMap $type): void
    {
        $this->type = $type;

        $this->setInputType();
    }

    public function setRelation(string $foreignColumn, string $table): void
    {
        $this->relation = new RelationStructure($foreignColumn, $table);
    }

    public function inputType(): ?string
    {
        return $this->inputType;
    }

    public function isCreatedAt(): bool
    {
        return $this->column() === 'created_at';
    }

    public function isUpdatedAt(): bool
    {
        return $this->column() === 'updated_at';
    }

    public function isDeletedAt(): bool
    {
        return $this->column() === 'deleted_at';
    }

    public function isId(): bool
    {
        return  $this->type()->isIdType();
    }

    public function isLaravelTimestamp(): bool
    {
        return $this->isCreatedAt() || $this->isUpdatedAt() || $this->isDeletedAt();
    }

    public function rulesType(): ?string
    {
        if($this->inputType === 'number') {
            return 'int';
        }

        if($this->inputType === 'number') {
            return 'int';
        }

        if($this->inputType === 'text') {
            return 'string';
        }

        return $this->inputType;
    }

    public function setInputType(): void
    {
        if(! is_null($this->inputType)) {
            return;
        }

        if($this->column === 'email' || $this->column === 'password') {
            $this->inputType = $this->column;
            return;
        }

        $this->inputType = $this->type()->getInputType();
    }
}