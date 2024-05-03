<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodeStructure;

use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use DevLnk\LaravelCodeBuilder\Support\NameStr;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class CodeStructure
{
    /**
     * @var array<int, ColumnStructure>
     */
    private array $columns = [];

    private readonly NameStr $entity;

    private string $stubDir;

    private bool $isCreatedAt = false;

    private bool $isUpdatedAt = false;

    private bool $isDeletedAt = false;

    private bool $hasBelongsTo = false;

    private bool $hasHasMany = false;

    private bool $hasHasOne = false;

    private bool $hasBelongsToMany = false;

    public function __construct(
        private readonly string $table,
        string $entity
    ) {
        $this->entity = new NameStr(str($entity)->camel()->value());
    }

    public function setStubDir(string $stubDir): void
    {
        $this->stubDir = str_replace('//', '/', $stubDir);
    }

    public function stubDir(): string
    {
        return $this->stubDir;
    }

    public function table(): string
    {
        return $this->table;
    }

    public function entity(): NameStr
    {
        return $this->entity;
    }

    public function hasBelongsTo(): bool
    {
        return $this->hasBelongsTo;
    }

    public function hasHasMany(): bool
    {
        return $this->hasHasMany;
    }

    public function hasHasOne(): bool
    {
        return $this->hasHasOne;
    }

    public function hasBelongsToMany(): bool
    {
        return $this->hasBelongsToMany;
    }

    public function addColumn(ColumnStructure $column): void
    {
        if(in_array($column, $this->columns)) {
            return;
        }

        $this->columns[] = $column;

        $this->setTimestamps($column);

        if($column->type() === SqlTypeMap::BELONGS_TO) {
            $this->hasBelongsTo = true;
        }

        if($column->type() === SqlTypeMap::HAS_MANY) {
            $this->hasHasMany = true;
        }

        if($column->type() === SqlTypeMap::HAS_ONE) {
            $this->hasHasOne = true;
        }

        if($column->type() === SqlTypeMap::BELONGS_TO_MANY) {
            $this->hasBelongsToMany = true;
        }
    }

    private function setTimestamps(ColumnStructure $column): void
    {
        if(! $this->isCreatedAt && $column->isCreatedAt()) {
            $this->isCreatedAt = true;
            return;
        }

        if(! $this->isUpdatedAt && $column->isUpdatedAt()) {
            $this->isUpdatedAt = true;
            return;
        }

        if(! $this->isDeletedAt && $column->isDeletedAt()) {
            $this->isDeletedAt = true;
        }
    }

    /**
     * @return array<int, ColumnStructure>
     */
    public function columns(): array
    {
        return $this->columns();
    }

    public function isTimestamps(): bool
    {
        return $this->isCreatedAt && $this->isUpdatedAt;
    }

    public function isSoftDeletes(): bool
    {
        return $this->isDeletedAt;
    }

    /**
     * @return array<int, string>
     */
    public function dateColumns(): array
    {
        return [
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    /**
     * @return array<int, SqlTypeMap>
     */
    public function noInputType(): array
    {
        return [
            SqlTypeMap::HAS_MANY,
            SqlTypeMap::HAS_ONE,
        ];
    }

    /**
     * @return array<int, SqlTypeMap>
     */
    public function noFillableType(): array
    {
        return [
            SqlTypeMap::HAS_MANY,
            SqlTypeMap::HAS_ONE,
            SqlTypeMap::BELONGS_TO_MANY,
        ];
    }

    public function columnsToModel(): string
    {
        $result = "";

        foreach ($this->columns as $column) {
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

        foreach ($this->columns as $column) {
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

    public function columnsToRules(): string
    {
        $result = "";

        foreach ($this->columns as $column) {
            if(
                in_array($column->column(), $this->dateColumns())
                || in_array($column->type(), $this->noInputType())
            ) {
                continue;
            }

            $result .= str("'{$column->column()}' => ['{$column->rulesType()}'")
                ->when($column->type() === SqlTypeMap::BOOLEAN,
                    fn($str) => $str->append(", 'sometimes'"),
                    fn($str) => $str->append(", 'nullable'")
                )
                ->append(']')
                ->prepend("\t\t\t")
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
    public function columnsToForm(): string
    {
        $result = "";

        foreach ($this->columns as $column) {
            if(
                in_array($column->column(), $this->dateColumns())
                || in_array($column->type(), $this->noInputType())
                || $column->isId()
            ) {
                continue;
            }

            $type = $column->inputType() !== 'text' ? " type=\"{$column->inputType()}\"" : '';

            $inputStub = match ($column->type()) {
                SqlTypeMap::BELONGS_TO => 'InputSelect',
                SqlTypeMap::BELONGS_TO_MANY => 'InputMultiple',
                SqlTypeMap::BOOLEAN => 'InputBoolean',
                default => 'Input'
            };

            $input = StubBuilder::make($this->stubDir() . $inputStub)
                ->getFromStub([
                    '{column}' => $column->column(),
                    '{label}' => $column->column(),
                    '{type}' => $type
                ])
            ;

            $result .= str($input)
                ->prepend("\n")
            ;
        }

        return $result;
    }

    public function columnsToDTO(): string
    {
        $result = "";

        foreach ($this->sortColumnsFromDefaultValue() as $column) {
            $result .= str('private ')->prepend("\n\t\t")
                ->when($column->nullable(), fn($str) => $str->append('?'))
                ->append($column->phpType() . ' ')
                ->append('$' . str($column->column())->camel()->value())
                ->when(! is_null($column->default()) && ! $column->nullable(),
                    fn($str) => $str->append(' = ' . $column->defaultInStub())
                )
                ->when($column->nullable(), fn($str) => $str->append(' = null'))
                ->append(',')
            ;
        }
        return $result;
    }

    public function columnsToArrayDTO(): string
    {
        $result = "";

        foreach ($this->sortColumnsFromDefaultValue() as $column) {
            $result .= str(str($column->column())->camel()->value() . ': ')
                ->append("\$data['{$column->column()}']")
                ->when(! is_null($column->default()) && ! $column->nullable(),
                    fn($str) => $str->append(" ?? {$column->default()}")
                )
                ->when($column->nullable(), fn($str) => $str->append(" ?? null"))
                ->append(',')
                ->prepend("\n\t\t\t")
            ;
        }
        return $result;
    }

    public function columnsToRequestDTO(): string
    {
        $result = "";

        foreach ($this->sortColumnsFromDefaultValue() as $column) {
            if(
                in_array($column->column(), $this->dateColumns())
                || in_array($column->type(), $this->noInputType())
            ) {
                continue;
            }
            $result .= str(str($column->column())->camel()->value() . ': ')
                ->when($column->phpType() === 'int', fn($str) => $str->append('(int) '))
                ->append("\$request->")
                ->when($column->type() === SqlTypeMap::BOOLEAN,
                    fn($str) => $str->append('has'),
                    fn($str) => $str->append('input'),
                )
                ->append("('{$column->column()}'),")
                ->prepend("\n\t\t\t")
            ;
        }
        return $result;
    }

    public function columnsToModelDTO(): string
    {
        $result = "";

        foreach ($this->sortColumnsFromDefaultValue() as $column) {
            $result .= str(str($column->column())->camel()->value() . ': ')
                ->when($column->phpType() === 'int', fn($str) => $str->append('(int) '))
                ->when($column->phpType() === 'bool', fn($str) => $str->append('(bool) '))
                ->when($column->type() === SqlTypeMap::HAS_ONE,
                    fn($str) => $str->append("\$model->{$column->column()} ? ". $column->relation()?->table()->ucFirstSingular() . 'DTO::fromModel(')
                )
                ->append("\$model->{$column->column()}")
                ->when(in_array($column->column(), $this->dateColumns()),
                    fn($str) => $str->append("?->format('Y-m-d H:i:s')")
                )
                ->when($column->type() === SqlTypeMap::HAS_ONE,
                    fn($str) => $str->append(') : null')
                )
                ->when(($column->type() === SqlTypeMap::HAS_MANY || $column->type() === SqlTypeMap::BELONGS_TO_MANY),
                    fn($str) => $str->append('->toArray()')
                )
                ->append(',')
                ->prepend("\n\t\t\t")
            ;
        }
        return $result;
    }

    public function columnsToGettersDTO(): string
    {
        $result = "";

        foreach ($this->sortColumnsFromDefaultValue() as $column) {
            $columnCamel = str($column->column())->camel()->value();

            $result .= str("public function $columnCamel(): ")
                ->prepend("\n\t")
                ->when($column->nullable(), fn($str) => $str->append('?'))
                ->append("{$column->phpType()}")
                ->newLine()
                ->append("\t{\n\t\treturn \$this->$columnCamel;")
                ->newLine()
                ->append("\t}\n")
            ;
        }
        return $result;
    }

    public function columnsFromArrayDTO(): string
    {
        $result = "";
        foreach ($this->sortColumnsFromDefaultValue() as $column) {
            $columnCamel = str($column->column())->camel()->value();
            $result .= str("'{$column->column()}' => \$this->$columnCamel,")
                ->prepend("\n\t\t\t")
            ;
        }
        return $result;
    }

    /**
     * @return array<int, ColumnStructure>
     */
    private function sortColumnsFromDefaultValue(): array
    {
        $columns = [];
        foreach ($this->columns as $column) {
            if(is_null($column->default())
                && ! $column->nullable()
                && ! in_array($column->column(), $this->dateColumns())
            ) {
                $columns[] = $column;
            }
        }
        foreach ($this->columns as $column) {
            if(
                (! is_null($column->default()) || $column->nullable())
                && ! in_array($column->column(), $this->dateColumns())
            ) {
                $columns[] = $column;
            }
        }
        foreach ($this->columns as $column) {
            if(in_array($column->column(), $this->dateColumns())) {
                $columns[] = $column;
            }
        }
        return $columns;
    }
}