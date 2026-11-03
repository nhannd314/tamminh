<x-layouts.app title="Tướng số & Tử vi">
    @include('pages.home.hero')
    @include('pages.home.analysis')
    @if ($experts->isNotEmpty())
        @include('pages.home.experts')
    @endif
    @include('pages.home.palm-reading')
    @include('pages.home.reviews')
    @if ($posts->isNotEmpty())
        @include('pages.home.blog')
    @endif
    @include('pages.home.faq')
    @include('pages.home.trust')
</x-layouts.app>
