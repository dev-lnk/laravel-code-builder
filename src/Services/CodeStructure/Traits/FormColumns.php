<?php

namespace DevLnk\LaravelCodeBuilder\Services\CodeStructure\Traits;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

trait FormColumns
{
    /**
     * @throws FileNotFoundException
     */
    public function columnsToForm(): string
    {
        $result = "";

        foreach ($this->columns() as $column) {
            if(
                in_array($column->column(), $this->dateColumns())
                || in_array($column->type(), $this->noInputType())
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

            $input = StubBuilder::make($this->stubDir() . $inputStub)
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
