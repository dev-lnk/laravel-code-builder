<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodeStructure\Factories;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructure;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\ColumnStructure;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\RelationStructure;
use Illuminate\Support\Facades\Schema;

final class CodeStructureFromMysql
{
    public static function make(
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

        $primaryKey = 'id';
        foreach ($indexes as $index) {
            if($index['name'] === 'primary') {
                $primaryKey = $index['columns'][0];
                break;
            }
        }

        $foreignList = [];
        foreach ($foreignKeys as $value) {
            $foreignList[$value['columns'][0]] = [
                'table' => $value['foreign_table'],
                'foreign_column' => $value['foreign_columns'][0],
            ];
        }

        $codeStructure = new CodeStructure($table, $entity);

        foreach ($columns as $column) {
            $type = $column['name'] === $primaryKey
                ? 'primary'
                : preg_replace("/[0-9]+|\(|\)|,/", '', $column['type']);

            // For pgsql
            if ($type === 'primary') {
                $column['default'] = null;
            }

            // For pgsql
            if (!is_null($column['default']) && str_contains($column['default'], '::')) {
                $column['default'] = substr(
                    $column['default'],
                    0,
                    strpos($column['default'], '::')
                );
            }

            // For mysql
            $type = $column['type'] === 'tinyint(1)' ? 'boolean' : $type;

            if ($type === 'boolean' && !is_null($column['default'])) {
                // For mysql
                if ($column['default'] !== 'false'
                    && $column['default'] !== true
                ) {
                    $column['default'] = $column['default'] ? 'true' : 'false';
                }
            }

            if (
                // For mysql
                $column['default'] === 'current_timestamp()'
                // For pgsql
                || $column['default'] === 'CURRENT_TIMESTAMP'
            ) {
                $column['default'] = '';
            }

            $sqlType = ($isBelongsTo && isset($foreignList[$column['name']]))
                ? SqlTypeMap::BELONGS_TO
                : SqlTypeMap::fromSqlType($type);

            $columnStructure = new ColumnStructure(
                column: $column['name'],
                name: $column['name'],
                type: $sqlType,
                default: $column['default'],
                nullable: $column['nullable'],
            );

            if($isBelongsTo && isset($foreignList[$column['name']])) {
                $columnStructure->setRelation(new RelationStructure(
                    $foreignList[$column['name']]['foreign_column'],
                    $foreignList[$column['name']]['table']
                ));
            }

            $codeStructure->addColumn($columnStructure);
        }

        foreach ($hasMany as $tableName) {
            $columnStructure = new ColumnStructure(
                column: $tableName,
                name: $tableName,
                type: SqlTypeMap::HAS_MANY,
                default: '[]',
                nullable: false,
            );
            $columnStructure->setRelation(new RelationStructure(
                str($table)->singular()->snake()->value() . '_id',
                $tableName
            ));
            $codeStructure->addColumn($columnStructure);
        }

        foreach ($hasOne as $tableName) {
            $columnStructure = new ColumnStructure(
                column: str($tableName)->singular()->snake()->value(),
                name: str($tableName)->singular()->snake()->value(),
                type: SqlTypeMap::HAS_ONE,
                default: null,
                nullable: true,
            );
            $columnStructure->setRelation(new RelationStructure(
                str($table)->singular()->snake()->value() . '_id',
                $tableName,
            ));
            $codeStructure->addColumn($columnStructure);
        }

        foreach ($belongsToMany as $tableName) {
            $columnStructure = new ColumnStructure(
                column: $tableName,
                name: $tableName,
                type: SqlTypeMap::BELONGS_TO_MANY,
                default: '[]',
                nullable: false,
            );
            $columnStructure->setRelation(new RelationStructure(
                str($table)->singular()->snake()->value() . '_id',
                $tableName,
            ));
            $codeStructure->addColumn($columnStructure);
        }

        return $codeStructure;
    }
}