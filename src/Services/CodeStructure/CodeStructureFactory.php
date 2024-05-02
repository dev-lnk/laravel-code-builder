<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodeStructure;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use DevLnk\LaravelCodeBuilder\Support\NameStr;
use Illuminate\Support\Facades\Schema;

final class CodeStructureFactory
{
    /**
     * @param string $table
     * @param string $entity
     * @param bool   $isBelongsTo
     * @param array<int, string>  $hasMany
     * @param array<int, string>  $hasOne
     * @param array<int, string>  $belongsToMany
     *
     * @return CodeStructure
     */
    public static function makeFromTable(
        string $table,
        string $entity,
        bool $isBelongsTo,
        array $hasMany,
        array $hasOne,
        array $belongsToMany
    ): CodeStructure {
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
            $type = $column['name'] === $primaryKey
                ? 'primary'
                : preg_replace("/[0-9]+|\(|\)|,/", '', $column['type']);

            $type = $column['type'] === 'tinyint(1)' ? 'boolean' : $type;
            if($type === 'boolean') {
                $column['default'] = $column['default'] ? 'true' : 'false';
            }

            $columnStructure = new ColumnStructure(
                $column['name'],
                $column['comment'] ?? '',
                $column['default'],
                $column['nullable']
            );

            if($isBelongsTo && isset($foreigns[$column['name']])) {
                $columnStructure->setRelation(
                    $foreigns[$column['name']]['foreign_column'],
                    $foreigns[$column['name']]['table']
                );
                $columnStructure->setType(SqlTypeMap::BELONGS_TO);
            } else {
                $columnStructure->setType(SqlTypeMap::fromSqlType($type));
            }

            $codeStructure->addColumn($columnStructure);
        }

        foreach ($hasMany as $column) {
            $columnStructure = new ColumnStructure($column, '', '[]', false);
            $columnStructure->setRelation(
                str($table)->singular()->snake()->value() . '_id',
                $column
            );
            $columnStructure->setType(SqlTypeMap::HAS_MANY);
            $codeStructure->addColumn($columnStructure);
        }

        foreach ($hasOne as $column) {
            $columnStructure = new ColumnStructure(str($column)->singular()->snake()->value(), '', NULL, true);
            $columnStructure->setRelation(
                str($table)->singular()->snake()->value() . '_id',
                $column
            );
            $columnStructure->setType(SqlTypeMap::HAS_ONE);
            $codeStructure->addColumn($columnStructure);
        }

        foreach ($belongsToMany as $column) {
            $columnStructure = new ColumnStructure($column, '', '[]', false);
            $columnStructure->setRelation(
                str($table)->singular()->snake()->value() . '_id',
                $column
            );
            $columnStructure->setType(SqlTypeMap::BELONGS_TO_MANY);
            $codeStructure->addColumn($columnStructure);
        }

        return $codeStructure;
    }
}