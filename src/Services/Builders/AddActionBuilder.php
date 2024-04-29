<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\Builders;

class AddActionBuilder extends AbstractBuilder
{
    public function build(): void
    {
        if($this->onlyFlag && $this->onlyFlag !== 'addAction') {
            return;
        }


    }
}