@php
    $palettes = [
        'copper' => [50, 100, 200, 300, 400, 500, 600, 700],
        'navy' => [50, 100, 200, 300, 400, 500, 600, 700],
        'cream' => [50, 100, 200, 300, 400],
        'ink' => [100, 200, 300, 400, 500, 600],
    ];

    $icons = ['sparkle', 'face', 'shield', 'bolt', 'yinyang', 'book', 'upload', 'arrow', 'chevron-down', 'message', 'messenger', 'user', 'users', 'headset', 'mountain', 'star', 'check', 'send', 'plus', 'phone', 'mail', 'pin', 'x', 'circle-x', 'circle-check'];
@endphp

<x-layouts.app title="Design System">
    @push('head')
        <meta name="robots" content="noindex">
    @endpush

    <div class="tm-container flex flex-col gap-16 py-14">
        <x-ui.section-heading eyebrow="Tâm Minh UI" title="Design" accent="system" rule>
            Tokens và Blade components dùng chung cho toàn bộ website. Nguồn: <code class="text-copper-700">resources/css/app.css</code> và <code class="text-copper-700">resources/views/components</code>.
        </x-ui.section-heading>

        {{-- Colors --}}
        <section class="flex flex-col gap-6">
            <h2 class="tm-overline text-copper-700">Màu sắc</h2>
            @foreach ($palettes as $palette => $shades)
                <div class="grid grid-cols-4 gap-3 sm:grid-cols-8">
                    @foreach ($shades as $shade)
                        <div class="flex flex-col gap-1.5">
                            <div class="h-14 rounded-lg border border-cream-300" style="background: var(--color-{{ $palette }}-{{ $shade }})"></div>
                            <div class="text-xs text-ink-400">{{ $palette }}-{{ $shade }}</div>
                        </div>
                    @endforeach
                </div>
            @endforeach
            <div class="grid grid-cols-4 gap-3 sm:grid-cols-8">
                @foreach (['success', 'warning', 'danger', 'info'] as $status)
                    <div class="flex flex-col gap-1.5">
                        <div class="h-14 rounded-lg" style="background: var(--color-{{ $status }})"></div>
                        <div class="text-xs text-ink-400">{{ $status }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Typography --}}
        <section class="flex flex-col gap-6">
            <h2 class="tm-overline text-copper-700">Typography</h2>
            <div class="tm-panel flex flex-col gap-5">
                <div class="tm-display">Display<span class="tm-accent"> tướng mạo</span></div>
                <div class="tm-heading">Heading<span class="tm-accent"> tướng số</span></div>
                <div class="tm-card-title">Card title — Lora 600</div>
                <p class="tm-lead">Lead — Manrope. Kế thừa tinh hoa nhân tướng học phương Đông, Tâm Minh vẽ nên tấm bản đồ chi tiết về con người bạn.</p>
                <p class="tm-card-desc">Muted description — ink-300.</p>
                <div class="flex flex-wrap items-center gap-4">
                    <x-ui.eyebrow>Eyebrow</x-ui.eyebrow>
                    <span class="tm-overline text-copper-700">Overline</span>
                    <a href="#" class="tm-link text-sm">Text link →</a>
                    <span class="tm-rule"></span>
                </div>
                <blockquote class="tm-quote">"Tướng tự tâm sinh, tâm tự tướng diệt."</blockquote>
            </div>
        </section>

        {{-- Buttons --}}
        <section class="flex flex-col gap-6">
            <h2 class="tm-overline text-copper-700">Buttons</h2>
            <div class="tm-panel flex flex-col gap-5">
                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.button>Primary</x-ui.button>
                    <x-ui.button variant="secondary">Secondary</x-ui.button>
                    <x-ui.button variant="outline">Outline</x-ui.button>
                    <x-ui.button variant="ghost">Ghost</x-ui.button>
                    <x-ui.button variant="soft">Soft</x-ui.button>
                    <x-ui.button disabled>Disabled</x-ui.button>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.button size="sm" icon="sparkle" :icon-size="14">Small</x-ui.button>
                    <x-ui.button trailing-icon="arrow">Medium</x-ui.button>
                    <x-ui.button size="lg" trailing-icon="arrow" class="uppercase tracking-wide">Large</x-ui.button>
                    <x-ui.button variant="outline" pill trailing-icon="arrow">Pill</x-ui.button>
                    <x-ui.button variant="soft" size="icon" aria-label="User"><x-ui.icon name="user" :size="20" /></x-ui.button>
                </div>
                <x-ui.button size="lg" block trailing-icon="arrow" class="uppercase tracking-wide">Block</x-ui.button>
            </div>
        </section>

        {{-- Forms --}}
        <section class="flex flex-col gap-6">
            <h2 class="tm-overline text-copper-700">Form controls</h2>
            <div class="grid gap-5 lg:grid-cols-2">
                <x-ui.form-card title="Form card">
                    <div class="space-y-4">
                        <x-ui.field label="Input" for="ds-input">
                            <x-ui.input id="ds-input" placeholder="Placeholder" />
                        </x-ui.field>
                        <x-ui.field label="Select">
                            <x-ui.select placeholder="Chọn gói" :options="['basic' => 'Cơ bản', 'premium' => 'Chuyên sâu']" />
                        </x-ui.field>
                        <x-ui.field label="Date select">
                            <x-ui.date-select name="ds_birth" />
                        </x-ui.field>
                        <x-ui.field label="Time select">
                            <x-ui.time-select name="ds_birth" />
                        </x-ui.field>
                        <x-ui.field label="Radio">
                            <div class="flex gap-7">
                                <x-ui.radio name="ds_gender" value="male" checked>Nam</x-ui.radio>
                                <x-ui.radio name="ds_gender" value="female">Nữ</x-ui.radio>
                            </div>
                        </x-ui.field>
                        <x-ui.field label="Checkbox">
                            <x-ui.checkbox name="ds_agree">Tôi đồng ý với <a href="#" class="tm-link">Điều khoản dịch vụ</a></x-ui.checkbox>
                        </x-ui.field>
                    </div>
                </x-ui.form-card>
                <x-ui.form-card title="Selection">
                    <div class="space-y-4">
                        <x-ui.field label="Radio card">
                            <div class="grid gap-2.5 sm:grid-cols-2">
                                <x-ui.radio-card name="ds_pkg" value="basic" title="Cơ bản" description="Miễn phí" highlight checked />
                                <x-ui.radio-card name="ds_pkg" value="premium" title="Chuyên sâu" description="Luận giải chi tiết cùng Thầy" />
                            </div>
                        </x-ui.field>
                        <x-ui.field label="File drop">
                            <x-ui.file-drop name="ds_file" hint="(PNG, JPG)" />
                        </x-ui.field>
                    </div>
                </x-ui.form-card>
            </div>
        </section>

        {{-- Cards --}}
        <section class="flex flex-col gap-6">
            <h2 class="tm-overline text-copper-700">Cards</h2>
            <div class="grid gap-5 md:grid-cols-3">
                <x-ui.card number="01" title="Hover card" description="tm-card — nâng nhẹ khi hover." />
                <x-ui.card :hover="false" number="02" title="Static panel" description="tm-panel — không hiệu ứng." />
                <x-ui.card title="With callout" description="Callout dùng cho ghi chú nổi bật.">
                    <div class="tm-callout">
                        <x-ui.icon name="sparkle" :size="16" class="mt-px text-copper-500" />
                        <p class="text-sm text-ink-500">Tam đình cân đối báo hiệu cuộc đời hanh thông.</p>
                    </div>
                </x-ui.card>
            </div>
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                <x-cards.expert :expert="['glyph' => '明', 'name' => 'Thầy Minh Quang', 'role' => 'Chuyên gia tướng số', 'years' => 30, 'sessions' => 1287, 'photo' => null]" />
                <x-cards.review :review="['rating' => 4, 'quote' => 'Phần lời khuyên cải vận rất thực tế.', 'name' => 'Phạm Đức Anh', 'role' => 'Lập trình viên · Hà Nội', 'service' => 'Luận giải nhân tướng', 'avatar' => 'Đ', 'tone' => 'navy']" />
                <x-cards.post :post="['category' => 'Tử vi', 'glyph' => '紫', 'tone' => 'navy', 'title' => 'Năm Bính Ngọ 2026: vận khí 12 con giáp', 'excerpt' => 'Lưu niên 2026 đem theo sự dịch chuyển của Thái Tuế, Tam Tai và Kim Lâu.', 'author' => 'Thầy Huyền Lâm', 'date' => '08 thg 5, 2026', 'minutes' => 12]" />
            </div>
        </section>

        {{-- Atoms --}}
        <section class="flex flex-col gap-6">
            <h2 class="tm-overline text-copper-700">Atoms</h2>
            <div class="tm-panel flex flex-wrap items-center gap-6">
                <x-ui.glyph>金</x-ui.glyph>
                <x-ui.glyph tone="navy" size="md">神</x-ui.glyph>
                <x-ui.glyph tone="ring" size="lg">目</x-ui.glyph>
                <x-ui.icon-ring icon="face" size="sm" />
                <x-ui.icon-ring icon="shield" size="md" />
                <x-ui.icon-ring icon="bolt" />
                <x-ui.avatar>L</x-ui.avatar>
                <x-ui.avatar tone="navy">T</x-ui.avatar>
                <x-ui.stars :rating="4" />
                <div class="flex gap-2.5 rounded-full bg-navy-500 p-2">
                    <x-ui.social-button network="facebook" />
                    <x-ui.social-button network="zalo" />
                </div>
            </div>
            <div class="tm-panel grid grid-cols-5 gap-4 text-ink-400 sm:grid-cols-9 lg:grid-cols-13">
                @foreach ($icons as $icon)
                    <div class="flex flex-col items-center gap-1.5" title="{{ $icon }}">
                        <x-ui.icon :name="$icon" />
                        <span class="text-[10px] text-ink-300">{{ $icon }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Feedback --}}
        <section class="flex flex-col gap-6">
            <h2 class="tm-overline text-copper-700">Accordion & toast</h2>
            <x-ui.accordion>
                <x-ui.accordion-item question="Accordion mở sẵn" open>Nội dung câu trả lời.</x-ui.accordion-item>
                <x-ui.accordion-item question="Accordion đóng">Nội dung câu trả lời.</x-ui.accordion-item>
            </x-ui.accordion>
            <div>
                <x-ui.button variant="secondary" data-toast="Đây là một thông báo toast.">Hiện toast</x-ui.button>
            </div>
        </section>
    </div>
</x-layouts.app>
