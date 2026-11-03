<x-layouts.app title="Thông tin cá nhân" description="Cập nhật thông tin cá nhân và địa chỉ giao hàng tại Tâm Minh.">
    @push('head')
        <meta name="robots" content="noindex, nofollow">
    @endpush

    <div class="tm-container max-w-[1040px] pt-6 pb-16">
        <x-ui.breadcrumb class="mb-6" :items="['Trang chủ' => route('home'), 'Tài khoản' => route('account.show'), 'Thông tin cá nhân' => null]" />

        <div class="grid items-start gap-6 lg:grid-cols-[320px_minmax(0,1fr)] lg:gap-8">
            <x-account.sidebar :user="$user" />

            <section class="rounded-2xl border border-cream-300 bg-white p-5 shadow-soft-sm sm:p-7" aria-labelledby="account-edit">
                <h1 id="account-edit" class="font-serif text-2xl font-semibold text-ink-600">Thông tin cá nhân</h1>
                <p class="mt-1.5 text-sm text-ink-400">Số điện thoại và địa chỉ được điền sẵn khi bạn đặt vật phẩm.</p>

                <form method="POST" action="{{ route('account.update') }}" class="mt-6 flex flex-col gap-5" data-submit-loading novalidate>
                    @csrf
                    @method('PUT')

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.field label="Họ và tên" for="account-name" name="name">
                            <x-ui.input id="account-name" name="name" :value="$user->name" autocomplete="name" placeholder="Nguyễn Văn An" required maxlength="100" />
                        </x-ui.field>

                        <div class="tm-field">
                            <label for="account-email" class="tm-label">Email <span class="font-normal text-ink-200">(không đổi được)</span></label>
                            <input id="account-email" type="email" value="{{ $user->email }}" class="tm-input cursor-not-allowed bg-cream-100 text-ink-400" disabled />
                        </div>

                        <x-ui.field label="Số điện thoại" for="account-phone" name="phone">
                            <x-ui.input id="account-phone" type="tel" name="phone" :value="$user->phone" inputmode="tel" autocomplete="tel" placeholder="09xx xxx xxx" maxlength="20" />
                        </x-ui.field>
                    </div>

                    <x-ui.field label="Địa chỉ giao hàng" for="account-address" name="address">
                        <x-ui.input id="account-address" name="address" :value="$user->address" autocomplete="street-address" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố" maxlength="500" />
                    </x-ui.field>

                    <x-ui.checkbox name="newsletter" :checked="$errors->any() ? (bool) old('newsletter') : $user->newsletter_opt_in">Nhận bài viết phong thủy và ưu đãi qua email</x-ui.checkbox>

                    <div class="flex flex-wrap items-center gap-3 border-t border-cream-300 pt-5">
                        <x-ui.button type="submit" data-loading-text="Đang lưu…">Lưu thay đổi</x-ui.button>
                        <x-ui.button :href="route('account.show')" variant="ghost">Huỷ</x-ui.button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-layouts.app>
