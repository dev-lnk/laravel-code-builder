<?php

namespace DevLnk\LaravelCodeBuilder\Services\CodeStructure\Traits;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

trait ModelColumns
{
    public function columnsToModel(): string
    {
        $result = "";

        foreach ($this->columns() as $column) {
            if(
                $column->type()->isIdType()
                || in_array($column->type(), $this->noFillableType())
                || $column->isLaravelTimestamp()
            ) {
                continue;
            }

            $result .= str("'{$column->column()}'")
                ->prepend("\t\t")
                ->prepend(PHP_EOL)
                ->append(',')
                ->value()
            ;
        }

        return $result;
    }

    /**
     * @throws FileNotFoundException
     */
    public function relationsToModel(): string
    {
        $result = str('');

        foreach ($this->columns() as $column) {
            if(is_null($column->relation())) {
                continue;
            }

            $stubName = match ($column->type()) {
                SqlTypeMap::BELONGS_TO => 'BelongsTo',
                SqlTypeMap::HAS_MANY => 'HasMany',
                SqlTypeMap::HAS_ONE => 'HasOne',
                SqlTypeMap::BELONGS_TO_MANY => 'BelongsToMany',
                default => ''
            };

            if(empty($stubName)) {
                continue;
            }

            $stubBuilder = StubBuilder::make($this->stubDir() . $stubName);
            if($column->type() === SqlTypeMap::BELONGS_TO) {
                $stubBuilder->setKey(
                    '{relation_id}',
                    ", '{$column->relation()->foreignColumn()}'",
                    $column->relation()->foreignColumn() !== 'id'
                );
            }

            $relation = $column->relation()->table()->str();

            $relation = ($column->type() === SqlTypeMap::HAS_MANY || $column->type() === SqlTypeMap::BELONGS_TO_MANY)
                ? $relation->plural()->camel()->value()
                : $relation->singular()->camel()->value();

            $relationColumn = ($column->type() === SqlTypeMap::HAS_MANY || $column->type() === SqlTypeMap::HAS_ONE)
                ? $column->relation()->foreignColumn()
                : $column->column();

            $result = $result->newLine()->newLine()->append(
                $stubBuilder->getFromStub([
                    '{relation}' => $relation,
                    '{relation_model}' => $column->relation()->table()->ucFirstSingular(),
                    '{relation_column}' => $relationColumn
                ])
            );
        }

        return $result->value();
    }
}