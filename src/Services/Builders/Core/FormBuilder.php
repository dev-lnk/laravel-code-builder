<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Core;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\Builders\AbstractBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\FormBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

final class FormBuilder extends AbstractBuilder implements FormBuilderContract
{
    /**
     * @throws NotFoundCodePathException
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        $formPath = $this->codePath->path(BuildType::FORM->value);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($formPath->file(), [
                '{entity_plural}' => $this->codeStructure->entity()->plural(),
                '{inputs}' => $this->codeStructure->columnsToForm(),
            ]);
    }
}
