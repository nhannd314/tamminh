<x-mail::message>
# Có lịch xem chỉ tay mới

Khách vừa đặt lịch xem chỉ tay trên website, vui lòng liên hệ xác nhận sớm.

<x-mail::table>
| Thông tin | Chi tiết |
|:--|:--|
| Họ và tên | **{{ $booking->name }}** |
| Giới tính | {{ $booking->gender->label() }} |
| Ngày sinh | {{ $booking->birth_date->format('d/m/Y') }} |
| Số điện thoại | **{{ $booking->phone }}** |
| Gói xem | {{ $booking->package->label() }} |
| Thời gian đặt | {{ $booking->created_at->format('H:i d/m/Y') }} |
</x-mail::table>

<x-mail::button :url="$adminUrl">
Xem trong trang quản trị
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
