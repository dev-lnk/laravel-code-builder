<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodeStructure;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use Illuminate\Support\Facades\Schema;

final class CodeStructureFactory
{
    public static function makeFromTable(string $table, string $entity, bool $isBelongsTo): CodeStructure
    {
        $columns = Schema::getColumns($table);

        $indexes = Schema::getIndexes($table);

        $foreignKeys = $isBelongsTo ? Schema::getForeignKeys($table) : [];
        $foreigns = [];
        foreach ($foreignKeys as $value) {
            $foreigns[$value['columns'][0]] = [
                'table' => $value['foreign_table'],
                'foreign_column' => $value['foreign_columns'][0],
            ];
        }

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

            if($isBelongsTo && isset($foreigns[$column['name']])) {
                $columnStructure->setRelation(
                    $foreigns[$column['name']]['foreign_column'],
                    $foreigns[$column['name']]['table']
                );
                $columnStructure->setType(SqlTypeMap::BELONGS_TO->value);
            } else {
                $columnStructure->setType(SqlTypeMap::fromSqlType($type)->value);
            }

            $codeStructure->addColumn($columnStructure);
        }

        return $codeStructure;
    }
}