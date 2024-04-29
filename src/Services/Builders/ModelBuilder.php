<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders;

use DevLnk\LaravelCodeBuilder\Enums\StubValue;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ModelBuilder extends AbstractBuilder
{
    /**
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        if($this->onlyFlag && $this->onlyFlag !== 'model') {
            return;
        }

        StubBuilder::make($this->stubFile)
            ->setKey(
                StubValue::USE_SOFT_DELETES->key(),
                StubValue::USE_SOFT_DELETES->value(),
                $this->codeStructure->isSoftDeletes()
            )
            ->setKey(
                StubValue::SOFT_DELETES->key(),
                StubValue::SOFT_DELETES->value(),
                $this->codeStructure->isSoftDeletes()
            )->setKey(
                StubValue::TIMESTAMPS->key(),
                StubValue::TIMESTAMPS->value(),
                ! $this->codeStructure->isTimestamps()
            )->setKey(
                StubValue::TABLE->key(),
                StubValue::TABLE->value() . " '{$this->codeStructure->table()}';",
                $this->codeStructure->table() !== $this->codeStructure->entity()->str()->plural()->snake()->value()
            )->makeFromStub($this->path->file(), [
                '{namespace}' => $this->path->namespace(),
                '{class}' => $this->codeStructure->entity()->ucFirstSingular(),
                '{fillable}' => $this->codeStructure->columnsToModel(),
            ])
        ;
    }
}