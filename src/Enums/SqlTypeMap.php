<?php

namespace DevLnk\LaravelCodeBuilder\Enums;

enum SqlTypeMap: string
{
    /*ID*/
    case ID = 'id';

    case BIG_INCREMENTS = 'bigIncrements';

    case MEDIUM_INCREMENTS = 'mediumIncrements';

    case INCREMENTS = 'increments';

    case SMALL_INCREMENTS = 'smallIncrements';

    case TINY_INCREMENTS = 'tinyIncrements';

    /*Number*/
    case BIG_INTEGER = 'bigInteger';

    case MEDIUM_INTEGER = 'mediumInteger';

    case INTEGER = 'integer';

    case SMALL_INTEGER = 'smallInteger';

    case TINY_INTEGER = 'tinyInteger';

    case UNSIGNED_BIG_INTEGER = 'unsignedBigInteger';

    case UNSIGNED_MEDIUM_INTEGER = 'unsignedMediumInteger';

    case UNSIGNED_INTEGER = 'unsignedInteger';

    case UNSIGNED_SMALL_INTEGER = 'unsignedSmallInteger';

    case UNSIGNED_TINY_INTEGER = 'unsignedTinyInteger';

    case DECIMAL = 'decimal';

    case DOUBLE = 'double';

    case FLOAT = 'float';

    /*Switcher*/
    case BOOLEAN = 'boolean';

    /*Text*/
    case CHAR = 'char';

    case STRING = 'string';

    case TEXT = 'text';

    case JSON = 'json';

    case JSONB = 'jsonb';

    case LONG_TEXT = 'longText';

    case MEDIUM_TEXT = 'mediumText';

    case TINY_TEXT = 'tinyText';

    case UUID = 'uuid';

    /*Date*/
    case TIMESTAMP = 'timestamp';

    case TIME = 'time';

    case DATE_TIME = 'dateTime';

    case DATE = 'date';

    case DATE_TIME_TZ = 'dateTimeTz';

    case YEAR = 'year';

    /*Enum*/
    case ENUM = 'enum';

    /*Relations*/
    case HAS_ONE = 'HasOne';

    case HAS_MANY = 'HasMany';

    case BELONGS_TO = 'BelongsTo';

    public function getInputType(): string
    {
        return match ($this) {
            /*Number*/
            self::ID,
            self::BIG_INCREMENTS,
            self::MEDIUM_INCREMENTS,
            self::INCREMENTS,
            self::SMALL_INCREMENTS,
            self::TINY_INCREMENTS,
            self::BIG_INTEGER,
            self::MEDIUM_INTEGER,
            self::INTEGER,
            self::SMALL_INTEGER,
            self::TINY_INTEGER,
            self::UNSIGNED_BIG_INTEGER,
            self::UNSIGNED_MEDIUM_INTEGER,
            self::UNSIGNED_INTEGER,
            self::UNSIGNED_SMALL_INTEGER,
            self::UNSIGNED_TINY_INTEGER,
            self::DECIMAL,
            self::DOUBLE,
            self::FLOAT,
            self::BELONGS_TO
            => 'number',

            /*Switcher*/
            self::BOOLEAN => 'checkbox',

            /*Text*/
            self::CHAR,
            self::STRING,
            self::TEXT,
            self::JSON,
            self::JSONB,
            self::LONG_TEXT,
            self::MEDIUM_TEXT,
            self::TINY_TEXT,
            self::UUID,
            => 'text',

            /*Date*/
            self::TIMESTAMP,
            self::TIME,
            self::DATE_TIME,
            self::DATE,
            self::DATE_TIME_TZ,
            self::YEAR
            => 'text',

            /*Enum*/
            self::ENUM => 'text',

            /*Relations*/
            self::HAS_ONE => 'HasOne',
            self::HAS_MANY => 'HasMany',
        };
    }

    public function isIdType(): bool
    {
        $idFields = [
            self::ID,
            self::BIG_INCREMENTS,
            self::MEDIUM_INCREMENTS,
            self::INCREMENTS,
            self::SMALL_INCREMENTS,
            self::TINY_INCREMENTS
        ];

        return in_array($this, $idFields);
    }

    public static function fromSqlType(string $sqlType): SqlTypeMap
    {
        return match ($sqlType) {
            /*ID*/
            'primary' => self::ID,

            /*Number*/
            'bigint' => self::BIG_INTEGER,
            'mediumint' => self::MEDIUM_INTEGER,
            'int' => self::INTEGER,
            'integer' => self::INTEGER,
            'smallint' => self::SMALL_INTEGER,
            'tinyint' => self::TINY_INTEGER,
            'bigint unsigned' => self::UNSIGNED_BIG_INTEGER,
            'mediumint unsigned' => self::UNSIGNED_MEDIUM_INTEGER,
            'int unsigned' => self::UNSIGNED_INTEGER,
            'smallint unsigned' => self::UNSIGNED_SMALL_INTEGER,
            'tinyint unsigned' => self::UNSIGNED_TINY_INTEGER,
            'decimal' => self::DECIMAL,
            'double' => self::DOUBLE,
            'float' => self::FLOAT,

            /*Text*/
            'char' => self::CHAR,
            'varchar' => self::STRING,
            'json' => self::JSON,
            'jsonb' => self::JSONB,
            'longtext' => self::LONG_TEXT,
            'mediumtext' => self::MEDIUM_TEXT,
            'tinytext' => self::TINY_TEXT,
            'uuid' => self::UUID,

            /*Date*/
            'timestamp' => self::TIMESTAMP,
            'datetime' => self::DATE_TIME,
            'year' => self::YEAR,
            'date' => self::DATE,
            'time' => self::TIME,

            /*Enum*/
            'enum' => self::ENUM,

            default => self::TEXT
        };
    }
}
