<?php

declare(strict_types=1);

namespace Relaticle\ActivityLog;

use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Foundation\Application;
use Livewire\Livewire;
use Relaticle\ActivityLog\Icons\ActivityLogIconAlias;
use Relaticle\ActivityLog\Renderers\RendererRegistry;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class ActivityLogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('activity-log')
            ->hasConfigFile('activity-log')
            ->hasViews('activity-log')
            ->hasTranslations();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(RendererRegistry::class, fn (Application $app): RendererRegistry => new RendererRegistry($app));
        $this->app->singleton(Timeline\TimelineCache::class);
    }

    public function packageBooted(): void
    {
        FilamentIcon::register([
            ActivityLogIconAlias::LOG_OPERATION_CREATED => 'heroicon-o-plus',
            ActivityLogIconAlias::LOG_OPERATION_DELETED => 'heroicon-o-trash',
            ActivityLogIconAlias::LOG_OPERATION_RESTORED => 'heroicon-o-arrow-uturn-left',
            ActivityLogIconAlias::LOG_OPERATION_UPDATED => 'heroicon-o-pencil-square',
            ActivityLogIconAlias::LOG_OPERATION_UNKNOWN => 'heroicon-o-pencil-square',
            ActivityLogIconAlias::LOG_COLLAPSE_BUTTON => 'heroicon-m-chevron-down',
            ActivityLogIconAlias::LOG_VALUE_DIFF => 'heroicon-m-arrow-right',

            ActivityLogIconAlias::TIMELINE_COLLAPSE_BUTTON => 'heroicon-m-chevron-down',
            ActivityLogIconAlias::TIMELINE_LOAD_MORE_BUTTON => 'heroicon-m-arrow-down',
            ActivityLogIconAlias::TIMELINE_EMPTY => 'heroicon-o-clock',

            ActivityLogIconAlias::ACTION_ICON => 'heroicon-o-bars-3-bottom-left',
            ActivityLogIconAlias::RELATION_MANAGER_ICON => 'heroicon-o-clock',
        ]);

        Livewire::component('activity-log', Filament\Livewire\ActivityLogLivewire::class);

        $registry = $this->app->make(RendererRegistry::class);
        $registry->register('activity_log', Renderers\ActivityLogRenderer::class);
        $registry->register('related_activity_log', Renderers\ActivityLogRenderer::class);
    }
}
