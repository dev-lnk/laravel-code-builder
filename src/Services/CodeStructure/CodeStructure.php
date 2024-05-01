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

    public function addColumn(ColumnStructure $column): void
    {
        if(in_array($column, $this->columns)) {
            return;
        }

        $this->columns[] = $column;

        $this->setTimestamps($column);

        if($column->type() == SqlTypeMap::BELONGS_TO->value) {
            $this->hasBelongsTo = true;
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

    public function columnsToModel(): string
    {
        $result = "";

        foreach ($this->columns as $column) {
            if(
                SqlTypeMap::from($column->type())->isIdType()
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
    public function belongsToInModel(): string
    {
        $result = str('');

        foreach ($this->columns as $column) {
            if(is_null($column->relation())
                || $column->type() !== SqlTypeMap::BELONGS_TO->value
            ) {
                continue;
            }

            $result = $result->newLine()->newLine()->append(
                StubBuilder::make($this->stubDir().'BelongsTo')
                    ->setKey(
                        '{relation_id}',
                        ", '{$column->relation()->foreignColumn()}'",
                        $column->relation()->foreignColumn() !== 'id'
                    )
                    ->getFromStub([
                        '{relation}' => $column->relation()->table()->str()->singular()->camel()->value(),
                        '{relation_model}' => $column->relation()->table()->ucFirstSingular(),
                        '{relation_column}' => $column->column()
                    ])
            );
        }

        return $result->value();
    }

    public function columnsToRules(): string
    {
        $result = "";

        foreach ($this->columns as $column) {
            if(in_array($column->column(), $this->dateColumns())) {
                continue;
            }

            $result .= str("'{$column->column()}' => ['{$column->rulesType()}', 'nullable']")
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
                || $column->isId()
            ) {
                continue;
            }

            $type = $column->inputType() !== 'text' ? " type=\"{$column->inputType()}\"" : '';

            $inputStub = 'Input';
            if($column->type() === SqlTypeMap::BELONGS_TO->value) {
                $inputStub = 'Select';
            }

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
}