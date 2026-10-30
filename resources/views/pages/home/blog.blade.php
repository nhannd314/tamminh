<section class="border-t border-cream-300 bg-cream-100 py-14 lg:py-20">
    <div class="tm-container">
        <x-ui.section-heading eyebrow="Kiến thức tướng số" title="Bài viết & tư liệu" accent="mới nhất" size="md" class="mb-10 max-w-[880px] gap-5" data-reveal>
            Mỗi tuần, các Thầy của Tâm Minh chia sẻ một bài luận sâu về tướng số, tử vi và phong thủy - viết bằng ngôn ngữ gần gũi, ứng dụng được vào đời sống hiện đại.
        </x-ui.section-heading>

        <div class="grid grid-cols-1 gap-[18px] sm:grid-cols-2 lg:grid-cols-3 lg:gap-[22px]" data-reveal-stagger>
            @foreach ($posts as $post)
                <x-cards.post :post="$post" :class="$loop->last && $loop->odd ? 'sm:max-lg:col-span-2' : ''" data-reveal />
            @endforeach
        </div>

        <div class="mt-9 flex justify-center">
            <x-ui.button href="#" variant="outline" pill trailing-icon="arrow" class="gap-2.5 uppercase tracking-wide" data-toast="Mở trang bài viết.">
                Xem tất cả bài viết
            </x-ui.button>
        </div>
    </div>
</section>
