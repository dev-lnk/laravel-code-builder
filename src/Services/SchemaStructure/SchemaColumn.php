<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\SchemaStructure;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use DevLnk\LaravelCodeBuilder\Support\Traits\DataTrait;

final class SchemaColumn
{
    use DataTrait;

    public function __construct(
        private string $name,
        private SqlTypeMap $type,
        private bool $nullable,
        private ?string $default,
        private ?string $comment,
        private ?SchemaForeign $foreign = null
    ) {

    }

    public function setForeign(SchemaForeign $foreign): void
    {
        $this->foreign = $foreign;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): SqlTypeMap
    {
        return $this->type;
    }

    public function nullable(): bool
    {
        return $this->nullable;
    }

    public function default(): ?string
    {
        return $this->default;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function foreign(): ?SchemaForeign
    {
        return $this->foreign;
    }
}