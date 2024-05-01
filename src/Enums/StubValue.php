<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Enums;

enum StubValue
{
    case USE_SOFT_DELETES;

    case SOFT_DELETES;

    case TIMESTAMPS;

    case TABLE;

    case USE_BELONGS_TO;

    case BELONGS_TO;

    function key(): string
    {
        return match ($this) {
            self::USE_SOFT_DELETES => '{use_soft_deletes}',
            self::SOFT_DELETES => '{soft_deletes}',
            self::TIMESTAMPS => '{timestamps}',
            self::TABLE => '{table}',
            self::USE_BELONGS_TO => '{use_belongs_to}',
            self::BELONGS_TO => '{belongs_to}',
        };
    }

    function value(): string
    {
        return match ($this) {
            self::USE_SOFT_DELETES => "use Illuminate\Database\Eloquent\SoftDeletes;",
            self::SOFT_DELETES => "\tuse SoftDeletes;",
            self::TIMESTAMPS => "\tpublic \$timestamps = false;",
            self::TABLE => "\tprotected \$table = ",
            self::USE_BELONGS_TO => "use Illuminate\Database\Eloquent\Relations\BelongsTo;",
            default => '',
        };
    }
}