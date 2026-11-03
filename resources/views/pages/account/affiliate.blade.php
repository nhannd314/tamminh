@php
    $money = fn (int $amount): string => number_format($amount, 0, ',', '.').'đ';
    $card = 'rounded-2xl border border-cream-300 bg-white p-5 shadow-soft-sm sm:p-7';
    $rules = [
        "Vật phẩm phong thủy: {$productPercent}% tiền hàng (không tính phí giao hàng), được xác nhận khi đơn giao thành công.",
        "Xem tướng online: {$analysisPercent}% số tiền khách mở khoá luận giải, được xác nhận ngay khi khách thanh toán.",
        "Link có hiệu lực {$cookieDays} ngày kể từ lần khách bấm gần nhất. Khách bấm link của người khác sau đó thì tính cho người sau.",
        'Không tính hoa hồng khi tự mua qua link của chính mình, hoặc đơn bị huỷ.',
    ];
@endphp

<x-layouts.app title="Kiếm tiền affiliate" description="Chương trình affiliate Tâm Minh: chia sẻ link, nhận hoa hồng vật phẩm phong thủy và xem tướng online.">
    @push('head')
        <meta name="robots" content="noindex, nofollow">
    @endpush

    <div class="tm-container max-w-[1040px] pt-6 pb-16">
        <x-ui.breadcrumb class="mb-6" :items="['Trang chủ' => route('home'), 'Tài khoản' => route('account.show'), 'Kiếm tiền affiliate' => null]" />

        <div class="grid items-start gap-6 lg:grid-cols-[320px_minmax(0,1fr)] lg:gap-8">
            <x-account.sidebar :user="$user" />

            <div class="flex min-w-0 flex-col gap-6">
                @if ($affiliate === null)
                    {{-- Chưa tham gia --}}
                    <section class="{{ $card }}" aria-labelledby="affiliate-join">
                        <x-ui.eyebrow>Chương trình affiliate</x-ui.eyebrow>
                        <h1 id="affiliate-join" class="mt-2 font-serif text-3xl leading-tight font-semibold text-ink-600">Chia sẻ Tâm Minh, nhận hoa hồng</h1>
                        <p class="mt-3 max-w-[560px] text-base leading-relaxed text-ink-400">Giới thiệu bạn bè xem tướng online và mua vật phẩm phong thủy qua link riêng của bạn. Mỗi đơn thành công, bạn nhận hoa hồng chuyển khoản trực tiếp.</p>

                        <div class="mt-6 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-copper-200 bg-copper-50 p-4">
                                <div class="font-serif text-3xl font-semibold text-copper-600">{{ $productPercent }}%</div>
                                <div class="mt-1 text-sm font-semibold text-ink-600">Vật phẩm phong thủy</div>
                            </div>
                            <div class="rounded-xl border border-copper-200 bg-copper-50 p-4">
                                <div class="font-serif text-3xl font-semibold text-copper-600">{{ $analysisPercent }}%</div>
                                <div class="mt-1 text-sm font-semibold text-ink-600">Xem tướng số online</div>
                            </div>
                        </div>

                        <ol class="mt-6 grid gap-4 sm:grid-cols-3">
                            @foreach (['Bấm tham gia để nhận link giới thiệu riêng', 'Chia sẻ link qua Facebook, Zalo, TikTok…', 'Nhận hoa hồng chuyển khoản khi đơn thành công'] as $step)
                                <li class="flex gap-3 text-sm leading-relaxed text-ink-500">
                                    <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-navy-500 text-xs font-semibold text-copper-300">{{ $loop->iteration }}</span>
                                    {{ $step }}
                                </li>
                            @endforeach
                        </ol>

                        <ul class="mt-6 flex flex-col gap-2 border-t border-cream-300 pt-5 text-sm leading-relaxed text-ink-400">
                            @foreach ($rules as $rule)
                                <li class="flex gap-2"><x-ui.icon name="check" :size="16" class="mt-0.5 text-copper-500" />{{ $rule }}</li>
                            @endforeach
                        </ul>

                        <form method="POST" action="{{ route('account.affiliate.store') }}" class="mt-6" data-submit-loading>
                            @csrf
                            <x-ui.button type="submit" icon="coins" data-loading-text="Đang kích hoạt…">Tham gia ngay - miễn phí</x-ui.button>
                        </form>
                    </section>
                @else
                    <h1 class="sr-only">Kiếm tiền affiliate</h1>

                    @unless ($affiliate->isActive())
                        <div class="flex gap-3 rounded-2xl border border-danger/30 bg-danger/5 p-5 text-sm leading-relaxed text-ink-600" role="alert">
                            <x-ui.icon name="circle-x" :size="20" class="text-danger" />
                            <p>Tài khoản affiliate của bạn đang bị tạm khoá: link giới thiệu không còn ghi nhận đơn mới. Liên hệ Tâm Minh nếu bạn cần hỗ trợ.</p>
                        </div>
                    @endunless

                    {{-- Thống kê --}}
                    <section class="grid gap-3 sm:grid-cols-3" aria-label="Thống kê hoa hồng">
                        @foreach ([
                            [\App\AffiliateCommissionStatus::Pending, 'Đơn chưa hoàn tất'],
                            [\App\AffiliateCommissionStatus::Approved, 'Tâm Minh sẽ chuyển khoản cho bạn'],
                            [\App\AffiliateCommissionStatus::Paid, 'Tổng đã nhận'],
                        ] as [$status, $hint])
                            <div class="rounded-2xl border border-cream-300 bg-white p-5 shadow-soft-sm">
                                <div class="text-xs font-semibold tracking-eyebrow text-ink-400 uppercase">{{ $status->label() }}</div>
                                <div @class(['mt-2 font-serif text-2xl font-semibold', 'text-copper-600' => $status === \App\AffiliateCommissionStatus::Approved, 'text-ink-600' => $status !== \App\AffiliateCommissionStatus::Approved])>{{ $money($totals[$status->value]) }}</div>
                                <div class="mt-1 text-xs text-ink-300">{{ $hint }}</div>
                            </div>
                        @endforeach
                    </section>

                    {{-- Link giới thiệu --}}
                    <section class="{{ $card }}" aria-labelledby="affiliate-links">
                        <div class="mb-1 flex flex-wrap items-center justify-between gap-3">
                            <h2 id="affiliate-links" class="font-serif text-xl font-semibold text-ink-600">Link giới thiệu của bạn</h2>
                            <span class="rounded-full bg-copper-50 px-3 py-1 font-mono text-sm font-semibold tracking-wider text-copper-700">Mã: {{ $affiliate->code }}</span>
                        </div>
                        <p class="mb-5 text-sm text-ink-400">Hoa hồng {{ $productPercent }}% vật phẩm · {{ $analysisPercent }}% xem tướng online · link hiệu lực {{ $cookieDays }} ngày.</p>

                        <div class="flex flex-col gap-4">
                            @foreach ([
                                ['Trang chủ', $affiliate->referralUrl()],
                                ['Xem tướng khuôn mặt online', $affiliate->referralUrl('home', [], 'xem-tuong')],
                                ['Vật phẩm phong thủy', $affiliate->referralUrl('products.index')],
                            ] as [$label, $url])
                                <div class="tm-field">
                                    <span class="tm-label">{{ $label }}</span>
                                    <div class="flex gap-2">
                                        <input type="text" value="{{ $url }}" class="tm-input min-w-0 flex-1 font-mono text-sm" readonly aria-label="Link giới thiệu {{ mb_strtolower($label) }}" />
                                        <x-ui.button variant="outline" icon="copy" :icon-size="15" :data-copy="$url" data-copy-message="Đã sao chép link giới thiệu.">
                                            <span class="hidden sm:inline">Sao chép</span>
                                        </x-ui.button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <p class="mt-4 text-xs leading-relaxed text-ink-300">Mẹo: thêm <span class="font-mono text-ink-500">?ref={{ $affiliate->code }}</span> vào cuối link bất kỳ trang nào của Tâm Minh (bài viết, vật phẩm cụ thể…) để giới thiệu đúng trang đó.</p>
                    </section>

                    {{-- Hoa hồng --}}
                    <section class="{{ $card }}" aria-labelledby="affiliate-commissions">
                        <h2 id="affiliate-commissions" class="mb-4 font-serif text-xl font-semibold text-ink-600">Lịch sử hoa hồng</h2>

                        @if ($commissions->isEmpty())
                            <p class="rounded-xl bg-cream-50 p-5 text-sm text-ink-400">Chưa có đơn nào qua link của bạn. Hãy chia sẻ link để bắt đầu nhận hoa hồng!</p>
                        @else
                            <div class="-mx-5 overflow-x-auto sm:mx-0">
                                <table class="w-full min-w-[560px] text-left text-sm">
                                    <thead class="border-b border-cream-300 text-xs tracking-wide text-ink-400 uppercase">
                                        <tr>
                                            <th class="py-2.5 pr-3 pl-5 font-semibold sm:pl-0">Ngày</th>
                                            <th class="px-3 py-2.5 font-semibold">Đơn</th>
                                            <th class="px-3 py-2.5 text-right font-semibold">Giá trị</th>
                                            <th class="px-3 py-2.5 text-right font-semibold">Hoa hồng</th>
                                            <th class="py-2.5 pr-5 pl-3 text-right font-semibold sm:pr-0">Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-cream-200">
                                        @foreach ($commissions as $commission)
                                            <tr>
                                                <td class="py-3 pr-3 pl-5 whitespace-nowrap text-ink-400 sm:pl-0">{{ $commission->created_at->format('d/m/Y') }}</td>
                                                <td class="px-3 py-3 font-medium whitespace-nowrap text-ink-600">{{ $commission->sourceLabel() }}</td>
                                                <td class="px-3 py-3 text-right whitespace-nowrap text-ink-500">{{ $money($commission->base_amount) }}</td>
                                                <td class="px-3 py-3 text-right whitespace-nowrap">
                                                    <span class="font-semibold text-copper-600">{{ $money($commission->amount) }}</span>
                                                    <span class="text-xs text-ink-300">({{ $commission->rate_percent }}%)</span>
                                                </td>
                                                <td class="py-3 pr-5 pl-3 text-right sm:pr-0">
                                                    <span @class([
                                                        'rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap',
                                                        'bg-warning/10 text-warning' => $commission->status === \App\AffiliateCommissionStatus::Pending,
                                                        'bg-copper-50 text-copper-700' => $commission->status === \App\AffiliateCommissionStatus::Approved,
                                                        'bg-success/10 text-success' => $commission->status === \App\AffiliateCommissionStatus::Paid,
                                                        'bg-cream-200 text-ink-400' => $commission->status === \App\AffiliateCommissionStatus::Cancelled,
                                                    ])>{{ $commission->status->label() }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <x-ui.pagination :paginator="$commissions" class="mt-5" />
                        @endif
                    </section>

                    {{-- Tài khoản nhận tiền --}}
                    <section class="{{ $card }}" aria-labelledby="affiliate-bank">
                        <h2 id="affiliate-bank" class="font-serif text-xl font-semibold text-ink-600">Tài khoản nhận hoa hồng</h2>
                        <p class="mt-1.5 text-sm text-ink-400">Tâm Minh chuyển khoản hoa hồng "Chờ chi trả" vào tài khoản này.</p>

                        @unless ($affiliate->hasBankAccount())
                            <p class="mt-4 rounded-lg border border-warning/30 bg-warning/5 px-4 py-3 text-sm text-ink-600">Bạn chưa khai báo tài khoản ngân hàng - hãy bổ sung để nhận hoa hồng.</p>
                        @endunless

                        <form method="POST" action="{{ route('account.affiliate.update') }}" class="mt-5 grid gap-5 sm:grid-cols-2" data-submit-loading novalidate>
                            @csrf
                            @method('PUT')

                            <x-ui.field label="Ngân hàng" for="affiliate-bank-name" name="bank_name">
                                <x-ui.input id="affiliate-bank-name" name="bank_name" :value="$affiliate->bank_name" placeholder="VD: Vietcombank" required maxlength="100" />
                            </x-ui.field>

                            <x-ui.field label="Số tài khoản" for="affiliate-bank-number" name="bank_account_number">
                                <x-ui.input id="affiliate-bank-number" name="bank_account_number" :value="$affiliate->bank_account_number" inputmode="numeric" placeholder="0123456789" required maxlength="30" />
                            </x-ui.field>

                            <x-ui.field label="Tên chủ tài khoản" for="affiliate-bank-holder" name="bank_account_name" class="sm:col-span-2">
                                <x-ui.input id="affiliate-bank-holder" name="bank_account_name" :value="$affiliate->bank_account_name" placeholder="NGUYEN VAN AN" required maxlength="100" class="uppercase" />
                            </x-ui.field>

                            <div class="sm:col-span-2">
                                <x-ui.button type="submit" data-loading-text="Đang lưu…">Lưu tài khoản</x-ui.button>
                            </div>
                        </form>
                    </section>

                    {{-- Quy định --}}
                    <section class="{{ $card }}" aria-labelledby="affiliate-rules">
                        <h2 id="affiliate-rules" class="mb-3 font-serif text-xl font-semibold text-ink-600">Cách tính hoa hồng</h2>
                        <ul class="flex flex-col gap-2 text-sm leading-relaxed text-ink-400">
                            @foreach ($rules as $rule)
                                <li class="flex gap-2"><x-ui.icon name="check" :size="16" class="mt-0.5 text-copper-500" />{{ $rule }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
