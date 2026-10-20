<?php

declare(strict_types=1);

namespace Relaticle\ActivityLog\Support;

use Relaticle\ActivityLog\Icons\ActivityLogIconAlias;

enum ActivityLogOperation: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Restored = 'restored';

    public function icon(): string
    {
        return match ($this) {
            self::Created => ActivityLogIconAlias::LOG_OPERATION_CREATED,
            self::Deleted => ActivityLogIconAlias::LOG_OPERATION_DELETED,
            self::Restored => ActivityLogIconAlias::LOG_OPERATION_RESTORED,
            self::Updated => ActivityLogIconAlias::LOG_OPERATION_UPDATED,
        };
    }

    public function verb(): string
    {
        return $this->value;
    }
}
