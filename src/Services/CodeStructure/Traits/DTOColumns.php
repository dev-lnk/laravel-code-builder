<?php

namespace DevLnk\LaravelCodeBuilder\Services\CodeStructure\Traits;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\ColumnStructure;

trait DTOColumns
{
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
                in_array($column->column(), $this->dateColumns())
                || in_array($column->type(), $this->noInputType())
            ) {
                continue;
            }
            $result .= str(str($column->column())->camel()->value() . ': ')
                ->when($column->phpType() === 'int', fn ($str) => $str->append('(int) '))
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
                ->when(
                    $column->type() === SqlTypeMap::HAS_ONE,
                    fn ($str) => $str->append("\$model->{$column->column()} ? " . $column->relation()?->table()->ucFirstSingular() . 'DTO::fromModel(')
                )
                ->append("\$model->{$column->column()}")
                ->when(
                    in_array($column->column(), $this->dateColumns()),
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
        $columns = [];
        foreach ($this->columns() as $column) {
            if(is_null($column->default())
                && ! $column->nullable()
                && ! in_array($column->column(), $this->dateColumns())
            ) {
                $columns[] = $column;
            }
        }
        foreach ($this->columns() as $column) {
            if(
                (! is_null($column->default()) || $column->nullable())
                && ! in_array($column->column(), $this->dateColumns())
            ) {
                $columns[] = $column;
            }
        }
        foreach ($this->columns() as $column) {
            if(in_array($column->column(), $this->dateColumns())) {
                $columns[] = $column;
            }
        }

        return $columns;
    }
}
