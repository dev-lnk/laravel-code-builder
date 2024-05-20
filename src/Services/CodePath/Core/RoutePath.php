<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodePath\Core;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Services\CodePath\AbstractPathItem;

readonly class RoutePath extends AbstractPathItem
{
    public function getBuildAlias(): string
    {
        return BuildType::ROUTE->value;
    }
}
