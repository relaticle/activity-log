<?php

declare(strict_types=1);

namespace Relaticle\ActivityLog\Filament\RelationManagers;

use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Relaticle\ActivityLog\Filament\Livewire\ActivityLogLivewire;
use Relaticle\ActivityLog\Icons\ActivityLogIconAlias;

final class ActivityLogRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return (string) __('activity-log::messages.title');
    }

    public static function getIcon(Model $ownerRecord, string $pageClass): string|BackedEnum|Htmlable|null
    {
        return FilamentIcon::resolve(ActivityLogIconAlias::RELATION_MANAGER_ICON);
    }

    public static bool $infiniteScroll = true;

    public static bool $groupByDate = true;

    public static int $perPage = 20;

    public static ?string $emptyState = null;

    public function content(Schema $schema): Schema
    {
        $owner = $this->getOwnerRecord();

        return $schema->components([
            Section::make()
                ->schema([
                    Livewire::make(ActivityLogLivewire::class, [
                        'subjectClass' => $owner::class,
                        'subjectKey' => $owner->getKey(),
                        'groupByDate' => self::$groupByDate,
                        'perPage' => self::$perPage,
                        'infiniteScroll' => self::$infiniteScroll,
                        'emptyState' => static::$emptyState ?? (string) __('activity-log::messages.empty_state'),
                    ])->key('activity-log-relation-manager-'.$owner->getKey()),
                ]),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    /**
     * @return Builder<Model>|null
     */
    public function getTableQuery(): ?Builder
    {
        return null;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    /**
     * @return HasOne<Model, Model>
     */
    public function getRelationship(): HasOne
    {
        $owner = $this->getOwnerRecord();
        $keyName = $owner->getKeyName();

        return new HasOne($owner->newQuery(), $owner, $keyName, $keyName);
    }
}
