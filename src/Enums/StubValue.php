<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Enums;

enum StubValue
{
    case USE_CONTROLLER;

    case USE_SOFT_DELETES;

    case SOFT_DELETES;

    case TIMESTAMPS;

    case TABLE;

    case USE_BELONGS_TO;

    case USE_HAS_MANY;

    case USE_HAS_ONE;

    case USE_BELONGS_TO_MANY;

    case RELATIONS;

    function key(): string
    {
        return match ($this) {
            self::USE_CONTROLLER => '{use_controller}',
            self::USE_SOFT_DELETES => '{use_soft_deletes}',
            self::USE_BELONGS_TO => '{use_belongs_to}',
            self::USE_HAS_MANY => '{use_has_many}',
            self::USE_HAS_ONE => '{use_has_one}',
            self::USE_BELONGS_TO_MANY => '{use_belongs_to_many}',
            self::SOFT_DELETES => '{soft_deletes}',
            self::TIMESTAMPS => '{timestamps}',
            self::TABLE => '{table}',
            self::RELATIONS => '{relations}',
        };
    }

    function value(): string
    {
        return match ($this) {
            self::USE_CONTROLLER => "use App\Http\Controllers\Controller;",
            self::USE_SOFT_DELETES => "use Illuminate\Database\Eloquent\SoftDeletes;",
            self::SOFT_DELETES => "\tuse SoftDeletes;",
            self::TIMESTAMPS => "\tpublic \$timestamps = false;",
            self::TABLE => "\tprotected \$table = ",
            self::USE_BELONGS_TO => "use Illuminate\Database\Eloquent\Relations\BelongsTo;",
            self::USE_HAS_MANY => "use Illuminate\Database\Eloquent\Relations\HasMany;",
            self::USE_HAS_ONE => "use Illuminate\Database\Eloquent\Relations\HasOne;",
            self::USE_BELONGS_TO_MANY => "use Illuminate\Database\Eloquent\Relations\BelongsToMany;",
            default => '',
        };
    }
}