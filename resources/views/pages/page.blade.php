@php
    $contacts = [
        ['phone', 'Hotline', \App\Support\ShopContact::hotline()],
        ['mail', 'Email', \App\Support\ShopContact::email()],
        ['pin', 'Địa chỉ', \App\Support\ShopContact::address()],
        ['clock', 'Giờ làm việc', \App\Support\ShopContact::hours()],
    ];
@endphp

<x-layouts.app :title="$page->title" :description="$page->meta_description ?: $page->summary">
    @push('head')
        <link rel="canonical" href="{{ route('pages.show', $page) }}">
    @endpush

    <section class="border-b border-cream-300 bg-cream-100 pt-6 pb-10 lg:pb-12">
        <div class="tm-container">
            <div @class(['max-w-[760px]', 'mx-auto' => ! $isContact])>
                <x-ui.breadcrumb class="mb-8" :items="['Trang chủ' => route('home'), $page->title => null]" />

                <div class="flex flex-col items-start gap-4">
                    <h1 class="font-serif text-3xl leading-tight font-semibold text-balance text-ink-600 sm:text-4xl lg:text-5xl">{{ $page->title }}</h1>
                    @if ($page->summary)
                        <p class="tm-lead">{{ $page->summary }}</p>
                    @endif
                    <p class="text-sm text-ink-300">Cập nhật lần cuối: {{ $page->updated_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-12 lg:py-16">
        <div @class(['tm-container', 'grid items-start gap-10 lg:grid-cols-[minmax(0,1fr)_360px]' => $isContact])>
            <div @class(['tm-prose', 'mx-auto' => ! $isContact])>
                {{ $page->getRichContentAttribute('content') }}
            </div>

            @if ($isContact)
                <aside class="flex flex-col gap-4 rounded-2xl border border-cream-300 bg-white p-6 shadow-soft-sm lg:sticky lg:top-28">
                    <div class="font-serif text-xl font-semibold text-ink-600">{{ config('site.company') }}</div>
                    <dl class="flex flex-col gap-4">
                        @foreach ($contacts as [$icon, $label, $value])
                            <div class="flex gap-3">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-copper-50 text-copper-600">
                                    <x-ui.icon :name="$icon" :size="18" />
                                </span>
                                <div class="min-w-0">
                                    <dt class="text-xs font-semibold tracking-eyebrow text-ink-300 uppercase">{{ $label }}</dt>
                                    <dd class="mt-0.5 text-base font-medium break-words text-ink-600">{{ $value }}</dd>
                                </div>
                            </div>
                        @endforeach
                    </dl>
                </aside>
            @endif
        </div>
    </section>
</x-layouts.app>
