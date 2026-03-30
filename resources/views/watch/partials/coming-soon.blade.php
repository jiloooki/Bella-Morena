@props(['listing'])

@php
    $releaseDate = $listing->release_date?->copy()?->startOfDay();
    $releaseIso = $releaseDate?->format('Y-m-d\T00:00:00');
    $storageKey = 'bella-coming-soon-'.$listing->type.'-'.$listing->id;
@endphp

<section
    id="coming-soon"
    class="bella-coming-soon-panel bella-detail-panel"
    x-data="{
        releaseAt: new Date(@js($releaseIso)).getTime(),
        storageKey: @js($storageKey),
        notifyLabel: @js(__('Notify Me')),
        savedLabel: @js(__('Reminder Saved')),
        notified: false,
        days: '00',
        hours: '00',
        minutes: '00',
        seconds: '00',
        init() {
            @guest
            try {
                this.notified = localStorage.getItem(this.storageKey) === '1';
            } catch (error) {}
            @endguest

            this.tick();
            this.interval = setInterval(() => this.tick(), 1000);
        },
        tick() {
            const diff = Math.max(0, this.releaseAt - Date.now());
            const totalSeconds = Math.floor(diff / 1000);

            const dayCount = Math.floor(totalSeconds / 86400);
            const hourCount = Math.floor((totalSeconds % 86400) / 3600);
            const minuteCount = Math.floor((totalSeconds % 3600) / 60);
            const secondCount = totalSeconds % 60;

            this.days = String(dayCount).padStart(2, '0');
            this.hours = String(hourCount).padStart(2, '0');
            this.minutes = String(minuteCount).padStart(2, '0');
            this.seconds = String(secondCount).padStart(2, '0');
        },
        saveReminder() {
            this.notified = true;

            try {
                localStorage.setItem(this.storageKey, '1');
            } catch (error) {}
        }
    }"
>
    <div class="bella-coming-soon-layout">
        <div class="bella-coming-soon-poster">
            <img src="{{ $listing->imageurl }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
        </div>

        <div class="bella-coming-soon-copy">
            <span class="bella-coming-soon-badge">{{ __('Coming Soon') }}</span>

            <h2 class="bella-section-heading !text-3xl sm:!text-4xl !mb-0">{{ $listing->title }}</h2>

            <p class="bella-coming-soon-date">
                {{ __('Available :date', ['date' => $listing->release_date->translatedFormat('F j, Y')]) }}
            </p>

            <div class="bella-coming-soon-countdown" aria-label="{{ __('Release countdown') }}">
                <div class="bella-coming-soon-count">
                    <strong x-text="days"></strong>
                    <span>{{ __('Days') }}</span>
                </div>
                <div class="bella-coming-soon-count">
                    <strong x-text="hours"></strong>
                    <span>{{ __('Hours') }}</span>
                </div>
                <div class="bella-coming-soon-count">
                    <strong x-text="minutes"></strong>
                    <span>{{ __('Minutes') }}</span>
                </div>
                <div class="bella-coming-soon-count">
                    <strong x-text="seconds"></strong>
                    <span>{{ __('Seconds') }}</span>
                </div>
            </div>

            <p class="bella-coming-soon-note">
                {{ __('This title is not available to stream yet. Save a reminder and check back on release day.') }}
            </p>

            <div class="bella-coming-soon-actions">
                @auth
                    <livewire:watchlist-component
                        :model="$listing"
                        :showTextAlways="true"
                        buttonClass="bella-coming-soon-notify"
                        :label="__('Notify Me')"
                        :activeLabel="__('Reminder Saved')"
                    />
                @else
                    <button
                        type="button"
                        class="bella-inline-action is-wide bella-coming-soon-notify"
                        :class="{ 'is-saved': notified }"
                        @click="saveReminder()"
                    >
                        <span aria-hidden="true">🔔</span>
                        <span x-text="notified ? savedLabel : notifyLabel"></span>
                    </button>
                @endauth
            </div>
        </div>
    </div>
</section>
