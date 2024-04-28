<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Enums;

enum StubValue
{
    case USE_SOFT_DELETES;

    case SOFT_DELETES;

    case TIMESTAMPS;

    case TABLE;

    function key(): string
    {
        return match ($this) {
            self::USE_SOFT_DELETES => '{use_soft_deletes}',
            self::SOFT_DELETES => '{soft_deletes}',
            self::TIMESTAMPS => '{timestamps}',
            self::TABLE => '{table}',
        };
    }

    function value(): string
    {
        return match ($this) {
            self::USE_SOFT_DELETES => "use Illuminate\Database\Eloquent\SoftDeletes;",
            self::SOFT_DELETES => "\tuse SoftDeletes;",
            self::TIMESTAMPS => "\tpublic \$timestamps = false;",
            self::TABLE => "\tprotected \$table = ",
        };
    }
}