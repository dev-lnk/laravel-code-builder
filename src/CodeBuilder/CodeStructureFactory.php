<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\CodeBuilder;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use Illuminate\Support\Facades\Schema;

final class CodeStructureFactory
{
    public static function makeFromTable(string $table, string $entity): CodeStructure
    {
        $columns = Schema::getColumns($table);

        $indexes = Schema::getIndexes($table);

        $codeStructure = new CodeStructure($table, $entity);

        $primaryKey = 'id';
        foreach ($indexes as $index) {
            if($index['name'] === 'primary') {
                $primaryKey = $index['columns'][0];
                break;
            }
        }

        foreach ($columns as $column) {
            $columnStructure = new ColumnStructure($column['name'], $column['comment'] ?? '');

            $type = $column['name'] === $primaryKey
                ? 'primary'
                : preg_replace("/[0-9]+|\(|\)|,/", '', $column['type']);

            $columnStructure->setType(SqlTypeMap::fromSqlType($type)->value);

            $codeStructure->addColumn($columnStructure);
        }

        return $codeStructure;
    }
}