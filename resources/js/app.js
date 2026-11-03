/**
 * Tâm Minh — UI behaviours.
 *
 * Mọi tương tác được gắn qua data-attribute để Blade components
 * không phụ thuộc vào id cụ thể:
 *   data-burger / data-drawer / data-drawer-scrim / data-drawer-close
 *   data-dropdown > data-dropdown-trigger + data-dropdown-menu → menu thả xuống, đóng khi bấm ra ngoài / Esc
 *   data-accordion / data-accordion-item / data-accordion-trigger
 *   data-toast="message"            → hiện toast khi click
 *   data-toast-on-submit="message"  → hiện toast khi submit form
 *   data-modal-open="id" / data-modal="id" / data-modal-close
 *   data-reveal / data-reveal-stagger
 *   data-file-input → cập nhật [data-file-name], xem trước ảnh [data-file-preview] trong cùng dropzone
 *   data-submit-loading            → khoá nút submit khi form gửi lên server
 *   data-copy="text"               → sao chép vào clipboard
 *   data-print                     → mở hộp thoại in
 *   data-toc                       → đánh dấu mục đang đọc (.is-active)
 *   data-tabs / data-tab / data-tab-panel → chuyển tab
 *   data-modal-autoopen + data-payment-status="url" → mở cửa sổ thanh toán, chờ SePay báo đã nhận tiền
 *   data-payment-watch="url"     → khung chuyển khoản trên trang, tự tải lại khi SePay báo đã nhận tiền
 *   data-unlock-form               → tạo đơn bằng fetch và mở cửa sổ thanh toán tại chỗ
 *   data-generation-stream="url"   → nhận nội dung Gemini qua SSE, thay từng phần đang viết
 *   data-generation-status="url"   → dự phòng: chờ luận giải xong rồi tải lại trang
 *   data-portrait-upload="url"     → gửi ảnh chân dung ngay khi chọn để server xử lý trước
 *   data-sticky-header
 *   data-product                   → trang chi tiết vật phẩm: [data-option-group] > [data-option] (kích thước/kiểu dáng),
 *                                    [data-qty-*] (số lượng), [data-gallery-*] (ảnh); đồng bộ vào form [data-product-form]
 */

const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

/* ---------- Toast ---------- */
let toastTimer;

/**
 * Clipboard API chỉ có trên HTTPS/localhost; trang chạy http (VD tamminh.local) thì chép qua một ô textarea tạm.
 * Ô tạm đặt cạnh nút để vẫn chọn được chữ khi nút nằm trong cửa sổ modal.
 */
async function copyText(text, trigger) {
    if (navigator.clipboard && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(text);

            return true;
        } catch {
            // Trình duyệt từ chối quyền ghi clipboard: thử cách dự phòng bên dưới.
        }
    }

    const field = document.createElement('textarea');
    field.value = text;
    field.setAttribute('readonly', '');
    field.style.cssText = 'position:fixed;top:0;left:0;opacity:0;pointer-events:none';
    (trigger.parentElement || document.body).appendChild(field);
    field.select();

    try {
        return document.execCommand('copy');
    } catch {
        return false;
    } finally {
        field.remove();
        trigger.focus({ preventScroll: true });
    }
}

export function showToast(message) {
    const root = document.querySelector('[data-toast-root]');

    if (!root) {
        return;
    }

    root.querySelector('[data-toast-message]').textContent = message;
    root.classList.add('is-visible');

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => root.classList.remove('is-visible'), 2600);
}

/* ---------- Drawer ---------- */
function setDrawer(open) {
    document.body.classList.toggle('drawer-open', open);
    $$('[data-burger]').forEach((burger) => burger.setAttribute('aria-expanded', String(open)));
}

/* ---------- Dropdown ---------- */
function setDropdown(dropdown, open) {
    dropdown.querySelector('[data-dropdown-menu]').hidden = !open;
    dropdown.querySelector('[data-dropdown-trigger]').setAttribute('aria-expanded', String(open));
}

function closeDropdowns(except = null) {
    $$('[data-dropdown]').forEach((dropdown) => {
        if (dropdown !== except) {
            setDropdown(dropdown, false);
        }
    });
}

/* ---------- Modal ---------- */
function openModal(id, payload = {}) {
    const modal = document.querySelector(`[data-modal="${id}"]`);

    if (!modal) {
        return;
    }

    Object.entries(payload).forEach(([key, value]) => {
        $$(`[data-modal-field="${key}"]`, modal).forEach((el) => {
            el.textContent = value;
        });
    });

    modal.dataset.payload = JSON.stringify(payload);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.querySelector('input, textarea, button')?.focus();
}

function closeModal(modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

/* ---------- Scroll reveal ---------- */
function setupReveal() {
    $$('[data-reveal-stagger]').forEach((group) => {
        $$(':scope > [data-reveal]', group).forEach((child, index) => {
            child.style.setProperty('--reveal-delay', `${index * 80}ms`);
        });
    });

    const targets = $$('[data-reveal]');

    if (!('IntersectionObserver' in window)) {
        targets.forEach((el) => el.classList.add('is-revealed'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -60px 0px' },
    );

    targets.forEach((el) => observer.observe(el));
}

/* ---------- Wiring ---------- */
document.addEventListener('click', (event) => {
    const target = event.target;

    const dropdownTrigger = target.closest('[data-dropdown-trigger]');
    const dropdown = target.closest('[data-dropdown]');
    closeDropdowns(dropdown);
    if (dropdownTrigger) {
        setDropdown(dropdown, dropdownTrigger.getAttribute('aria-expanded') !== 'true');
    }

    const toastTrigger = target.closest('[data-toast]');
    if (toastTrigger) {
        event.preventDefault();
        showToast(toastTrigger.dataset.toast);
    }

    if (target.closest('[data-burger]')) {
        setDrawer(!document.body.classList.contains('drawer-open'));
    }

    if (target.closest('[data-drawer-close], [data-drawer-scrim], [data-drawer] a')) {
        setDrawer(false);
    }

    const accordionTrigger = target.closest('[data-accordion-trigger]');
    if (accordionTrigger) {
        const item = accordionTrigger.closest('[data-accordion-item]');
        const group = item.closest('[data-accordion]');
        const wasOpen = item.classList.contains('is-open');

        if (group && group.dataset.accordion !== 'multiple') {
            $$('[data-accordion-item].is-open', group).forEach((openItem) => {
                openItem.classList.remove('is-open');
                openItem.querySelector('[data-accordion-trigger]')?.setAttribute('aria-expanded', 'false');
            });
        }

        item.classList.toggle('is-open', !wasOpen);
        accordionTrigger.setAttribute('aria-expanded', String(!wasOpen));
    }

    const modalTrigger = target.closest('[data-modal-open]');
    if (modalTrigger) {
        openModal(modalTrigger.dataset.modalOpen, JSON.parse(modalTrigger.dataset.modalPayload || '{}'));
    }

    const modalRoot = target.closest('[data-modal]');
    if (modalRoot && (target === modalRoot || target.closest('[data-modal-close]'))) {
        closeModal(modalRoot);
    }

    const copyTrigger = target.closest('[data-copy]');
    if (copyTrigger) {
        event.preventDefault();
        copyText(copyTrigger.dataset.copy, copyTrigger).then((copied) =>
            showToast(copied ? copyTrigger.dataset.copyMessage || 'Đã sao chép.' : `Không sao chép được, vui lòng chép tay: ${copyTrigger.dataset.copy}`),
        );
    }

    if (target.closest('[data-print]')) {
        window.print();
    }

    const tab = target.closest('[data-tabs] [data-tab]');
    if (tab) {
        const group = tab.closest('[data-tabs]');

        $$('[data-tab]', group).forEach((button) => button.setAttribute('aria-selected', String(button === tab)));
        $$('[data-tab-panel]', group).forEach((panel) => {
            panel.hidden = panel.dataset.tabPanel !== tab.dataset.tab;
        });
    }
});

/* ---------- Form xem tướng: gửi ảnh ngay khi chọn để server cắt mặt, đo Tam đình trước ---------- */
let portraitUpload = null;

function showPortraitError(input, message) {
    const field = input.closest('.tm-field');
    let error = field?.querySelector('.tm-error');

    if (field && !error) {
        error = document.createElement('p');
        error.className = 'tm-error';
        field.append(error);
    }

    if (error) {
        error.textContent = message;
        error.hidden = !message;
    }
}

/**
 * Trạng thái khung chọn ảnh: null (chưa có ảnh), 'selected', 'scanning' (đang nhận diện), 'detected'.
 */
function setDropzoneState(dropzone, state, message = '') {
    if (state) {
        dropzone.dataset.state = state;
    } else {
        delete dropzone.dataset.state;
    }

    dropzone.querySelector('[data-file-status]').textContent = message;
}

function previewFile(input) {
    const dropzone = input.closest('[data-dropzone]');
    const image = dropzone?.querySelector('[data-file-preview]');
    const file = input.files[0];

    if (!image || !file.type.startsWith('image/')) {
        return false;
    }

    if (image.src.startsWith('blob:')) {
        URL.revokeObjectURL(image.src);
    }

    image.src = URL.createObjectURL(file);
    setDropzoneState(dropzone, 'selected');

    return true;
}

function uploadPortrait(input) {
    const form = input.closest('[data-portrait-upload]');
    const idInput = form.querySelector('[data-portrait-upload-id]');
    const dropzone = input.closest('[data-dropzone]');
    const body = new FormData();

    body.append('portrait', input.files[0]);
    body.append('_token', form.querySelector('[name="_token"]').value);
    idInput.value = '';
    showPortraitError(input, '');
    setDropzoneState(dropzone, 'scanning', 'Đang nhận diện khuôn mặt…');

    const upload = fetch(form.dataset.portraitUpload, { method: 'POST', body, headers: { Accept: 'application/json' } })
        .then(async (response) => {
            const data = await response.json().catch(() => ({}));

            // Khách đã chọn ảnh khác trong lúc chờ.
            if (portraitUpload !== upload) {
                return;
            }

            if (response.ok) {
                idInput.value = data.id;
                setDropzoneState(dropzone, data.face_detected ? 'detected' : 'selected', data.face_detected ? 'Đã nhận diện khuôn mặt' : '');

                return;
            }

            if (response.status === 422) {
                input.value = '';
                setDropzoneState(dropzone, null);
                showPortraitError(input, data.errors?.portrait?.[0] ?? data.message);

                return;
            }

            // Lỗi khác: để form gửi kèm ảnh như bình thường.
            setDropzoneState(dropzone, 'selected');
        })
        .catch(() => {
            if (portraitUpload === upload) {
                setDropzoneState(dropzone, 'selected');
            }
        })
        .finally(() => {
            if (portraitUpload === upload) {
                portraitUpload = null;
            }
        });

    portraitUpload = upload;
}

// Chạy trước listener khoá nút (capture) để chờ ảnh gửi xong rồi mới submit.
document.addEventListener(
    'submit',
    (event) => {
        const form = event.target.closest('[data-portrait-upload]');

        if (!form) {
            return;
        }

        if (portraitUpload) {
            event.preventDefault();
            $$('button[type="submit"]', form).forEach((button) => {
                button.disabled = true;
            });
            portraitUpload.then(() => form.requestSubmit());

            return;
        }

        // Ảnh đã ở trên server: chỉ gửi id, không gửi lại file.
        const fileInput = form.querySelector('[data-file-input]');
        fileInput.disabled = Boolean(form.querySelector('[data-portrait-upload-id]').value);
    },
    true,
);

/* ---------- Submit thật lên server: khoá nút, tránh gửi trùng ---------- */
document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-submit-loading]');

    if (!form || event.defaultPrevented) {
        return;
    }

    $$('button[type="submit"]', form).forEach((button) => {
        button.disabled = true;

        if (button.dataset.loadingText) {
            button.textContent = button.dataset.loadingText;
        }
    });
});

/* ---------- Toast gửi từ server (session flash) ---------- */
const flashToast = document.querySelector('[data-toast-root]')?.dataset.flash;
if (flashToast) {
    setTimeout(() => showToast(flashToast), 300);
}

/* ---------- Cửa sổ thanh toán: tự mở, tự kiểm tra trạng thái đơn ---------- */
function setupPaymentModal(root = document) {
    const marker = root.querySelector('[data-modal-autoopen]');
    const modal = marker?.closest('[data-modal]');

    if (!modal) {
        return;
    }

    openModal(modal.dataset.modal);

    const statusUrl = marker.dataset.paymentStatus;
    if (!statusUrl) {
        return;
    }

    // Ngừng khi người dùng đã đóng cửa sổ hoặc cửa sổ bị thay bằng đơn khác.
    watchPayment(statusUrl, modal, 'Đã nhận thanh toán, đang mở khóa nội dung…', () => !modal.classList.contains('hidden') && modal.isConnected);
}

/**
 * Hỏi trạng thái đơn mỗi 4 giây (tối đa 20 phút); SePay báo đã nhận tiền thì hiện thông báo và tải lại trang.
 */
function watchPayment(statusUrl, root, paidMessage, isActive = () => true) {
    const startedAt = Date.now();
    const timer = setInterval(async () => {
        if (Date.now() - startedAt > 20 * 60 * 1000 || !isActive()) {
            clearInterval(timer);
            return;
        }

        try {
            const response = await fetch(statusUrl, { headers: { Accept: 'application/json' } });
            const { paid } = await response.json();

            if (paid) {
                clearInterval(timer);
                const message = root.querySelector('[data-payment-message]');
                if (message) {
                    message.textContent = paidMessage;
                }
                setTimeout(() => window.location.reload(), 1200);
            }
        } catch {
            // Mất mạng tạm thời: thử lại ở lượt sau.
        }
    }, 4000);
}

/* ---------- Khung chuyển khoản ngay trên trang (đơn vật phẩm) ---------- */
document.querySelectorAll('[data-payment-watch]').forEach((panel) => {
    watchPayment(panel.dataset.paymentWatch, panel, panel.dataset.paymentPaidMessage || 'Đã nhận thanh toán, đang cập nhật đơn…');
});

/* ---------- Nút mở khoá: tạo đơn và mở cửa sổ thanh toán tại chỗ, không tải lại trang ---------- */
async function submitUnlockForm(form) {
    const button = form.querySelector('[type="submit"]');
    button.disabled = true;

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) {
            throw new Error(String(response.status));
        }

        const { html } = await response.json();

        if (!html) {
            window.location.reload();
            return;
        }

        $$('[data-modal="payment"]').forEach((modal) => modal.remove());

        const template = document.createElement('template');
        template.innerHTML = html.trim();
        const modal = template.content.firstElementChild;
        document.body.append(modal);
        setupPaymentModal(modal);
    } catch {
        showToast('Không tạo được đơn thanh toán, vui lòng thử lại.');
    } finally {
        button.disabled = false;
    }
}

/* ---------- Đang luận giải: nhận nội dung Gemini qua SSE và thay từng phần ngay khi có chữ ---------- */
function replaceWithHtml(id, html) {
    const current = document.getElementById(id);

    if (!current) {
        return;
    }

    const template = document.createElement('template');
    template.innerHTML = html.trim();
    const next = template.content.firstElementChild;
    $$('[data-reveal]', next).forEach((el) => el.classList.add('is-revealed'));
    current.replaceWith(next);
}

function setupGenerationStream() {
    const hook = document.querySelector('[data-generation-stream]');

    if (!hook || !('EventSource' in window)) {
        setupGenerationStatus();
        return;
    }

    const source = new EventSource(hook.dataset.generationStream);
    const finish = () => source.close();

    source.addEventListener('section', (event) => {
        const { id, html } = JSON.parse(event.data);
        replaceWithHtml(id, html);
    });

    source.addEventListener('done', (event) => {
        finish();
        replaceWithHtml('tong-quan-van-menh', JSON.parse(event.data).html);
        hook.remove();
    });

    // Các spinner còn lại đổi thành thông báo lỗi ngay tại chỗ.
    source.addEventListener('failed', (event) => {
        finish();
        const { message, html } = JSON.parse(event.data);

        if (html) {
            replaceWithHtml('tong-quan-van-menh', html);
        }

        $$('[data-generation-loading]').forEach((loading) => {
            loading.querySelector('[data-generation-spinner]')?.remove();
            loading.querySelector('[data-generation-loading-label]').textContent = message;
            loading.classList.add('text-danger');
        });
    });

    // Đang có tiến trình khác viết (tab khác, job dự phòng) hoặc mất kết nối: hỏi định kỳ rồi tải lại trang.
    source.addEventListener('busy', () => {
        finish();
        setupGenerationStatus();
    });

    source.addEventListener('error', () => {
        if (source.readyState !== EventSource.CLOSED) {
            finish();
            setupGenerationStatus();
        }
    });
}

function setupGenerationStatus() {
    const statusUrl = document.querySelector('[data-generation-status]')?.dataset.generationStatus;

    if (!statusUrl) {
        return;
    }

    const startedAt = Date.now();
    const timer = setInterval(async () => {
        if (Date.now() - startedAt > 15 * 60 * 1000) {
            clearInterval(timer);
            return;
        }

        try {
            const response = await fetch(statusUrl, { headers: { Accept: 'application/json' } });
            const { generating } = await response.json();

            // Không tải lại khi khách đang mở cửa sổ thanh toán.
            if (!generating && !document.querySelector('[data-modal="payment"].flex')) {
                clearInterval(timer);
                window.location.reload();
            }
        } catch {
            // Mất mạng tạm thời: thử lại ở lượt sau.
        }
    }, 5000);
}

/* ---------- Mục lục: đánh dấu phần đang đọc ---------- */
function setupScrollSpy() {
    const links = $$('[data-toc] a[href^="#"]');
    const sections = links.map((link) => document.querySelector(link.getAttribute('href'))).filter(Boolean);

    if (!sections.length || !('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries
                .filter((entry) => entry.isIntersecting)
                .forEach((entry) => {
                    links.forEach((link) => link.classList.toggle('is-active', link.getAttribute('href') === `#${entry.target.id}`));
                });
        },
        { rootMargin: '-30% 0px -60% 0px' },
    );

    sections.forEach((section) => observer.observe(section));
}

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    setDrawer(false);
    closeDropdowns();
    $$('[data-modal].flex').forEach(closeModal);
});

document.addEventListener('submit', (event) => {
    const unlockForm = event.target.closest('[data-unlock-form]');
    if (unlockForm) {
        event.preventDefault();
        submitUnlockForm(unlockForm);
        return;
    }

    const form = event.target.closest('[data-toast-on-submit]');

    if (!form) {
        return;
    }

    event.preventDefault();
    showToast(form.dataset.toastOnSubmit);
    form.reset();

    const modal = form.closest('[data-modal]');
    if (modal) {
        closeModal(modal);
    }
});

document.addEventListener('change', (event) => {
    const input = event.target.closest('[data-file-input]');

    if (input?.files[0]) {
        const label = input.closest('label')?.querySelector('[data-file-name]');

        // Ảnh thì hiện xem trước; tệp khác thì hiện tên tệp.
        if (!previewFile(input) && label) {
            label.textContent = input.files[0].name;
        }

        if (input.closest('[data-portrait-upload]')) {
            uploadPortrait(input);
        }
    }
});

const stickyHeader = document.querySelector('[data-sticky-header]');
if (stickyHeader) {
    const syncShadow = () => stickyHeader.classList.toggle('shadow-soft-sm', window.scrollY > 8);
    window.addEventListener('scroll', syncShadow, { passive: true });
    syncShadow();
}

/* ---------- Trang chi tiết vật phẩm: chọn kích thước/kiểu dáng/số lượng, đổi ảnh, đồng bộ vào form thêm giỏ ---------- */
const formatMoney = (amount) => `${amount.toLocaleString('vi-VN')}đ`;

function setupProductDetail() {
    const root = document.querySelector('[data-product]');
    if (!root) {
        return;
    }

    const form = root.querySelector('[data-product-form]');
    const stock = Number(root.dataset.stock) || 1;
    const price = root.querySelector('[data-product-price]');
    const compare = root.querySelector('[data-product-compare]');
    const subtotal = root.querySelector('[data-product-subtotal]');
    const qtyValue = root.querySelector('[data-qty-value]');
    let quantity = 1;
    let unitPrice = Number(price.textContent.replace(/\D/g, ''));

    const selected = (group) => root.querySelector(`[data-option-group="${group}"] [data-option][aria-pressed="true"]`);

    const render = () => {
        const size = selected('size');
        const style = selected('style');

        if (size) {
            unitPrice = Number(size.dataset.price);
        }

        const oldPrice = size ? Number(size.dataset.compare) : 0;

        price.textContent = formatMoney(unitPrice);
        compare.textContent = oldPrice ? formatMoney(oldPrice) : '';
        compare.classList.toggle('hidden', !oldPrice);
        subtotal.classList.toggle('hidden', quantity < 2);
        subtotal.querySelector('span').textContent = `${formatMoney(unitPrice * quantity)} (${quantity} sản phẩm)`;

        if (qtyValue) {
            qtyValue.textContent = String(quantity);
        }

        form.elements.quantity.value = String(quantity);
        form.elements.size.value = size?.dataset.name ?? '';
        form.elements.style.value = style?.dataset.name ?? '';
    };

    root.addEventListener('click', (event) => {
        const target = event.target;
        const option = target.closest('[data-option]');

        if (option) {
            $$('[data-option]', option.closest('[data-option-group]')).forEach((item) => item.setAttribute('aria-pressed', String(item === option)));
            render();
        } else if (target.closest('[data-qty-minus]')) {
            quantity = Math.max(1, quantity - 1);
            render();
        } else if (target.closest('[data-qty-plus]')) {
            quantity = Math.min(stock, quantity + 1);
            render();
        }

        const thumb = target.closest('[data-gallery-thumb]');
        if (thumb) {
            $$('[data-gallery-image]', root).forEach((image, index) => image.classList.toggle('hidden', index !== Number(thumb.dataset.galleryThumb)));
            $$('[data-gallery-thumb]', root).forEach((item) => item.setAttribute('aria-current', String(item === thumb)));
        }
    });

    render();
}

setupProductDetail();

/* ---------- Thêm vào giỏ: gửi bằng fetch, ảnh vật phẩm bay vào nút giỏ trên header ---------- */
function flyToCart(source, cartButton) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return Promise.resolve();
    }

    const from = source.getBoundingClientRect();
    const to = cartButton.getBoundingClientRect();
    const size = Math.min(Math.max(from.width, 48), 120);
    const startX = from.left + from.width / 2 - size / 2;
    const startY = from.top + from.height / 2 - size / 2;
    const endX = to.left + to.width / 2 - size / 2;
    const endY = to.top + to.height / 2 - size / 2;

    const ghost = document.createElement(source instanceof HTMLImageElement ? 'img' : 'div');
    ghost.setAttribute('aria-hidden', 'true');
    ghost.style.cssText = `position:fixed;z-index:9999;left:0;top:0;width:${size}px;height:${size}px;border-radius:9999px;pointer-events:none;object-fit:cover;box-shadow:0 8px 24px rgb(0 0 0 / .25);background:var(--color-copper-400,#c27a4a)`;

    if (ghost instanceof HTMLImageElement) {
        ghost.src = source.currentSrc || source.src;
    }

    document.body.append(ghost);

    const animation = ghost.animate(
        [
            { transform: `translate(${startX}px, ${startY}px) scale(1)`, opacity: 1, offset: 0 },
            { transform: `translate(${(startX + endX) / 2}px, ${Math.min(startY, endY) - 80}px) scale(.6)`, opacity: 1, offset: 0.55 },
            { transform: `translate(${endX}px, ${endY}px) scale(.15)`, opacity: 0.4, offset: 1 },
        ],
        { duration: 750, easing: 'cubic-bezier(.4, 0, .2, 1)' },
    );

    return animation.finished.catch(() => {}).finally(() => ghost.remove());
}

function bumpCart(cartButton, count) {
    const badge = cartButton.querySelector('[data-cart-count]');

    if (badge) {
        badge.textContent = count > 99 ? '99+' : String(count);
        badge.classList.toggle('hidden', count < 1);
    }

    cartButton.setAttribute('aria-label', `Giỏ hàng (${count} sản phẩm)`);
    cartButton.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.25)' }, { transform: 'scale(1)' }], { duration: 350, easing: 'ease-out' });
}

async function submitAddToCart(form, submitter) {
    const cartButton = document.querySelector('[data-cart-button]');
    const button = submitter ?? form.querySelector('[type="submit"]');
    const source = form.closest('article')?.querySelector('img') ?? document.querySelector('[data-gallery-image]:not(.hidden)') ?? button;

    if (button) {
        button.disabled = true;
    }

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            showToast(data.message ?? 'Không thêm được vào giỏ hàng, vui lòng thử lại.');

            return;
        }

        if (cartButton && source) {
            await flyToCart(source, cartButton);
        }

        if (cartButton) {
            bumpCart(cartButton, data.count);
        }

        showToast(data.message);
    } catch {
        // Mất mạng: gửi form theo cách thường để server xử lý.
        form.submit();
    } finally {
        if (button) {
            button.disabled = false;
        }
    }
}

document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-add-to-cart]');

    if (!form || event.defaultPrevented || !document.querySelector('[data-cart-button]')) {
        return;
    }

    event.preventDefault();
    submitAddToCart(form, event.submitter);
});

setupReveal();
setupScrollSpy();
setupPaymentModal();
setupGenerationStream();

// Quay lại trang bằng nút Back (bfcache): mở lại ô ảnh đã bị tắt lúc submit.
window.addEventListener('pageshow', () => {
    $$('[data-portrait-upload] [data-file-input]').forEach((input) => {
        input.disabled = false;
    });
});

/* ---------- Trang thanh toán: đổi hình thức thanh toán thì tính lại phí giao hàng và tổng ---------- */
document.querySelectorAll('[data-checkout]').forEach((form) => {
    const subtotal = Number(form.dataset.checkoutSubtotal);
    const fees = JSON.parse(form.dataset.checkoutFees);
    const money = (amount) => `${amount.toLocaleString('vi-VN')}đ`;

    form.addEventListener('change', (event) => {
        if (event.target.name !== 'payment_method') {
            return;
        }

        const fee = fees[event.target.value] ?? 0;
        form.querySelector('[data-checkout-shipping]').textContent = fee > 0 ? money(fee) : 'Miễn phí';
        form.querySelector('[data-checkout-total]').textContent = money(subtotal + fee);
    });
});

/* ---------- Ô mật khẩu: nút hiện/ẩn và thước đo độ mạnh ---------- */
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-password-toggle]');
    if (!toggle) {
        return;
    }

    const input = toggle.parentElement.querySelector('[data-password-input]');
    const showing = input.type === 'text';

    input.type = showing ? 'password' : 'text';
    toggle.setAttribute('aria-pressed', String(!showing));
    toggle.setAttribute('aria-label', showing ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
    toggle.querySelector('[data-password-icon="show"]').classList.toggle('hidden', !showing);
    toggle.querySelector('[data-password-icon="hide"]').classList.toggle('hidden', showing);
});

const passwordLevels = [
    null,
    { label: 'Yếu', color: '#DC2626' },
    { label: 'Trung bình', color: '#DAA85A' },
    { label: 'Khá', color: '#B6822F' },
    { label: 'Mạnh', color: '#15803D' },
];

function passwordScore(value) {
    if (!value) {
        return 0;
    }

    const score = [value.length >= 8, /[a-z]/.test(value) && /[A-Z]/.test(value), /\d/.test(value), /[^A-Za-z0-9]/.test(value)].filter(Boolean).length;

    return Math.max(score, 1);
}

document.addEventListener('input', (event) => {
    const input = event.target.closest('[data-password-input]');
    const meter = input?.closest('[data-password-field]')?.querySelector('[data-password-strength]');
    if (!meter) {
        return;
    }

    const score = passwordScore(input.value);
    const level = passwordLevels[score];

    meter.querySelectorAll('[data-strength-bar]').forEach((bar, index) => {
        bar.style.backgroundColor = level && index < score ? level.color : '';
    });

    const label = meter.querySelector('[data-strength-label]');
    label.textContent = level?.label ?? '';
    label.style.color = level?.color ?? '';
});

/* ---------- Nút "Gửi lại email": khoá trong N giây rồi mới cho bấm ---------- */
document.querySelectorAll('[data-resend-cooldown]').forEach((button) => {
    let remaining = Number(button.dataset.resendCooldown);

    const timer = setInterval(() => {
        remaining -= 1;

        if (remaining <= 0) {
            clearInterval(timer);
            button.disabled = false;
            button.textContent = button.dataset.resendLabel;
            return;
        }

        button.textContent = `Gửi lại sau ${remaining} giây`;
    }, 1000);
});

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
import './activity-feed';
