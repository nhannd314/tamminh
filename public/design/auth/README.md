# Handoff: Auth — Đăng nhập, Đăng ký, Quên mật khẩu (Tâm Minh)

## Overview
Three authentication screens for **Tâm Minh** (Vietnamese face-reading / feng-shui platform): Login, Register, Forgot/Reset password. All copy is Vietnamese and final.

## About the Design Files
The HTML files here are **design references** — prototypes showing intended look and behavior, not production code. Recreate them in the target codebase's environment (React/Next, Vue, etc.) using its existing patterns. If no environment exists, pick an appropriate framework (Next.js + Tailwind recommended, since the prototypes use Tailwind classes 1:1). All network actions (Google OAuth, submit, email sending) are **mocked** with toasts/timeouts — replace with real APIs.

## Fidelity
**High-fidelity.** Final colors, type, spacing, copy and interactions. Recreate pixel-perfectly.

## Shared Layout (all 3 screens)
- Root: `flex min-h-screen`, body bg `cream-100 #FAF6EC`, text `ink-500 #2C2924`.
- **Brand panel (left)** — only ≥1024px (`lg`). Width 44%, bg `navy-600 #112A4F`, padding 48px, flex column `justify-between`, `overflow-hidden`.
  - Background: `assets/zodiac-wheel.svg`, 720px wide, positioned bottom −224px / right −224px, opacity 0.14, rotates 360° over 60s linear infinite, pointer-events none.
  - Top: logo link → home. `logo-mark.svg` 48×48, gap 12px; "Tâm Minh" Lora 24px/600 `cream-50 #FDFBF6`; tagline "Hiểu mình - An tâm sống" 12px, tracking wide, `navy-200 #8898BA`, 4px top margin.
  - Middle (max-w 448px): copper rule 56×2px `copper-400 #C89544`, 24px below → quote Lora 30px/500 leading-snug `cream-50` → body 14px leading-relaxed `navy-200`, 16px top.
  - Bottom: "© 2026 Tâm Minh." 12px `navy-300 #4D6592`.
- **Form column (right)**: `flex-1`, centered both axes, padding 48px vertical / 16px (24px ≥640px) horizontal.
  - Mobile/tablet (<1024px) shows centered logo (48px mark + "Tâm Minh" Lora 24px/600 `ink-600`) 32px above card.
  - Card: white, border 1px `cream-300 #E8DCC0`, radius 10px, shadow `0 8px 24px rgba(70,50,20,.08)`, padding 24px (36px ≥640px). Max width: Login 420px, Register 460px, Forgot 420px.
  - Entry animation: fade-up (opacity 0→1, translateY 24px→0), 0.9s `cubic-bezier(.16,1,.3,1)`.
  - Footer link under card: 14px `ink-300 #847C70`, 24px top margin, link weight 600.

## Shared Components
- **Card heading**: Lora 24px/600 `ink-600 #1A1814`; subtitle 14px `ink-300`, 6px top.
- **Label**: 14px/500 `ink-500`. Field = column, gap 6px. Fields stacked gap 20px.
- **Input**: full width, radius 6px, border 1px `cream-300`, white bg, padding 12px 14px, 14px `ink-600`, placeholder `ink-200 #B6AEA0`. Focus: border `copper-400` + 2px ring `copper-400` @20%. Error: border `red-400`, ring red @20%; error text 12px `red-600`, shown under field.
- **Password eye toggle**: 32×32 button, absolute right 8px, vertically centered; input has right padding 44px. Icon Lucide `eye` 18px, `ink-200` (hover `ink-400`); when showing text → color `#946821`, aria-label toggles "Hiện mật khẩu" / "Ẩn mật khẩu".
- **Primary button**: full width, padding 16px 28px, radius 6px, bg `copper-400`, white 14px/600 Manrope, shadow `0 2px 6px rgba(70,50,20,.06)`; hover `copper-500 #B6822F`; active scale .97; disabled opacity .6.
- **Google button**: full width, white, border `cream-300`, padding 15px 28px, `ink-500`, 19px Google "G" logo + label, gap 8px; hover border `copper-300`, bg `cream-50`; active scale .99. 28px below subtitle.
- **Divider**: 24px vertical margins; two 1px `cream-300` lines flanking "HOẶC DÙNG EMAIL" 12px/600 uppercase tracking .14em `ink-200`, gap 14px.
- **Checkbox**: 16px (login: 14px), `accent-color: copper-400`, label 14px `ink-400 #565049`, gap 10px.
- **Password strength meter** (Register, Reset): 4 bars in grid, gap 6px, height 4px, radius full, empty `cream-200 #F2EBD8`; label right, 64px wide, 12px/500. Score: +1 length ≥8, +1 upper+lower, +1 digit, +1 symbol (min 1 if non-empty). 1 "Yếu" `#DC2626`, 2 "Trung bình" `#DAA85A`, 3 "Khá" `#B6822F`, 4 "Mạnh" `#15803D`. Filled bars = score, colored by level. Transition bg .25s.
- **Toast**: fixed bottom 24px centered, `ink-600` bg, `cream-50` 14px/500, padding 12px 20px, radius 6px, shadow-soft-lg; fades/slides in 12px over .3s, auto-hide 2.6s.

## Screens

### 1. Đăng nhập (Login)
- Brand quote: "Biết người là trí, / biết mình là sáng." Body: "Đăng nhập để lưu lá số tử vi, theo dõi luận giải và nhận thông báo vận hạn theo tháng." *(Note: product removed "tử vi" elsewhere — consider replacing with copy like Register's.)*
- Title "Đăng nhập", subtitle "Chào mừng bạn quay lại. Chọn cách đăng nhập bên dưới."
- Google button "Tiếp tục với Google" → divider → Email (placeholder "ban@vidu.com") → Mật khẩu with "Quên mật khẩu?" link (12px/500, right-aligned in label row) → link to Forgot page → checkbox "Ghi nhớ đăng nhập" (checked) → button "Đăng nhập".
- Footer: "Chưa có tài khoản? **Đăng ký miễn phí**" → Register.
- Validation on submit: email regex `^\S+@\S+\.\S+$`, password non-empty (currently via toast; prefer inline errors like Register).

### 2. Đăng ký (Register)
- Brand headline: "Bắt đầu hành trình / hiểu rõ bản thân." followed (32px gap) by 3 benefit rows (gap 16px, 14px `navy-100 #C5CFE0`, icon 18px `copper-300 #DAA85A`):
  - `bookmark` — Lưu kết quả phân tích tướng mặt và xem lại bất cứ lúc nào
  - `calendar-check` — Đặt lịch tư vấn trực tiếp với chuyên gia
  - `gift` — Ưu đãi riêng cho thành viên khi mua vật phẩm phong thủy
- Title "Tạo tài khoản", subtitle "Miễn phí, chỉ mất chưa đến một phút."
- Google "Đăng ký với Google" → divider → fields:
  - Họ và tên (placeholder "Nguyễn Văn An") — required. Error "Vui lòng nhập họ tên."
  - Row (2 cols ≥640px, gap 20px): Email — required, error "Email chưa hợp lệ." | Số điện thoại *(tùy chọn, `ink-200` regular)* placeholder "09xx xxx xxx" — optional; if filled must match `^(\+84|0)\d{9,10}$` after stripping spaces. Error "Số điện thoại chưa đúng."
  - Mật khẩu (placeholder "Tối thiểu 8 ký tự") + strength meter. Error "Mật khẩu cần ít nhất 8 ký tự."
  - Nhập lại mật khẩu — must match. Error "Mật khẩu nhập lại không khớp."
  - Checkbox (required, unchecked): "Tôi đồng ý với Điều khoản sử dụng và Chính sách bảo mật" (both links 500). Error "Bạn cần đồng ý với điều khoản để tiếp tục." (indented 24px).
  - Checkbox (checked): "Nhận bài viết phong thủy và ưu đãi qua email".
  - Button "Tạo tài khoản".
- Footer: "Đã có tài khoản? **Đăng nhập**".
- Validation: all fields validated on submit, all errors shown at once, focus moves to first invalid. Typing in a field clears its error. Success → (demo) toast "Tạo tài khoản thành công (demo). Kiểm tra email để xác nhận."

### 3. Quên mật khẩu (Forgot / Reset) — 4-step single card
Each step: 56px circle icon (bg `copper-50 #FBF4E5`, icon 24px `copper-600 #946821`), title 20px below, then content. Steps swap with fade-up (.7s, 16px). Footer under card: arrow-left icon + "Quay lại đăng nhập".
Brand panel: "Đừng lo, / chuyện nhỏ thôi." / "Kết quả phân tích và lịch hẹn của bạn vẫn được lưu an toàn. Đặt lại mật khẩu chỉ mất vài bước."
1. **Nhập email** — icon `key-round`. "Quên mật khẩu?" / "Nhập email bạn đã dùng để đăng ký. Chúng tôi sẽ gửi liên kết đặt lại mật khẩu." Email field → "Gửi liên kết đặt lại" (loading label "Đang gửi…", disabled ~900ms).
2. **Kiểm tra hộp thư** — icon `mail-check`. Body includes the submitted email in bold, "Liên kết có hiệu lực trong 30 phút." Tip box (bg `cream-100`, radius 6px, padding 14px 16px, 14px `ink-400`): "Không thấy email? Hãy kiểm tra mục **Thư rác** hoặc **Quảng cáo**." Buttons (gap 12px): primary "Mở liên kết trong email (demo)" *(prototype-only; remove — in production step 3 is reached via emailed token URL)*, secondary ghost "Gửi lại email" with 60s cooldown ("Gửi lại sau N giây", disabled). Text link with `pencil` icon "Dùng email khác" → step 1.
3. **Đặt mật khẩu mới** — icon `lock-keyhole`. "Mật khẩu mới nên khác với mật khẩu bạn đã dùng trước đây." New password + strength meter, confirm field, "Cập nhật mật khẩu". Same rules/errors as Register.
4. **Hoàn tất** — centered, icon `check` 28px in green circle (bg `green-50`, `green-700`). "Đã đổi mật khẩu" / "Mật khẩu của bạn đã được cập nhật. Hãy đăng nhập lại bằng mật khẩu mới." Button "Đăng nhập" → Login.

## State Management
- Login: `email`, `password`, `remember`, `showPassword`, `submitting`, `errors`.
- Register: `name, email, phone, password, confirm, acceptTerms, newsletter (default true)`, `showPassword/showConfirm`, `errors{}`, `submitting`, derived `strength`.
- Forgot: `step (1–4)`, `email`, `resendCooldown (sec)`, `password, confirm`, `errors`. In production, step 3 is a separate route `/reset-password?token=…` (validate token; show expired state if invalid).
- APIs needed: `POST /auth/login`, `GET /auth/google` (OAuth), `POST /auth/register`, `POST /auth/forgot` (always respond success to avoid email enumeration), `POST /auth/reset {token, password}`.

## Design Tokens
- **copper** 50 #FBF4E5 · 100 #F4E2BC · 200 #E9C886 · 300 #DAA85A · 400 #C89544 · 500 #B6822F · 600 #946821 · 700 #6E4D17
- **navy** 50 #EAEEF5 · 100 #C5CFE0 · 200 #8898BA · 300 #4D6592 · 400 #25446F · 500 #15325E · 600 #112A4F · 700 #0B1E3C
- **cream** 50 #FDFBF6 · 100 #FAF6EC · 200 #F2EBD8 · 300 #E8DCC0 · 400 #C9B68C
- **ink** 100 #DCD6CA · 200 #B6AEA0 · 300 #847C70 · 400 #565049 · 500 #2C2924 · 600 #1A1814
- Status: error red-400 #F87171 / red-600 #DC2626; success green-50 #F0FDF4 / green-700 #15803D
- Links: `#946821`, hover `#6E4D17`. Selection bg `#F4E2BC`.
- **Fonts**: Lora (serif, headings) 400–700; Manrope (sans, UI/body) 300–800. Google Fonts.
- **Type scale** (root 16px): xs 12/16 · sm 14/20 · base 16/24 · lg 18/28 · xl 20/28 · 2xl 24/32 · 3xl 30/36.
- **Radius**: inputs/buttons 6px, card 10px, pills/bars full.
- **Shadows**: soft-sm `0 2px 6px rgba(70,50,20,.06)` · soft-md `0 8px 24px rgba(70,50,20,.08)` · soft-lg `0 20px 48px rgba(70,50,20,.10)`.
- Spacing: Tailwind 4px scale.

## Assets
- `assets/logo-mark.svg` — brand mark.
- `assets/zodiac-wheel.svg` — decorative rotating wheel on brand panel.
- Icons: Lucide (v0.460) — key-round, mail-check, lock-keyhole, check, eye, pencil, arrow-left, bookmark, calendar-check, gift. Login uses inline-SVG eye.
- Google "G" logo: inline SVG (official colors).

## Files
- `Đăng nhập.html` — Login
- `Đăng ký.html` — Register
- `Quên mật khẩu.html` — Forgot / reset password (4 steps)
- Links between files reference `Trang chủ (Tailwind).html` (homepage, not included).
