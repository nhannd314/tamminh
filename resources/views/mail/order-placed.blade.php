<x-mail::message>
# Đơn hàng mới {{ $order->code }}

**{{ $order->payment_method->label() }}**{{ $order->payment_method === \App\OrderPaymentMethod::Online ? ' - kiểm tra đã nhận tiền trước khi giao.' : ' - thu tiền khi giao.' }}

<x-mail::table>
| Vật phẩm | SL | Thành tiền |
|:--|:--:|--:|
@foreach ($items as $item)
| {{ $item->product_name }}{{ $item->optionsLabel() ? ' ('.$item->optionsLabel().')' : '' }} | {{ $item->quantity }} | {{ $money($item->subtotal) }} |
@endforeach
| Tạm tính | | {{ $money($order->subtotal) }} |
| Phí giao hàng | | {{ $order->shipping_fee > 0 ? $money($order->shipping_fee) : 'Miễn phí' }} |
| **Tổng cộng** | | **{{ $money($order->total) }}** |
</x-mail::table>

<x-mail::table>
| Người nhận | |
|:--|:--|
| Họ và tên | **{{ $order->customer_name }}** |
| Số điện thoại | **{{ $order->phone }}** |
| Địa chỉ | {{ $order->address }} |
@if ($order->note)
| Ghi chú | {{ $order->note }} |
@endif
</x-mail::table>

<x-mail::button :url="$adminUrl">
Xem trong trang quản trị
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
