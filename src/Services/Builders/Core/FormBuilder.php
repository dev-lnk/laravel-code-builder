<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Core;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\Builders\AbstractBuilder;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

final class FormBuilder extends AbstractBuilder
{
    /**
     * @throws NotFoundCodePathException
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        $routePath = $this->codePath->path(BuildType::ROUTE->value);
        $formPath = $this->codePath->path(BuildType::FORM->value);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($formPath->file(), [
                '{name}' => $routePath->rawName(),
                '{inputs}' => $this->codeStructure->columnsToForm()
            ]);
    }
}