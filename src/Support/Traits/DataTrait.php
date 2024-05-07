<?php

namespace DevLnk\LaravelCodeBuilder\Support\Traits;

trait DataTrait
{
    /**
     * @var array<mixed, mixed>
     */
    private array $data = [];

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function setDataValue(mixed $key, mixed $value): void
    {
        if(isset($this->data[$key])) {
            return;
        }
        $this->data[$key] = $value;
    }

    /**
     * @return array<mixed, mixed>
     */
    public function data(): array
    {
        return $this->data;
    }

    public function dataValue(mixed $key): mixed
    {
        return $this->data[$key] ?? null;
    }
}
