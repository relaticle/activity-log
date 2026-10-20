@use(Relaticle\ActivityLog\Icons\ActivityLogIconAlias)

<div class="flex flex-col gap-6">
    @if ($entries->isEmpty())
        <div class="flex flex-col items-center justify-center gap-3 py-10 text-center">
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-500">
                <x-filament::icon :alias="ActivityLogIconAlias::TIMELINE_EMPTY" class="h-5 w-5" />
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $emptyState }}</p>
        </div>
    @elseif ($grouped !== null)
        @foreach ($grouped as $label => $groupEntries)
            <section x-data="{ open: true }" wire:key="timeline-group-{{ $label }}">
                <header class="mb-1 flex items-center gap-3 py-1">
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700 ring-1 ring-inset ring-gray-200 transition hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10 dark:hover:bg-white/10"
                        :aria-expanded="open.toString()"
                        @click="open = !open"
                    >
                        {{ $label }}
                    </button>
                    <span class="h-px flex-1 bg-gray-200/70 dark:bg-white/10"></span>
                    <button
                        type="button"
                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200"
                        :aria-expanded="open.toString()"
                        aria-label="{{ $label }}"
                        @click="open = !open"
                    >
                        <x-filament::icon
                            :alias="ActivityLogIconAlias::TIMELINE_COLLAPSE_BUTTON"
                            class="h-4 w-4 transition-transform"
                            x-bind:class="open ? '' : '-rotate-90'"
                        />
                    </button>
                </header>
                <ol x-show="open" x-collapse class="flex flex-col">
                    @foreach ($groupEntries as $i => $entry)
                        <li class="relative" wire:key="timeline-entry-{{ $entry->id }}">
                            @if ($i !== count($groupEntries) - 1)
                                <span aria-hidden="true" class="absolute left-2 top-9 -bottom-2 flex w-7 justify-center">
                                    <span class="w-px bg-gray-200 dark:bg-white/10"></span>
                                </span>
                            @endif
                            {!! $registry->resolve($entry)->render($entry) !!}
                        </li>
                    @endforeach
                </ol>
            </section>
        @endforeach
    @else
        <ol class="flex flex-col">
            @foreach ($entries as $i => $entry)
                <li class="relative" wire:key="timeline-entry-{{ $entry->id }}">
                    @if ($i !== count($entries) - 1)
                        <span aria-hidden="true" class="absolute left-2 top-9 -bottom-2 flex w-7 justify-center">
                            <span class="w-px bg-gray-200 dark:bg-white/10"></span>
                        </span>
                    @endif
                    {!! $registry->resolve($entry)->render($entry) !!}
                </li>
            @endforeach
        </ol>
    @endif

    @if (! $entries->isEmpty() && $hasMore)
        @if ($infiniteScroll)
            <div
                wire:intersect="loadMore"
                wire:key="timeline-load-more"
                class="flex items-center justify-center py-4 text-xs text-gray-500 dark:text-gray-400"
            >
                <span wire:loading.remove wire:target="loadMore">{{ __('activity-log::messages.scroll_to_load_more') }}</span>
                <span wire:loading wire:target="loadMore" class="flex items-center gap-2">
                    <x-filament::loading-indicator class="h-4 w-4" />
                    {{ __('activity-log::messages.loading') }}
                </span>
            </div>
        @else
            <div class="flex justify-center pt-2">
                <x-filament::button
                    color="gray"
                    outlined
                    size="sm"
                    wire:click="loadMore"
                    wire:loading.attr="disabled"
                    wire:target="loadMore"
                    {{-- Filament requires 'icon' to be set before it will resolve the 'icon-alias' --}}
                    :icon="ActivityLogIconAlias::TIMELINE_LOAD_MORE_BUTTON"
                    :icon-alias="ActivityLogIconAlias::TIMELINE_LOAD_MORE_BUTTON"
                >
                    <span wire:loading.remove wire:target="loadMore">{{ __('activity-log::messages.load_more') }}</span>
                    <span wire:loading wire:target="loadMore">{{ __('activity-log::messages.loading') }}</span>
                </x-filament::button>
            </div>
        @endif
    @endif
</div>
