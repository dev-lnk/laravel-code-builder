<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodeStructure;

use DevLnk\LaravelCodeBuilder\Services\SchemaStructure\SchemaStructure;

final class CodeStructureFactory
{
    public static function make(string $entity, SchemaStructure $schemeStructure): CodeStructure
    {
        $codeStructure = new CodeStructure($schemeStructure->table(), $entity);

        $codeStructure->setData($schemeStructure->data());

        foreach ($schemeStructure->columns() as $column) {
            $columnStructure = new ColumnStructure(
                $column->name(),
                $column->comment() ?? '',
                $column->type(),
                $column->default(),
                $column->nullable()
            );

            if($column->foreign()) {
                $columnStructure->setRelation(
                    $column->foreign()->column(),
                    $column->foreign()->table()
                );
            }

            $columnStructure->setData($column->data());

            $codeStructure->addColumn($columnStructure);
        }

        return $codeStructure;
    }
}
