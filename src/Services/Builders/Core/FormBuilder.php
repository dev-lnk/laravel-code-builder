<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Core;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use DevLnk\LaravelCodeBuilder\Services\Builders\AbstractBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\FormBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

final class FormBuilder extends AbstractBuilder implements FormBuilderContract
{
    /**
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        $formPath = $this->codePath->path(BuildType::FORM->value);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($formPath->file(), [
                '{entity_plural}' => $this->codeStructure->entity()->plural(),
                '{inputs}' => $this->columnsToForm(),
            ]);
    }

    /**
     * @throws FileNotFoundException
     */
    public function columnsToForm(): string
    {
        $result = "";

        foreach ($this->codeStructure->columns() as $column) {
            if(
                in_array($column->column(), $this->codeStructure->dateColumns())
                || in_array($column->type(), $this->codeStructure->noInputType())
                || $column->isId()
            ) {
                continue;
            }

            $type = $column->inputType() !== 'text' ? " type=\"{$column->inputType()}\"" : '';

            $inputStub = match ($column->type()) {
                SqlTypeMap::BELONGS_TO => 'InputSelect',
                SqlTypeMap::BELONGS_TO_MANY => 'InputMultiple',
                SqlTypeMap::BOOLEAN => 'InputBoolean',
                default => 'Input'
            };

            $input = StubBuilder::make($this->codeStructure->stubDir() . $inputStub)
                ->getFromStub([
                    '{column}' => $column->column(),
                    '{label}' => $column->column(),
                    '{type}' => $type,
                ])
            ;

            $result .= str($input)
                ->prepend("\n")
            ;
        }

        return $result;
    }
}
