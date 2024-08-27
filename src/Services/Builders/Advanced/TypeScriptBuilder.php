<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Advanced;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Enums\SqlTypeMap;
use DevLnk\LaravelCodeBuilder\Services\Builders\AbstractBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Advanced\Contracts\TypeScriptContract;
use DevLnk\LaravelCodeBuilder\Services\Builders\Traits\SortColumnsFromDefaultValue;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\ColumnStructure;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class TypeScriptBuilder extends AbstractBuilder implements TypeScriptContract
{
    use SortColumnsFromDefaultValue;

    /**
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        $tsPath = $this->codePath->path(BuildType::TYPE_SCRIPT->value);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($tsPath->file(), [
                '{name}' => str($tsPath->name())->replace('.ts', '')->singular()->ucfirst()->value(),
                '{properties}' => $this->columnsToTypeScript(),
            ]);
    }

    public function columnsToTypeScript(): string
    {
        $result = "";

        foreach ($this->sortColumnsFromDefaultValue() as $column) {
            $result .= str(str($column->column())->camel()->value())->prepend("\n\t")
                ->append(': ')
                ->append($this->getTsType($column))
                ->when($column->nullable(), fn ($str) => $str->append(' | null'))
            ;
        }

        return $result;
    }

    public function getTsType(ColumnStructure $column): string
    {
        return match ($column->type()) {
            /*Number*/
            SqlTypeMap::ID,
            SqlTypeMap::BIG_INCREMENTS,
            SqlTypeMap::MEDIUM_INCREMENTS,
            SqlTypeMap::INCREMENTS,
            SqlTypeMap::SMALL_INCREMENTS,
            SqlTypeMap::TINY_INCREMENTS,
            SqlTypeMap::BIG_INTEGER,
            SqlTypeMap::MEDIUM_INTEGER,
            SqlTypeMap::INTEGER,
            SqlTypeMap::SMALL_INTEGER,
            SqlTypeMap::TINY_INTEGER,
            SqlTypeMap::UNSIGNED_BIG_INTEGER,
            SqlTypeMap::UNSIGNED_MEDIUM_INTEGER,
            SqlTypeMap::UNSIGNED_INTEGER,
            SqlTypeMap::UNSIGNED_SMALL_INTEGER,
            SqlTypeMap::UNSIGNED_TINY_INTEGER,
            SqlTypeMap::DECIMAL,
            SqlTypeMap::DOUBLE,
            SqlTypeMap::FLOAT,
            SqlTypeMap::BELONGS_TO
            => 'number',

            /*Switcher*/
            SqlTypeMap::BOOLEAN => 'boolean',

            /*Text*/
            SqlTypeMap::CHAR,
            SqlTypeMap::STRING,
            SqlTypeMap::TEXT,
            SqlTypeMap::JSON,
            SqlTypeMap::JSONB,
            SqlTypeMap::LONG_TEXT,
            SqlTypeMap::MEDIUM_TEXT,
            SqlTypeMap::TINY_TEXT,
            SqlTypeMap::UUID,
            /*Date*/
            SqlTypeMap::TIMESTAMP,
            SqlTypeMap::TIME,
            SqlTypeMap::DATE_TIME,
            SqlTypeMap::DATE,
            SqlTypeMap::DATE_TIME_TZ,
            SqlTypeMap::YEAR,
            /*Enum*/
            SqlTypeMap::ENUM,
            => 'string',

            /*Relations*/
            SqlTypeMap::HAS_ONE =>
                $column->relation()?->table()->ucFirstSingular() . ' // TODO The object must be imported',
            SqlTypeMap::HAS_MANY, SqlTypeMap::BELONGS_TO_MANY =>
                $column->relation()?->table()->ucFirstSingular() . '[]' . ' // TODO The object must be imported',
        };
    }
}
