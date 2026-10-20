<?php

declare(strict_types=1);

namespace Relaticle\ActivityLog\Icons;

class ActivityLogIconAlias
{
    const string LOG_OPERATION_CREATED = 'relaticle::activity-log::log.operation-created';
    const string LOG_OPERATION_DELETED = 'relaticle::activity-log::log.operation-deleted';
    const string LOG_OPERATION_RESTORED = 'relaticle::activity-log::log.operation-restored';
    const string LOG_OPERATION_UPDATED = 'relaticle::activity-log::log.operation-updated';
    const string LOG_OPERATION_UNKNOWN = 'relaticle::activity-log::log.operation-unknown';
    const string LOG_COLLAPSE_BUTTON = 'relaticle::activity-log::log.collapse-button';
    const string LOG_VALUE_DIFF = 'relaticle::activity-log::log.value-diff';

    const string TIMELINE_COLLAPSE_BUTTON = 'relaticle::activity-log::timeline.collapse-button';
    const string TIMELINE_LOAD_MORE_BUTTON = 'relaticle::activity-log::timeline.load-more-button';
    const string TIMELINE_EMPTY = 'relaticle::activity-log::timeline.empty';

    const string ACTION_ICON = 'relaticle::activity-log::action.icon';
    const string RELATION_MANAGER_ICON = 'relaticle::activity-log::relation-manager.icon';
}