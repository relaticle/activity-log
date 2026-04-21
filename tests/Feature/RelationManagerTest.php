<?php

declare(strict_types=1);

use Relaticle\ActivityLog\Filament\RelationManagers\ActivityLogRelationManager;

it('exposes static configuration with sensible defaults', function (): void {
    expect(ActivityLogRelationManager::$groupByDate)->toBeTrue()
        ->and(ActivityLogRelationManager::$perPage)->toBe(20)
        ->and(ActivityLogRelationManager::$infiniteScroll)->toBeTrue();
});

it('allows overriding configuration at the class level', function (): void {
    $originalGroup = ActivityLogRelationManager::$groupByDate;
    $originalPerPage = ActivityLogRelationManager::$perPage;

    ActivityLogRelationManager::$groupByDate = false;
    ActivityLogRelationManager::$perPage = 30;

    try {
        expect(ActivityLogRelationManager::$groupByDate)->toBeFalse()
            ->and(ActivityLogRelationManager::$perPage)->toBe(30);
    } finally {
        ActivityLogRelationManager::$groupByDate = $originalGroup;
        ActivityLogRelationManager::$perPage = $originalPerPage;
    }
});
