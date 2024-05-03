<?php

namespace DevLnk\LaravelCodeBuilder\Services\CodeStructure\Traits;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;

trait RequestColumns
{
    public function columnsToRules(): string
    {
        $result = "";

        foreach ($this->columns() as $column) {
            if(
                in_array($column->column(), $this->dateColumns())
                || in_array($column->type(), $this->noInputType())
            ) {
                continue;
            }

            $result .= str("'{$column->column()}' => ['{$column->rulesType()}'")
                ->when($column->type() === SqlTypeMap::BOOLEAN,
                    fn($str) => $str->append(", 'sometimes'"),
                    fn($str) => $str->append(", 'nullable'")
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