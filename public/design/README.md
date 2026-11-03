# Handoff: Tâm Minh – Chi tiết chuyên gia, Vật phẩm phong thủy, Chi tiết sản phẩm

## Overview
Three pages for the Tâm Minh platform (Vietnamese face reading / palm reading / feng shui):
1. **Chi tiết chuyên gia** – expert profile with contact info (phone, email, address, hours), TikTok/Facebook channels, and a primary "Nhắn tin qua Zalo" CTA.
2. **Vật phẩm phong thủy** – product listing filtered by bản mệnh (five elements) and category, with sorting and add-to-cart.
3. **Chi tiết sản phẩm** – product detail page: gallery, variants, quantity, cart/buy, tabs, reviews, related products.

## About the Design Files
The HTML files in this bundle are **design references** – prototypes showing intended look and behavior, not production code. Recreate them in the target codebase's environment (React/Next, Vue, etc.) using its patterns. If no codebase exists, Next.js + Tailwind CSS is a natural fit since the prototypes are already authored in Tailwind utility classes.

Prototypes use Tailwind Play CDN with an inline `tailwind.config` (in `<head>` of each file) and `src/app-tailwind.js` for shared header nav, footer columns, trust bar, Lucide icon hydration (`data-ic="name" data-sz="px"`), toasts (`data-toast="message"`) and AOS scroll reveal. `src/image-slot.js` is a prototype-only drag-and-drop image placeholder – replace every `<image-slot>` with a real `<img>`.

## Fidelity
**High-fidelity.** Final colors, typography, spacing and interactions. Recreate pixel-accurately. All copy, prices, phone numbers, handles and reviews are placeholder data.

## Shared layout
- Body bg `cream-100 #FAF6EC`, text `ink-500 #2C2924`, font Manrope; headings Lora (serif).
- Container: `max-width 1320px`, padding-x 16px (24px ≥640px).
- Sticky header (white/95 + backdrop blur, bottom border cream-300), mobile drawer ≤1024px, navy footer – identical to `Trang chủ (Tailwind).html` / `Kết quả phân tích.html`.
- Breadcrumb at top of main: 14px, ink-300, chevron separators, current item ink-600 medium.
- Card pattern: `rounded-2xl (16px)`, 1px border cream-300 `#E8DCC0`, white bg, shadow-soft-sm, padding 24px (32px ≥640px).
- Section headings: Lora 24px semibold ink-600 (30px ≥640 where noted).
- Body copy is **16px minimum** (text-base, line-height 1.625 "relaxed"); meta text 14px.

## Screen 1 – Chi tiết chuyên gia
**Layout:** grid `lg: 380px | 1fr`, gap 24/32px. Below lg stacks; at md the contact + social cards sit 2-up.
**Left aside** (sticky `top: 96px` at lg):
- Profile card: photo (4:3, object-top; at sm–md photo 240px left beside info) with "Đang nhận lịch" pill (white/95, green 8px dot `emerald-500`). Name Lora 30px semibold, subtitle 16px ink-400.
- **Zalo CTA**: full width, py 16px, bg `#0068FF` (hover `#0054D1`), white 16px semibold, Zalo glyph 20px. Link `https://zalo.me/<phone>` target _blank.
- 2-col: "Gọi điện" (`tel:`; outline copper-300 border, copper-700 text, hover fill copper-400) + "Đặt lịch" (navy-500 bg).
- Contact card: header 14px uppercase tracking .14em; rows with 40px copper-50 circle icon (phone/mail/pin/clock), label 14px ink-300, value 16px semibold ink-600; rows are links (tel:, mailto:, #office), divided by cream-200.
- Social card: rows with 44px circle (TikTok bg ink-600; Facebook `#1877F2`), name 16px semibold, handle 14px ink-300, "Theo dõi" copper-600. External links.
**Main column:**
- Profile intro: eyebrow pill "✦ Hồ sơ chuyên gia", H2, 3-stat `<dl>` (Kinh nghiệm / Buổi xem / Đánh giá – Lora 24–30px copper-600, separated by vertical borders), 2 paragraphs, skill chips (rounded-full, copper-50 bg, copper-200 border, copper-700 14px).
- Dịch vụ & chi phí: rows (name Lora 20px, desc 16px ink-400, duration with clock icon, price Lora 20px copper-600); bottom cream-100 box with secondary Zalo button.
- Văn phòng tiếp khách (`#office`): address, map/photo 16:7 rounded-xl, "Chỉ đường" → Google Maps.
- Reviews: 2-col at md, cards cream-50 with 5 stars copper-400.
**Mobile (<lg):** fixed bottom bar: 48px square call button + Zalo button filling remaining width. Main has extra bottom padding (96px) so content isn't covered.

## Screen 2 – Vật phẩm phong thủy (listing)
- Intro card: eyebrow, H1 Lora 30/36px, paragraph; right: outline button "Chưa biết mệnh? Xem tướng miễn phí →" linking to analysis flow.
- **Bản mệnh chips** (toggle, single-select, click again to clear): rounded-full, border cream-300, 16px; color dot 12px – Kim `#C9B68C`, Mộc `#4E7A4A`, Thủy `#25446F`, Hỏa `#B5462F`, Thổ `#946821`. Active: bg/border navy-500, text cream-50, dot gets 2px cream ring.
- **Toolbar** (border-y cream-300): category tabs (Tất cả, Linh vật, Vòng tay, Đá phong thủy, Tranh & thư pháp, Cây phong thủy) – horizontal scroll with hidden scrollbar; active = copper-50 bg + copper-700 text. Right: result count + sort select (Phổ biến / Giá thấp → cao / Giá cao → thấp). Below lg the count/sort row stacks under tabs.
- **Grid:** 2 cols / 3 at ≥640 / 4 at ≥1024; gap 12px (20px ≥640).
- Product card: square image (optional badge "Bán chạy"/"Mới" navy pill top-left), mệnh dots line 14px, name Lora 18px, use 16px ink-400, price 18px semibold copper-600, "+ Thêm" copper button (min-h 40px). Hover: lift 2px, shadow-soft-md. Image + name link to product detail.
- Empty state: "Chưa có vật phẩm phù hợp. Xóa bộ lọc".
- 3 trust boxes (khai quang / kiểm định / tư vấn Zalo).
- Floating cart pill bottom-right (navy, h 56px, copper count badge).

## Screen 3 – Chi tiết sản phẩm
**Layout:** 2 cols from md (`lg: 1.1fr | 1fr`, gap 32/48px). Gallery column sticky at md (`top 96px`).
- Gallery: main square image rounded-2xl + 4 thumbnails grid; active thumb 2px copper-500 outline offset 2px.
- Info: mệnh pills ("Hợp mệnh Kim", nowrap) + SKU; H1 Lora 30/36px; rating row (5 stars, 4.9, "86 đánh giá" anchor to #pd-reviews, "Đã bán 420"); price Lora 30px copper-600 + struck compare price 18px ink-300; "Tạm tính" line appears when qty ≥ 2.
- Options: Kích thước (Nhỏ 3cm 1.290.000 / Vừa 5cm 1.890.000 / Lớn 8cm 2.990.000; compare 1.590.000 / 2.290.000 / 3.590.000), Kiểu dáng (Mặt dây chuyền / Tượng để bàn). Option button selected: border copper-500, bg copper-50, text copper-700.
- Khắc tên input (max 12 chars). Quantity stepper h 48px (1–stock 14) + stock text.
- Buttons (≥640): "Thêm vào giỏ" outline + "Mua ngay" copper, h 48px. Zalo advice card. 4 guarantees in 2-col list.
- Tabs card: Ý nghĩa / Cách đặt & đeo / Thông số – active tab ink-600 with 2px copper-400 bottom border.
- Reviews (2 cards), related products (4-up grid).
**Mobile (<640):** fixed bottom bar with Thêm vào giỏ + Mua ngay.

## Interactions & State
- Expert: all links native (tel/mailto/zalo/maps). "Đặt lịch" → booking flow (not built).
- Listing state: `{ menh: string|null, category: string, sort: 'pop'|'asc'|'desc' }`, client-side filter; `cartCount`. Product data: `{ name, category, menh[], price, desc, tag? }` → fetch from API.
- Detail state: `{ size, style, engraving, qty, activeImage, activeTab }`; price = size price; subtotal = price × qty. Add to cart toast: "Đã thêm {qty} × {name} ({size}) vào giỏ hàng."
- Toast: navy pill bottom-center, fades/slides in 300ms, ~2.4s.
- Hover transitions 200–300ms; card hover translateY(-2px).

## Design Tokens
- copper 50 `#FBF4E5` 100 `#F4E2BC` 200 `#E9C886` 300 `#DAA85A` 400 `#C89544` 500 `#B6822F` 600 `#946821` 700 `#6E4D17`
- navy 50 `#EAEEF5` 400 `#25446F` 500 `#15325E` 600 `#112A4F` 700 `#0B1E3C`
- cream 50 `#FDFBF6` 100 `#FAF6EC` 200 `#F2EBD8` 300 `#E8DCC0` 400 `#C9B68C`
- ink 100 `#DCD6CA` 200 `#B6AEA0` 300 `#847C70` 400 `#565049` 500 `#2C2924` 600 `#1A1814`
- Brand-external: Zalo `#0068FF`/`#0054D1`, Facebook `#1877F2`
- Type: Lora 400–700 (headings), Manrope 300–800 (UI/body). Scale (root 16px): xs 12, sm 14, base 16, lg 18, xl 20, 2xl 24, 3xl 30, 4xl 36, 5xl 48.
- Radius: md 6px (buttons/inputs), lg 8px, xl 12px (product cards), 2xl 16px (section cards), full (pills).
- Shadows: soft-sm `0 2px 6px rgba(70,50,20,.06)`, soft-md `0 8px 24px rgba(70,50,20,.08)`, soft-lg `0 20px 48px rgba(70,50,20,.10)`.
- Breakpoints: Tailwind defaults (sm 640, md 768, lg 1024, xl 1280).

## Assets
- `assets/logo-mark.svg` – brand mark.
- `assets/thay-tuong-so.png` – expert portrait (placeholder photo).
- Icons: Lucide (ISC) – paths inlined in `src/app-tailwind.js`; use `lucide-react` in production. TikTok/Facebook/Zalo glyphs are inline SVGs in the HTML.
- All product images, office/map image are empty placeholders – real photography needed.

## Files
- `Chi tiết chuyên gia.html`
- `Vật phẩm phong thủy.html` (listing logic in inline `<script>` at bottom)
- `Chi tiết sản phẩm.html` (detail logic in inline `<script>` at bottom)
- `src/app-tailwind.js` – shared header/footer/icons/toast
- `src/image-slot.js` – prototype-only placeholder component
