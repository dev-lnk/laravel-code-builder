<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders;

use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use DevLnk\LaravelCodeBuilder\Types\BuildType;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

final class FormBuilder extends AbstractBuilder
{
    /**
     * @throws NotFoundCodePathException
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        $routePath = $this->codePath->path(BuildType::ROUTE);
        $formPath = $this->codePath->path(BuildType::FORM);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($formPath->file(), [
                '{name}' => $routePath->rawName(),
                '{inputs}' => $this->codeStructure->columnsToForm()
            ]);
    }
}