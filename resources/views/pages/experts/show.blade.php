@php
    use Illuminate\Support\Str;

    $photo = $expert->photoUrl();
    $zaloUrl = $expert->zaloUrl();
    $telUrl = filled($expert->phone) ? 'tel:'.preg_replace('/\s+/', '', $expert->phone) : null;
    $mapUrl = $expert->mapUrl();
    $bookingUrl = $expert->services ? '#dich-vu' : $zaloUrl;
    $officeImage = $expert->officeImageUrl();
    $specialties = $expert->specialties ?? [];
    $services = $expert->services ?? [];
    $reviews = $expert->reviews ?? [];
    $subtitle = $expert->title;
    $socialMeta = [
        'tiktok' => ['TikTok', 'bg-ink-600'],
        'facebook' => ['Facebook', 'bg-[#1877F2]'],
        'youtube' => ['YouTube', 'bg-[#FF0000]'],
    ];
    $socialLinks = $expert->socialLinks();
    $contacts = array_filter([
        ['phone', 'Số điện thoại', $expert->phone, $telUrl],
        ['mail', 'Email', $expert->email, $expert->email ? 'mailto:'.$expert->email : null],
        ['pin', 'Địa chỉ văn phòng', $expert->address, $mapUrl ? '#van-phong' : null],
        ['clock', 'Giờ tiếp khách', $expert->working_hours, $mapUrl ? '#van-phong' : null],
    ], fn (array $contact): bool => filled($contact[2]));
    $summary = $expert->headline ?: 'Kết nối với '.$expert->name.' - '.$expert->years_experience.' năm kinh nghiệm xem tướng số tại Tâm Minh.';
    $zaloButton = 'tm-btn tm-btn-md bg-[#0068FF] !text-base !text-white shadow-soft-sm hover:bg-[#0054D1]';
@endphp

<x-layouts.app :title="$expert->name" :description="$summary">
    @push('head')
        <link rel="canonical" href="{{ route('experts.show', $expert) }}">
        <meta property="og:type" content="profile">
        <meta property="og:title" content="{{ $expert->name }}">
        <meta property="og:description" content="{{ $summary }}">
        <meta property="og:url" content="{{ route('experts.show', $expert) }}">
        @if ($photo)
            <meta property="og:image" content="{{ $photo }}">
        @endif
    @endpush

    <div class="tm-container pt-6 pb-24 lg:pb-16">
        <x-ui.breadcrumb class="mb-6" :items="['Trang chủ' => route('home'), $expert->name => null]" />

        <div class="grid items-start gap-6 lg:grid-cols-[380px_minmax(0,1fr)] lg:gap-8">
            <aside class="grid gap-5 md:grid-cols-2 lg:sticky lg:top-24 lg:grid-cols-1">
                <div class="grid overflow-hidden rounded-2xl border border-cream-300 bg-white shadow-soft-sm sm:grid-cols-[240px_minmax(0,1fr)] md:col-span-2 lg:col-span-1 lg:grid-cols-1">
                    <div class="relative bg-cream-200">
                        @if ($photo)
                            <img src="{{ $photo }}" alt="{{ $expert->name }}" class="block aspect-[4/3] h-full max-h-[320px] w-full object-cover object-top sm:aspect-auto sm:max-h-none lg:aspect-[4/3] lg:h-auto" />
                        @else
                            <div class="flex aspect-[4/3] items-center justify-center bg-navy-500 font-serif text-8xl font-semibold text-copper-400 sm:aspect-auto sm:h-full">{{ $expert->glyph }}</div>
                        @endif
                        @if ($expert->is_accepting_bookings)
                            <span class="absolute top-4 left-4 inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1.5 text-sm font-semibold text-ink-600 shadow-soft-sm">
                                <span class="size-2 rounded-full bg-emerald-500"></span>Đang nhận lịch
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-col justify-center gap-4 p-6">
                        <div>
                            <h1 class="font-serif text-3xl leading-tight font-semibold text-ink-600">{{ $expert->name }}</h1>
                            @if ($subtitle)
                                <p class="mt-1 text-base text-ink-400">{{ $subtitle }}</p>
                            @endif
                        </div>

                        @if ($zaloUrl)
                            <a href="{{ $zaloUrl }}" target="_blank" rel="noopener" class="{{ $zaloButton }} w-full"><x-ui.brand-icon network="zalo" />Nhắn tin qua Zalo</a>
                        @endif
                        @if ($telUrl || $bookingUrl)
                            <div class="grid grid-cols-2 gap-3">
                                @if ($telUrl)
                                    <x-ui.button :href="$telUrl" variant="outline" icon="phone" class="!text-base">Gọi điện</x-ui.button>
                                @endif
                                @if ($bookingUrl)
                                    <x-ui.button :href="$bookingUrl" variant="secondary" icon="calendar" class="!text-base">Đặt lịch</x-ui.button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                @if ($contacts)
                    <div class="rounded-2xl border border-cream-300 bg-white p-5 shadow-soft-sm">
                        <h2 class="mb-1 px-1 font-sans text-sm font-semibold tracking-[0.14em] text-ink-600 uppercase">Thông tin liên hệ</h2>
                        <div class="flex flex-col divide-y divide-cream-200">
                            @foreach ($contacts as [$icon, $label, $value, $href])
                                <{{ $href ? 'a' : 'div' }} @if ($href) href="{{ $href }}" @endif class="group flex items-start gap-3.5 rounded-lg px-1 py-3 transition-colors {{ $href ? 'hover:bg-cream-50' : '' }}">
                                    <span class="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-full bg-copper-50 text-copper-600"><x-ui.icon :name="$icon" :size="18" /></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm text-ink-300">{{ $label }}</span>
                                        <span @class(['block break-words text-base font-semibold text-ink-600', 'group-hover:text-copper-700' => $href])>{{ $value }}</span>
                                    </span>
                                </{{ $href ? 'a' : 'div' }}>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($socialLinks)
                    <div class="rounded-2xl border border-cream-300 bg-white p-5 shadow-soft-sm">
                        <h2 class="mb-3 px-1 font-sans text-sm font-semibold tracking-[0.14em] text-ink-600 uppercase">Kênh mạng xã hội</h2>
                        <div class="flex flex-col gap-3">
                            @foreach ($socialLinks as $network => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="flex items-center gap-3.5 rounded-xl border border-cream-300 p-3.5 transition-colors hover:border-copper-300 hover:bg-cream-50">
                                    <span class="flex size-11 shrink-0 items-center justify-center rounded-full text-white {{ $socialMeta[$network][1] }}"><x-ui.brand-icon :network="$network" :size="22" /></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-base font-semibold text-ink-600">{{ $socialMeta[$network][0] }}</span>
                                        <span class="block truncate text-sm text-ink-300">{{ Str::of($url)->after('://')->replaceMatches('#^www\.#', '')->rtrim('/') }}</span>
                                    </span>
                                    <span class="text-sm font-semibold text-copper-600">Theo dõi</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>

            <div class="flex min-w-0 flex-col gap-6">
                <section class="rounded-2xl border border-cream-300 bg-white p-6 shadow-soft-sm sm:p-8">
                    <x-ui.eyebrow>Hồ sơ chuyên gia</x-ui.eyebrow>
                    <h2 class="mt-4 font-serif text-2xl font-semibold text-ink-600 sm:text-3xl">{{ $expert->headline ?: $expert->years_experience.' năm kinh nghiệm' }}</h2>

                    <dl @class(['mt-6 grid gap-3 border-y border-cream-300 py-5', 'grid-cols-3' => $expert->rating, 'grid-cols-2' => ! $expert->rating])>
                        <div class="text-center">
                            <dt class="text-sm text-ink-300">Kinh nghiệm</dt>
                            <dd class="mt-1 font-serif text-2xl font-semibold text-copper-600 sm:text-3xl">{{ $expert->years_experience }} năm</dd>
                        </div>
                        <div @class(['border-l border-cream-300 text-center', 'border-r' => $expert->rating])>
                            <dt class="text-sm text-ink-300">Buổi xem</dt>
                            <dd class="mt-1 font-serif text-2xl font-semibold text-copper-600 sm:text-3xl">{{ number_format($expert->sessions_count, 0, ',', '.') }}</dd>
                        </div>
                        @if ($expert->rating)
                            <div class="text-center">
                                <dt class="text-sm text-ink-300">Đánh giá</dt>
                                <dd class="mt-1 font-serif text-2xl font-semibold text-copper-600 sm:text-3xl">{{ $expert->rating }}/5</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($expert->description)
                        <div class="mt-6 flex flex-col gap-4 text-base leading-relaxed text-ink-500 text-pretty">
                            @foreach (preg_split('/\R{1,}/', trim($expert->description)) as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if ($specialties)
                        <div class="mt-6 flex flex-wrap gap-2">
                            @foreach ($specialties as $specialty)
                                <span class="rounded-full border border-copper-200 bg-copper-50 px-3.5 py-1.5 text-sm font-medium text-copper-700">{{ $specialty }}</span>
                            @endforeach
                        </div>
                    @endif
                </section>

                @if ($services)
                    <section id="dich-vu" class="scroll-mt-24 rounded-2xl border border-cream-300 bg-white p-6 shadow-soft-sm sm:p-8">
                        <h2 class="font-serif text-2xl font-semibold text-ink-600">Dịch vụ & chi phí</h2>
                        <div class="mt-2">
                            @foreach ($services as $service)
                                <div class="flex flex-col gap-3 border-b border-cream-300 py-5 last:border-0 sm:flex-row sm:items-center sm:gap-6">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-serif text-xl font-semibold text-ink-600">{{ $service['name'] }}</h3>
                                        @if (filled($service['description'] ?? null))
                                            <p class="mt-1 text-base leading-relaxed text-ink-400">{{ $service['description'] }}</p>
                                        @endif
                                    </div>
                                    <div class="flex shrink-0 items-center gap-5">
                                        @if (filled($service['duration'] ?? null))
                                            <span class="inline-flex items-center gap-1.5 text-sm text-ink-300"><x-ui.icon name="clock" :size="15" />{{ $service['duration'] }}</span>
                                        @endif
                                        @if (filled($service['price'] ?? null))
                                            <span class="font-serif text-xl font-semibold text-copper-600">{{ $service['price'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($zaloUrl)
                            <div class="mt-5 flex flex-col gap-3 rounded-xl bg-cream-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-base text-ink-500">Chưa rõ nên chọn dịch vụ nào? Nhắn Zalo để thầy tư vấn miễn phí.</p>
                                <a href="{{ $zaloUrl }}" target="_blank" rel="noopener" class="{{ $zaloButton }} shrink-0"><x-ui.brand-icon network="zalo" />Nhắn Zalo</a>
                            </div>
                        @endif
                    </section>
                @endif

                @if ($expert->address)
                    <section id="van-phong" class="scroll-mt-24 rounded-2xl border border-cream-300 bg-white p-6 shadow-soft-sm sm:p-8">
                        <h2 class="font-serif text-2xl font-semibold text-ink-600">Văn phòng tiếp khách</h2>
                        <p class="mt-2 flex items-start gap-2 text-base text-ink-500"><x-ui.icon name="pin" :size="18" class="mt-0.5 text-copper-600" />{{ $expert->address }}</p>
                        @if ($officeImage)
                            <img src="{{ $officeImage }}" alt="Văn phòng {{ $expert->name }}" loading="lazy" class="mt-5 aspect-[16/7] w-full rounded-xl object-cover" />
                        @endif
                        <div class="mt-4 flex flex-wrap gap-3">
                            <x-ui.button :href="$mapUrl" target="_blank" rel="noopener" variant="outline" icon="pin" class="!text-base">Chỉ đường</x-ui.button>
                        </div>
                    </section>
                @endif

                @if ($reviews)
                    <section class="rounded-2xl border border-cream-300 bg-white p-6 shadow-soft-sm sm:p-8">
                        <div class="flex flex-wrap items-end justify-between gap-2">
                            <h2 class="font-serif text-2xl font-semibold text-ink-600">Khách hàng nói gì</h2>
                            @if ($expert->reviews_count)
                                <span class="text-sm text-ink-300">{{ number_format($expert->reviews_count, 0, ',', '.') }} đánh giá</span>
                            @endif
                        </div>
                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            @foreach ($reviews as $review)
                                <figure class="flex flex-col gap-3 rounded-xl border border-cream-300 bg-cream-50 p-6">
                                    <div class="flex gap-0.5 text-copper-400">
                                        @for ($i = 0; $i < (int) ($review['rating'] ?? 5); $i++)
                                            <x-ui.icon name="star" :size="16" class="fill-current" />
                                        @endfor
                                    </div>
                                    <blockquote class="flex-1 text-base leading-relaxed text-ink-500">{{ $review['quote'] }}</blockquote>
                                    <figcaption class="text-sm">
                                        <span class="font-semibold text-ink-600">{{ $review['name'] }}</span>
                                        @if (filled($review['location'] ?? null))
                                            <span class="text-ink-300"> · {{ $review['location'] }}</span>
                                        @endif
                                    </figcaption>
                                </figure>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </div>

    @if ($zaloUrl || $telUrl)
        <div class="fixed inset-x-0 bottom-0 z-40 flex gap-3 border-t border-cream-300 bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
            @if ($telUrl)
                <x-ui.button :href="$telUrl" variant="outline" icon="phone" :icon-size="20" aria-label="Gọi điện" class="size-12 shrink-0 !p-0" />
            @endif
            @if ($zaloUrl)
                <a href="{{ $zaloUrl }}" target="_blank" rel="noopener" class="{{ $zaloButton }} h-12 flex-1"><x-ui.brand-icon network="zalo" />Nhắn tin qua Zalo</a>
            @endif
        </div>
    @endif
</x-layouts.app>
