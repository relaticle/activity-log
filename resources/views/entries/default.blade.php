@php
    $event = $entry->event;
    $causerName = $entry->causer?->name ?? null;
    $fallbackTitle = Str::headline($event);
    $title = $entry->title ?? $fallbackTitle;
    $description = $entry->description
        ?? ($causerName
            ? sprintf('%s %s', $causerName, Str::lower($fallbackTitle))
            : $fallbackTitle);
@endphp

<div
    class="grid grid-cols-[28px_1fr_auto] items-start gap-x-3 rounded-md px-2 py-2 transition hover:bg-gray-50/60 dark:hover:bg-white/[0.03]"
    data-type="{{ $entry->type }}"
    data-event="{{ $event }}"
>
    <div class="flex justify-center pt-0.5">
        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-gray-600 ring-1 ring-gray-200 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10">
            <x-filament::icon icon="ri-circle-line" class="h-3.5 w-3.5" />
        </span>
    </div>

    <div class="min-w-0">
        <p class="text-[13px] leading-5 text-gray-900 dark:text-gray-100">
            <span class="font-medium">{{ $title }}</span>
        </p>
        @if ($description)
            <p class="mt-0.5 text-[12px] text-gray-500 dark:text-gray-400">
                {{ $description }}
            </p>
        @endif
    </div>

    <div class="flex items-center pt-0.5">
        <time
            class="text-[11px] text-gray-500 dark:text-gray-400 tabular-nums"
            datetime="{{ $entry->occurredAt->toIso8601String() }}"
            title="{{ $entry->occurredAt->toDayDateTimeString() }}"
        >
            {{ $entry->occurredAt->diffForHumans(syntax: null, short: true) }}
        </time>
    </div>
</div>
