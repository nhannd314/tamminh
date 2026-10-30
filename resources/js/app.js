/**
 * Tâm Minh — UI behaviours.
 *
 * Mọi tương tác được gắn qua data-attribute để Blade components
 * không phụ thuộc vào id cụ thể:
 *   data-burger / data-drawer / data-drawer-scrim / data-drawer-close
 *   data-accordion / data-accordion-item / data-accordion-trigger
 *   data-toast="message"            → hiện toast khi click
 *   data-toast-on-submit="message"  → hiện toast khi submit form
 *   data-modal-open="id" / data-modal="id" / data-modal-close
 *   data-reveal / data-reveal-stagger
 *   data-file-input → cập nhật [data-file-name] trong cùng dropzone
 *   data-submit-loading            → khoá nút submit khi form gửi lên server
 *   data-copy="text"               → sao chép vào clipboard
 *   data-print                     → mở hộp thoại in
 *   data-toc                       → đánh dấu mục đang đọc (.is-active)
 *   data-tabs / data-tab / data-tab-panel → chuyển tab
 *   data-modal-autoopen + data-payment-status="url" → mở cửa sổ thanh toán, chờ SePay báo đã nhận tiền
 *   data-sticky-header
 */

const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

/* ---------- Toast ---------- */
let toastTimer;

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
        navigator.clipboard
            ?.writeText(copyTrigger.dataset.copy)
            .then(() => showToast(copyTrigger.dataset.copyMessage || 'Đã sao chép liên kết.'))
            .catch(() => showToast(copyTrigger.dataset.copy));
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
function setupPaymentModal() {
    const marker = document.querySelector('[data-modal-autoopen]');
    const modal = marker?.closest('[data-modal]');

    if (!modal) {
        return;
    }

    openModal(modal.dataset.modal);

    const statusUrl = marker.dataset.paymentStatus;
    if (!statusUrl) {
        return;
    }

    const startedAt = Date.now();
    const timer = setInterval(async () => {
        // Ngừng sau 20 phút hoặc khi người dùng đã đóng cửa sổ.
        if (Date.now() - startedAt > 20 * 60 * 1000 || modal.classList.contains('hidden')) {
            clearInterval(timer);
            return;
        }

        try {
            const response = await fetch(statusUrl, { headers: { Accept: 'application/json' } });
            const { paid } = await response.json();

            if (paid) {
                clearInterval(timer);
                modal.querySelector('[data-payment-message]').textContent = 'Đã nhận thanh toán, đang mở khóa nội dung…';
                setTimeout(() => window.location.reload(), 1200);
            }
        } catch {
            // Mất mạng tạm thời: thử lại ở lượt sau.
        }
    }, 4000);
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
    $$('[data-modal].flex').forEach(closeModal);
});

document.addEventListener('submit', (event) => {
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

        if (label) {
            label.textContent = input.files[0].name;
        }
    }
});

const stickyHeader = document.querySelector('[data-sticky-header]');
if (stickyHeader) {
    const syncShadow = () => stickyHeader.classList.toggle('shadow-soft-sm', window.scrollY > 8);
    window.addEventListener('scroll', syncShadow, { passive: true });
    syncShadow();
}

setupReveal();
setupScrollSpy();
setupPaymentModal();
