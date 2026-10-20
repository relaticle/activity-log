<?php

declare(strict_types=1);

use Filament\Support\Facades\FilamentIcon;
use Relaticle\ActivityLog\Icons\ActivityLogIconAlias;

it('registers default icon aliases during package boot', function (): void {
    expect(FilamentIcon::resolve(ActivityLogIconAlias::LOG_OPERATION_CREATED))->toBe('heroicon-o-plus')
        ->and(FilamentIcon::resolve(ActivityLogIconAlias::LOG_OPERATION_DELETED))->toBe('heroicon-o-trash')
        ->and(FilamentIcon::resolve(ActivityLogIconAlias::LOG_OPERATION_RESTORED))->toBe('heroicon-o-arrow-uturn-left')
        ->and(FilamentIcon::resolve(ActivityLogIconAlias::LOG_OPERATION_UPDATED))->toBe('heroicon-o-pencil-square')
        ->and(FilamentIcon::resolve(ActivityLogIconAlias::LOG_OPERATION_UNKNOWN))->toBe('heroicon-o-pencil-square')
        ->and(FilamentIcon::resolve(ActivityLogIconAlias::LOG_COLLAPSE_BUTTON))->toBe('heroicon-m-chevron-down')
        ->and(FilamentIcon::resolve(ActivityLogIconAlias::LOG_VALUE_DIFF))->toBe('heroicon-m-arrow-right')
        ->and(FilamentIcon::resolve(ActivityLogIconAlias::TIMELINE_COLLAPSE_BUTTON))->toBe('heroicon-m-chevron-down')
        ->and(FilamentIcon::resolve(ActivityLogIconAlias::TIMELINE_LOAD_MORE_BUTTON))->toBe('heroicon-m-arrow-down')
        ->and(FilamentIcon::resolve(ActivityLogIconAlias::TIMELINE_EMPTY))->toBe('heroicon-o-clock')
        ->and(FilamentIcon::resolve(ActivityLogIconAlias::ACTION_ICON))->toBe('heroicon-o-bars-3-bottom-left')
        ->and(FilamentIcon::resolve(ActivityLogIconAlias::RELATION_MANAGER_ICON))->toBe('heroicon-o-clock');
});

it('allows overriding default icon aliases', function (): void {
    // Simulate a developer overriding an icon in their AppServiceProvider
    FilamentIcon::register([
        ActivityLogIconAlias::ACTION_ICON => 'heroicon-o-sparkles',
    ]);

    expect(FilamentIcon::resolve(ActivityLogIconAlias::ACTION_ICON))->toBe('heroicon-o-sparkles');
});