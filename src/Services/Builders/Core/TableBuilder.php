<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders\Core;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Services\Builders\AbstractBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\Core\Contracts\TableBuilderContract;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

final class TableBuilder extends AbstractBuilder implements TableBuilderContract
{
    /**
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        $tablePath = $this->codePath->path(BuildType::TABLE->value);

        StubBuilder::make($this->stubFile)
            ->makeFromStub($tablePath->file(), [
                '{thead}' => $this->columnsToThead(),
                '{entity_plural}' => $this->codeStructure->entity()->plural(),
                '{entity_singular}' => $this->codeStructure->entity()->singular(),
                '{tbody}' => $this->columnsToTbody(),
            ]);
    }

    public function columnsToThead(): string
    {
        $result = "";
        foreach ($this->codeStructure->columns() as $column) {
            $result .= str('')
                ->newLine()
                ->append("\t\t\t")
                ->append("<th>{$column->name()}</th>")
                ->value()
            ;
        }

        return $result;
    }

    public function columnsToTbody(): string
    {
        $result = "";
        foreach ($this->codeStructure->columns() as $column) {
            $result .= str('')
                ->newLine()
                ->append("\t\t\t")
                ->append("<td>{{ \${$this->codeStructure->entity()->singular()}->{$column->column()} }}</td>")
                ->value()
            ;
        }

        return $result;
    }
}
