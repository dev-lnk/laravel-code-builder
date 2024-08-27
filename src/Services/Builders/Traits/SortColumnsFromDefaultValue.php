<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Traits;

use DevLnk\LaravelCodeBuilder\Services\CodeStructure\ColumnStructure;

trait SortColumnsFromDefaultValue
{
    /**
     * @var null|array<int, ColumnStructure>
     */
    private ?array $sortColumns = null;

    /**
     * @return array<int, ColumnStructure>
     */
    private function sortColumnsFromDefaultValue(): array
    {
        if(is_array($this->sortColumns)) {
            return $this->sortColumns;
        }

        $searchColumns = $this->codeStructure->columns();

        foreach ($searchColumns as $key => $column) {
            if(is_null($column->default())
                && ! $column->nullable()
                && ! in_array($column->column(), $this->codeStructure->dateColumns())
            ) {
                $this->sortColumns[] = $column;
                unset($searchColumns[$key]);
            }
        }

        foreach ($searchColumns as $key => $column) {
            if(
                (! is_null($column->default()) || $column->nullable())
                && ! in_array($column->column(), $this->codeStructure->dateColumns())
            ) {
                $this->sortColumns[] = $column;
                unset($searchColumns[$key]);
            }
        }

        foreach ($searchColumns as $key => $column) {
            if(in_array($column->column(), $this->codeStructure->dateColumns())) {
                $this->sortColumns[] = $column;
                unset($searchColumns[$key]);
            }
        }

        if(! empty($searchColumns)) {
            foreach ($searchColumns as $column) {
                $this->sortColumns[] = $column;
            }
        }

        return $this->sortColumns;
    }
}
