<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services;

use Closure;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

final class StubBuilder
{
    private string $stub;

    /**
     * @var array<string, string>
     */
    private array $replacers = [];

    /**
     * @var array<int, string>
     */
    private array $removers = [];

    /**
     * @throws FileNotFoundException
     */
    public static function make(string $stubPath): StubBuilder
    {
        return new StubBuilder($stubPath);
    }

    /**
     * @throws FileNotFoundException
     */
    public function __construct(
        string $stubPath
    ) {
        $this->stub = (new Filesystem())->get($stubPath . '.stub');
    }

    /**
     * @param string $destination
     * @param array<string, string> $replace
     *
     * @return void
     */
    public function makeFromStub(string $destination, array $replace = []): void
    {
        $this->removeKeys()->replaceKeys($this->replacers);

        (new Filesystem())->put(
            $destination,
            $this->replaceKeys($replace)
        );
    }

    /**
     * @param array<string, string> $replace
     *
     * @return string
     */
    public function getFromStub(array $replace = []): string
    {
        $this->removeKeys()->replaceKeys($this->replacers);

        return $this->replaceKeys($replace);
    }

    public function setKey(string $key, string $text, bool|Closure $isAdd = true): self
    {
        $isAdd = is_callable($isAdd) ? $isAdd() : $isAdd;
        $isAdd ? $this->addReplacer($key, $text) : $this->addRemover($key);

        return $this;
    }

    private function addRemover(string $name): void
    {
        $this->removers[] = $name;
    }

    private function addReplacer(string $replace, string $text): void
    {
        $this->replacers[$replace] = $text;
    }

    private function removeKeys(): self
    {
        $this->stub = str($this->stub)
            ->replace("\r", "")
            ->replace(array_map(fn ($item) => "\n\n$item", $this->removers), "")
            ->replace(array_map(fn ($item) => "\n$item", $this->removers), "")
            ->replace(array_map(fn ($item) => "$item", $this->removers), "")
            ->value();

        return $this;
    }

    /**
     * @param array<string, string> $replace
     *
     * @return string
     */
    private function replaceKeys(array $replace): string
    {
        $this->stub = str($this->stub)
            ->replace(array_keys($replace), array_values($replace))
            ->value();

        return $this->stub;
    }
}
