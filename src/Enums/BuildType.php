<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Enums;

enum BuildType: string implements BuildTypeContract
{
    case MODEL = 'model';

    case ADD_ACTION = 'addAction';

    case EDIT_ACTION = 'editAction';

    case REQUEST = 'request';

    case CONTROLLER = 'controller';

    case ROUTE = 'route';

    case FORM = 'form';

    case DTO = 'DTO';

    case TABLE = 'table';

    case TYPE_SCRIPT = 'typeScript';

    public function value(): string
    {
        return $this->value;
    }

    public function stub(): string
    {
        return match ($this) {
            self::MODEL => 'Model',
            self::ADD_ACTION => 'AddAction',
            self::EDIT_ACTION => 'EditAction',
            self::REQUEST => 'Request',
            self::CONTROLLER => 'Controller',
            self::ROUTE => 'Route',
            self::FORM => 'Form',
            self::DTO => 'DTO',
            self::TABLE => 'Table',
            self::TYPE_SCRIPT => 'TypeScript',
        };
    }
}
