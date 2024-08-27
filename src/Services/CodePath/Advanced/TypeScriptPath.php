<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodePath\Advanced;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Services\CodePath\AbstractPathItem;

readonly class TypeScriptPath extends AbstractPathItem
{
    public function getBuildAlias(): string
    {
        return BuildType::TYPE_SCRIPT->value;
    }
}
