<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\SchemaStructure;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use Illuminate\Support\Facades\Schema;

final class SchemaFromMysql
{
    public static function make(
        string $table,
        bool $isBelongsTo,
        array $hasMany,
        array $hasOne,
        array $belongsToMany
    ): SchemaStructure {
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

        $schemeStructure = new SchemaStructure($table);

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

            $columnScheme = new SchemaColumn(
                $column['name'],
                $sqlType,
                $column['nullable'],
                $column['default'],
                $column['comment'],
            );

            if($isBelongsTo && isset($foreignList[$column['name']])) {
                $columnScheme->setForeign(new SchemaForeign(
                    $foreignList[$column['name']]['table'],
                    $foreignList[$column['name']]['foreign_column']
                ));
            }

            $schemeStructure->addColumn($columnScheme);
        }

        foreach ($hasMany as $column) {
            $columnScheme = new SchemaColumn(
                name: $column,
                type: SqlTypeMap::HAS_MANY,
                nullable: false,
                default: '[]',
                comment: '',
            );
            $columnScheme->setForeign(new SchemaForeign(
                $column,
                str($table)->singular()->snake()->value() . '_id'
            ));
            $schemeStructure->addColumn($columnScheme);
        }

        foreach ($hasOne as $column) {
            $columnScheme = new SchemaColumn(
                name: str($column)->singular()->snake()->value(),
                type: SqlTypeMap::HAS_ONE,
                nullable: true,
                default: null,
                comment: '',
            );
            $columnScheme->setForeign(new SchemaForeign(
                $column,
                str($table)->singular()->snake()->value() . '_id',
            ));
            $schemeStructure->addColumn($columnScheme);
        }

        foreach ($belongsToMany as $column) {
            $columnScheme = new SchemaColumn(
                name: $column,
                type: SqlTypeMap::BELONGS_TO_MANY,
                nullable: false,
                default: '[]',
                comment: '',
            );
            $columnScheme->setForeign(new SchemaForeign(
                $column,
                str($table)->singular()->snake()->value() . '_id',
            ));
            $schemeStructure->addColumn($columnScheme);
        }

        return $schemeStructure;
    }
}