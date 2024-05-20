<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Core;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use DevLnk\LaravelCodeBuilder\Enums\StubValue;
use DevLnk\LaravelCodeBuilder\Services\Builders\AbstractBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\ModelBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ModelBuilder extends AbstractBuilder implements ModelBuilderContract
{
    /**
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        $modelPath = $this->codePath->path(BuildType::MODEL->value);

        $relations = $this->relationsToModel();

        StubBuilder::make($this->stubFile)
            ->setKey(
                StubValue::USE_SOFT_DELETES->key(),
                StubValue::USE_SOFT_DELETES->value(),
                $this->codeStructure->isSoftDeletes()
            )
            ->setKey(
                StubValue::SOFT_DELETES->key(),
                StubValue::SOFT_DELETES->value() . PHP_EOL,
                $this->codeStructure->isSoftDeletes()
            )
            ->setKey(
                StubValue::USE_BELONGS_TO->key(),
                StubValue::USE_BELONGS_TO->value(),
                $this->codeStructure->hasBelongsTo()
            )
            ->setKey(
                StubValue::USE_HAS_MANY->key(),
                StubValue::USE_HAS_MANY->value(),
                $this->codeStructure->hasHasMany()
            )
            ->setKey(
                StubValue::USE_HAS_ONE->key(),
                StubValue::USE_HAS_ONE->value(),
                $this->codeStructure->hasHasOne()
            )
            ->setKey(
                StubValue::USE_BELONGS_TO_MANY->key(),
                StubValue::USE_BELONGS_TO_MANY->value(),
                $this->codeStructure->hasBelongsToMany()
            )
            ->setKey(
                StubValue::RELATIONS->key(),
                $relations,
                ! empty($relations)
            )
            ->setKey(
                StubValue::TIMESTAMPS->key(),
                StubValue::TIMESTAMPS->value() . PHP_EOL,
                ! $this->codeStructure->isTimestamps()
            )
            ->setKey(
                StubValue::TABLE->key(),
                StubValue::TABLE->value() . " '{$this->codeStructure->table()}';\n",
                $this->codeStructure->table() !== $this->codeStructure->entity()->str()->plural()->snake()->value()
            )
            ->makeFromStub($modelPath->file(), [
                '{namespace}' => $modelPath->namespace(),
                '{class}' => $this->codeStructure->entity()->ucFirstSingular(),
                '{fillable}' => $this->columnsToModel(),
            ])
        ;
    }

    public function columnsToModel(): string
    {
        $result = "";

        foreach ($this->codeStructure->columns() as $column) {
            if(
                $column->type()->isIdType()
                || in_array($column->type(), $this->codeStructure->noFillableType())
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

        foreach ($this->codeStructure->columns() as $column) {
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

            $stubBuilder = StubBuilder::make($this->codeStructure->stubDir() . $stubName);
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
                    '{relation_column}' => $relationColumn,
                ])
            );
        }

        return $result->value();
    }
}
