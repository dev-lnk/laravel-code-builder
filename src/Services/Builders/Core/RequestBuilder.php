<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Core;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use DevLnk\LaravelCodeBuilder\Services\Builders\AbstractBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\RequestBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

final class RequestBuilder extends AbstractBuilder implements RequestBuilderContract
{
    /**
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        $requestPath = $this->codePath->path(BuildType::REQUEST->value);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($requestPath->file(), [
                '{namespace}' => $requestPath->namespace(),
                '{name}' => $requestPath->rawName(),
                '{rules}' => $this->columnsToRules(),
            ]);
    }

    public function columnsToRules(): string
    {
        $result = "";

        foreach ($this->codeStructure->columns() as $column) {
            if(
                in_array($column->column(), $this->codeStructure->dateColumns())
                || in_array($column->type(), $this->codeStructure->noInputType())
            ) {
                continue;
            }

            $result .= str("'{$column->column()}' => ['{$column->rulesType()}'")
                ->when(
                    $column->type() === SqlTypeMap::BOOLEAN,
                    fn ($str) => $str->append(", 'sometimes'"),
                    fn ($str) => $str->append(", 'nullable'")
                )
                ->append(']')
                ->prepend("\t\t\t")
                ->prepend(PHP_EOL)
                ->append(',')
                ->value()
            ;
        }

        return $result;
    }
}
