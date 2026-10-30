{{-- Usage: <x-analysis.price :amount="49000" /> → 49.000đ --}}
@props([
    'amount',
])

<span {{ $attributes }}>{{ number_format($amount, 0, ',', '.') }}đ</span>
