<section class="border-t border-cream-300 bg-white py-6 lg:py-8">
    <div class="tm-container grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6" data-reveal-stagger>
        @foreach ($trustPoints as $point)
            <div class="flex items-center gap-3.5" data-reveal>
                <x-ui.icon-ring :icon="$point['icon']" size="md" />
                <div>
                    <div class="mb-0.5 text-sm font-semibold text-ink-600">{{ $point['title'] }}</div>
                    <div class="text-xs leading-snug text-ink-300">{{ $point['description'] }}</div>
                </div>
            </div>
        @endforeach
    </div>
</section>
