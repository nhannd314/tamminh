/**
 * Thông báo hoạt động realtime (Reverb): "A vừa đặt hàng...".
 * Nhận sự kiện `activity.occurred` trên kênh `activity`, xếp hàng và hiện lần lượt ở góc dưới bên trái.
 */
const SHOW_MS = 5000;
const GAP_MS = 1500;
const MAX_QUEUE = 3;

const root = document.querySelector('[data-activity-root]');

if (root && window.Echo) {
    const queue = [];
    let busy = false;

    const showNext = () => {
        const activity = queue.shift();

        if (!activity) {
            busy = false;
            return;
        }

        busy = true;
        root.querySelectorAll('[data-activity-icon]').forEach((icon) => {
            icon.hidden = icon.dataset.activityIcon !== activity.kind;
        });
        root.querySelector('[data-activity-message]').textContent = activity.message;
        root.classList.add('is-visible');

        setTimeout(() => {
            root.classList.remove('is-visible');
            setTimeout(showNext, GAP_MS);
        }, SHOW_MS);
    };

    root.querySelector('[data-activity-close]')?.addEventListener('click', () => root.classList.remove('is-visible'));

    window.Echo.channel('activity').listen('.activity.occurred', (activity) => {
        // Tab đang ẩn thì bỏ qua để không dồn thông báo cũ khi khách quay lại.
        if (document.hidden || queue.length >= MAX_QUEUE) {
            return;
        }

        queue.push(activity);

        if (!busy) {
            showNext();
        }
    });
}
