<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\CodeBuilder;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;

final class ColumnStructure
{
    private ?string $type = null;

    private ?string $inputType = null;

    public function __construct(
        private readonly string $column,
        private string $name = ''
    ) {
        if(empty($this->name)) {
            $this->name = str($this->column)->camel()->ucfirst()->value();
        }
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function column(): ?string
    {
        return $this->column;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function setType(string $type): void
    {
        $this->type = $type;

        $this->setInputType();
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

    public function isLaravelTimestamp(): bool
    {
        return $this->isCreatedAt() || $this->isUpdatedAt() || $this->isDeletedAt();
    }

    public function rulesType(): ?string
    {
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

        $this->inputType = SqlTypeMap::from($this->type())->getInputType();
    }
}