<x-layouts.app title="Đổi mật khẩu" description="Đổi mật khẩu đăng nhập tài khoản Tâm Minh.">
    @push('head')
        <meta name="robots" content="noindex, nofollow">
    @endpush

    <div class="tm-container max-w-[1040px] pt-6 pb-16">
        <x-ui.breadcrumb class="mb-6" :items="['Trang chủ' => route('home'), 'Tài khoản' => route('account.show'), 'Đổi mật khẩu' => null]" />

        <div class="grid items-start gap-6 lg:grid-cols-[320px_minmax(0,1fr)] lg:gap-8">
            <x-account.sidebar :user="$user" />

            <section class="rounded-2xl border border-cream-300 bg-white p-5 shadow-soft-sm sm:p-7" aria-labelledby="account-password">
                <h1 id="account-password" class="font-serif text-2xl font-semibold text-ink-600">{{ $user->hasPassword() ? 'Đổi mật khẩu' : 'Đặt mật khẩu' }}</h1>
                <p class="mt-1.5 text-sm text-ink-400">
                    @if ($user->hasPassword())
                        Sau khi đổi, các thiết bị khác đang ghi nhớ đăng nhập sẽ phải đăng nhập lại.
                    @else
                        Bạn đang đăng nhập bằng Google. Đặt mật khẩu để có thể đăng nhập bằng email <b class="font-semibold text-ink-600">{{ $user->email }}</b>.
                    @endif
                </p>

                <form method="POST" action="{{ route('account.password.update') }}" class="mt-6 flex max-w-[460px] flex-col gap-5" data-submit-loading novalidate>
                    @csrf
                    @method('PUT')

                    @if ($user->hasPassword())
                        <x-ui.field label="Mật khẩu hiện tại" for="account-current-password" name="current_password">
                            <x-ui.password-input id="account-current-password" name="current_password" autocomplete="current-password" required />
                        </x-ui.field>
                    @endif

                    <x-ui.field label="Mật khẩu mới" for="account-new-password" name="password">
                        <x-ui.password-input id="account-new-password" name="password" autocomplete="new-password" placeholder="Tối thiểu 8 ký tự" required strength />
                    </x-ui.field>

                    <x-ui.field label="Nhập lại mật khẩu mới" for="account-new-password-confirmation" name="password_confirmation">
                        <x-ui.password-input id="account-new-password-confirmation" name="password_confirmation" autocomplete="new-password" placeholder="Nhập lại mật khẩu mới" required />
                    </x-ui.field>

                    <div class="flex flex-wrap items-center gap-3 border-t border-cream-300 pt-5">
                        <x-ui.button type="submit" data-loading-text="Đang lưu…">{{ $user->hasPassword() ? 'Đổi mật khẩu' : 'Đặt mật khẩu' }}</x-ui.button>
                        <x-ui.button :href="route('account.show')" variant="ghost">Huỷ</x-ui.button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-layouts.app>
