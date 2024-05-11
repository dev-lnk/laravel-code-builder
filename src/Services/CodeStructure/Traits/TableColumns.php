<?php

namespace DevLnk\LaravelCodeBuilder\Services\CodeStructure\Traits;

trait TableColumns
{
    public function columnsToThead(): string
    {
        $result = "";
        foreach ($this->columns() as $column) {
            $result .= str('')
                ->newLine()
                ->append("\t\t\t")
                ->append("<th>{$column->name()}</th>")
                ->value()
            ;
        }
        return $result;
    }

    public function columnsToTbody(): string
    {
        $result = "";
        foreach ($this->columns() as $column) {
            $result .= str('')
                ->newLine()
                ->append("\t\t\t")
                ->append("<td>{{ \${$this->entity()->singular()}->{$column->column()} }}</td>")
                ->value()
            ;
        }
        return $result;
    }
}