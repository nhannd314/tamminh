{{--
    Khối "cần hỗ trợ": Zalo / hotline / email của cửa hàng (ShopContact, sửa ở Cấu hình > Liên hệ).
    Usage: <x-site.contact-help message="Đã thanh toán mà chưa nhận được luận giải? Liên hệ:" />
--}}
@props([
    'message' => 'Cần hỗ trợ? Liên hệ Tâm Minh:',
])

@use('App\Support\ShopContact')

@php
    $channels = array_filter([
        ['zalo', 'Zalo '.ShopContact::zalo(), ShopContact::zaloUrl(), true],
        ['phone', 'Hotline '.ShopContact::hotline(), ShopContact::hotlineUrl(), false],
        ['mail', ShopContact::email(), ShopContact::emailUrl(), false],
    ], fn (array $channel): bool => $channel[2] !== null);
@endphp

@if ($channels)
    <div {{ $attributes->class('rounded-lg border border-cream-300 bg-cream-50 px-4 py-3.5 text-sm text-ink-500') }} data-contact-help>
        <p class="mb-2.5 leading-relaxed">{{ $message }}</p>
        <ul class="flex flex-wrap gap-x-5 gap-y-2">
            @foreach ($channels as [$icon, $label, $url, $isExternal])
                <li>
                    <a href="{{ $url }}" @if ($isExternal) target="_blank" rel="noopener" @endif class="inline-flex items-center gap-2 font-semibold text-copper-700 hover:text-copper-600">
                        @if ($icon === 'zalo')
                            <span class="flex size-5 items-center justify-center rounded bg-[#0068FF] text-white"><x-ui.brand-icon network="zalo" :size="13" /></span>
                        @else
                            <x-ui.icon :name="$icon" :size="16" />
                        @endif
                        <span class="break-all">{{ $label }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
