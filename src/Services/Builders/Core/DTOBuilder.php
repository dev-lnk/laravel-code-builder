<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Core;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use DevLnk\LaravelCodeBuilder\Services\Builders\AbstractBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\DTOBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\ColumnStructure;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class DTOBuilder extends AbstractBuilder implements DTOBuilderContract
{
    /**
     * @var null|array<int, ColumnStructure>
     */
    private ?array $sortDTOColumns = null;

    /**
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        $dtoPath = $this->codePath->path(BuildType::DTO->value);
        $modelPath = $this->codePath->path(BuildType::MODEL->value);
        $requestPath = $this->codePath->path(BuildType::REQUEST->value);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($dtoPath->file(), [
                '{namespace}' => $dtoPath->namespace(),
                '{use_model}' => $modelPath->namespace() . '\\' . $modelPath->rawName(),
                '{use_request}' => $requestPath->namespace() . '\\' . $requestPath->rawName(),
                '{name}' => $dtoPath->rawName(),
                '{properties}' => $this->columnsToDTO(),
                '{array_inputs}' => $this->columnsToArrayDTO(),
                '{request_name}' => $requestPath->rawName(),
                '{request_inputs}' => $this->columnsToRequestDTO(),
                '{model_name}' => $modelPath->rawName(),
                '{model_inputs}' => $this->columnsToModelDTO(),
                '{getters}' => $this->columnsToGettersDTO(),
                '{to_array}' => $this->columnsFromArrayDTO(),
            ]);
    }

    public function columnsToDTO(): string
    {
        $result = "";

        foreach ($this->sortColumnsFromDefaultValue() as $column) {
            $result .= str('private ')->prepend("\n\t\t")
                ->when($column->nullable(), fn ($str) => $str->append('?'))
                ->append($column->phpType() . ' ')
                ->append('$' . str($column->column())->camel()->value())
                ->when(
                    ! is_null($column->default()) && ! $column->nullable(),
                    fn ($str) => $str->append(' = ' . $column->defaultInStub())
                )
                ->when($column->nullable(), fn ($str) => $str->append(' = null'))
                ->append(',')
            ;
        }

        return $result;
    }

    public function columnsToArrayDTO(): string
    {
        $result = "";

        foreach ($this->sortColumnsFromDefaultValue() as $column) {
            $result .= str(str($column->column())->camel()->value() . ': ')
                ->append("\$data['{$column->column()}']")
                ->when(
                    ! is_null($column->default()) && ! $column->nullable(),
                    fn ($str) => $str->append(" ?? {$column->defaultInStub()}")
                )
                ->when($column->nullable(), fn ($str) => $str->append(" ?? null"))
                ->append(',')
                ->prepend("\n\t\t\t")
            ;
        }

        return $result;
    }

    public function columnsToRequestDTO(): string
    {
        $result = "";

        foreach ($this->sortColumnsFromDefaultValue() as $column) {
            if(
                in_array($column->column(), $this->codeStructure->dateColumns())
                || in_array($column->type(), $this->codeStructure->noInputType())
            ) {
                continue;
            }
            $result .= str(str($column->column())->camel()->value() . ': ')
                ->when($column->phpType() === 'int', fn ($str) => $str->append('(int) '))
                ->when($column->phpType() === 'float', fn ($str) => $str->append('(float) '))
                ->append("\$request->")
                ->when(
                    $column->type() === SqlTypeMap::BOOLEAN,
                    fn ($str) => $str->append('has'),
                    fn ($str) => $str->append('input'),
                )
                ->append("('{$column->column()}'),")
                ->prepend("\n\t\t\t")
            ;
        }

        return $result;
    }

    public function columnsToModelDTO(): string
    {
        $result = "";

        foreach ($this->sortColumnsFromDefaultValue() as $column) {
            $result .= str(str($column->column())->camel()->value() . ': ')
                ->when($column->phpType() === 'int', fn ($str) => $str->append('(int) '))
                ->when($column->phpType() === 'bool', fn ($str) => $str->append('(bool) '))
                ->when($column->phpType() === 'float', fn ($str) => $str->append('(float) '))
                ->when(
                    $column->type() === SqlTypeMap::HAS_ONE,
                    fn ($str) => $str->append("\$model->{$column->column()} ? " . $column->relation()?->table()->ucFirstSingular() . 'DTO::fromModel(')
                )
                ->append("\$model->{$column->column()}")
                ->when(
                    in_array($column->column(), $this->codeStructure->dateColumns()),
                    fn ($str) => $str->append("?->format('Y-m-d H:i:s')")
                )
                ->when(
                    $column->type() === SqlTypeMap::HAS_ONE,
                    fn ($str) => $str->append(') : null')
                )
                ->when(
                    ($column->type() === SqlTypeMap::HAS_MANY || $column->type() === SqlTypeMap::BELONGS_TO_MANY),
                    fn ($str) => $str->append('->toArray()')
                )
                ->append(',')
                ->prepend("\n\t\t\t")
            ;
        }

        return $result;
    }

    public function columnsToGettersDTO(): string
    {
        $result = "";

        foreach ($this->sortColumnsFromDefaultValue() as $column) {
            $columnCamel = str($column->column())->camel()->value();

            $result .= str("public function $columnCamel(): ")
                ->prepend("\n\t")
                ->when($column->nullable(), fn ($str) => $str->append('?'))
                ->append("{$column->phpType()}")
                ->newLine()
                ->append("\t{\n\t\treturn \$this->$columnCamel;")
                ->newLine()
                ->append("\t}\n")
            ;
        }

        return $result;
    }

    public function columnsFromArrayDTO(): string
    {
        $result = "";
        foreach ($this->sortColumnsFromDefaultValue() as $column) {
            $columnCamel = str($column->column())->camel()->value();
            $result .= str("'{$column->column()}' => \$this->$columnCamel,")
                ->prepend("\n\t\t\t")
            ;
        }

        return $result;
    }

    /**
     * @return array<int, ColumnStructure>
     */
    private function sortColumnsFromDefaultValue(): array
    {
        if(is_array($this->sortDTOColumns)) {
            return $this->sortDTOColumns;
        }

        $searchColumns = $this->codeStructure->columns();

        foreach ($searchColumns as $key => $column) {
            if(is_null($column->default())
                && ! $column->nullable()
                && ! in_array($column->column(), $this->codeStructure->dateColumns())
            ) {
                $this->sortDTOColumns[] = $column;
                unset($searchColumns[$key]);
            }
        }

        foreach ($searchColumns as $key => $column) {
            if(
                (! is_null($column->default()) || $column->nullable())
                && ! in_array($column->column(), $this->codeStructure->dateColumns())
            ) {
                $this->sortDTOColumns[] = $column;
                unset($searchColumns[$key]);
            }
        }

        foreach ($searchColumns as $key => $column) {
            if(in_array($column->column(), $this->codeStructure->dateColumns())) {
                $this->sortDTOColumns[] = $column;
                unset($searchColumns[$key]);
            }
        }

        if(! empty($searchColumns)) {
            foreach ($searchColumns as $column) {
                $this->sortDTOColumns[] = $column;
            }
        }

        return $this->sortDTOColumns;
    }
}
