<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Support;

use Illuminate\Support\Stringable;

final readonly class NameStr
{
    public function __construct(
        private string $name
    ) {

    }

    public function raw(): string
    {
        return $this->name;
    }

    public function str(): Stringable
    {
        return str($this->name);
    }

    public function ucFirst(): string
    {
        return str($this->raw())->ucfirst()->value();
    }

    public function ucFirstSingular(): string
    {
        return str($this->raw())->singular()->ucfirst()->value();
    }

    public function lower(): string
    {
        return str($this->raw())
            ->snake()
            ->lower()
            ->value();
    }

    public function camel(): string
    {
        return str($this->raw())
            ->camel()
            ->value();
    }

    public function plural(): string
    {
        return str($this->camel())->plural()->value();
    }

    public function singular(): string
    {
        return str($this->camel())->singular()->value();
    }
}
