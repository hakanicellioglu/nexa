<?php
/**
 * toast.php
 * Minimal, fonksiyonel ve modern bir PHP toast bildirim sistemi.
 * Tek dosyada çalışır, XSS koruması vardır ve birden fazla toast destekler.
 */

function toast(string $message, string $type = 'info'): string
{
    static $initialized = false;

    $palette = [
        'success' => [
            'class' => 'toast-success',
            'title' => 'Başarılı',
            'icon' => '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M6.173 11.414 3.06 8.3a.75.75 0 1 0-1.06 1.061l3.25 3.25a.75.75 0 0 0 1.06 0l7.25-7.25a.75.75 0 1 0-1.06-1.06L6.173 11.414Z"/></svg>',
        ],
        'error' => [
            'class' => 'toast-error',
            'title' => 'Hata',
            'icon' => '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708Z"/></svg>',
        ],
        'warning' => [
            'class' => 'toast-warning',
            'title' => 'Uyarı',
            'icon' => '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M7.002 1.083a1 1 0 0 1 1.996 0l6.857 12a1 1 0 0 1-.866 1.5H1.01a1 1 0 0 1-.866-1.5l6.858-12ZM8 5.25a.75.75 0 0 0-.75.75v3.5a.75.75 0 0 0 1.5 0v-3.5A.75.75 0 0 0 8 5.25Zm0 7a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75Z"/></svg>',
        ],
        'info' => [
            'class' => 'toast-info',
            'title' => 'Bilgi',
            'icon' => '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M7.002 5.5a.75.75 0 0 1 1.496 0v.75a.75.75 0 0 1-1.496 0V5.5Zm.998 3.5a.75.75 0 0 0-.75.75v3.5a.75.75 0 0 0 1.5 0v-3.5a.75.75 0 0 0-.75-.75Zm-.998-7a7 7 0 1 0 0 14 7 7 0 0 0 0-14Z"/></svg>',
        ],
    ];

    $type = strtolower($type);
    if (!isset($palette[$type])) {
        $type = 'info';
    }

    $data = $palette[$type];
    $safeMessage = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $toastId = 'php-toast-' . uniqid();

    $toastHtml = <<<HTML
<div id="{$toastId}" class="php-toast {$data['class']}" role="status" aria-live="polite">
    <div class="php-toast__icon">{$data['icon']}</div>
    <div class="php-toast__content">
        <strong class="php-toast__title">{$data['title']}</strong>
        <p class="php-toast__message">{$safeMessage}</p>
    </div>
    <button type="button" class="php-toast__close" aria-label="Kapat">×</button>
</div>
HTML;

    if (!$initialized) {
        $initialized = true;
        $assets = <<<HTML
<style>
.php-toast-container {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-width: min(420px, calc(100vw - 1rem));
    pointer-events: none;
}
.php-toast {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 0.75rem;
    align-items: center;
    background: rgba(255, 255, 255, 0.96);
    color: #222;
    border: 1px solid rgba(34, 34, 34, 0.08);
    box-shadow: 0 18px 45px rgba(0, 0, 0, 0.12);
    border-radius: 14px;
    padding: 1rem 1rem 1rem 0.9rem;
    backdrop-filter: blur(14px);
    transform: translateX(20px);
    opacity: 0;
    pointer-events: auto;
    overflow: hidden;
    animation: toast-fade-in 0.4s ease forwards;
}
.php-toast--hide {
    animation: toast-fade-out 0.35s ease forwards;
}
.php-toast__icon,
.php-toast__close {
    flex-shrink: 0;
}
.php-toast__icon {
    width: 2.35rem;
    height: 2.35rem;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.06);
}
.php-toast__icon svg {
    width: 1.15rem;
    height: 1.15rem;
    fill: currentColor;
}
.php-toast__content {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    min-width: 0;
}
.php-toast__title {
    font-size: 0.95rem;
    font-weight: 700;
}
.php-toast__message {
    margin: 0;
    font-size: 0.9rem;
    line-height: 1.45;
    color: inherit;
    word-break: break-word;
}
.php-toast__close {
    width: 2rem;
    height: 2rem;
    border: none;
    background: transparent;
    color: inherit;
    font-size: 1.2rem;
    line-height: 1;
    cursor: pointer;
    transition: transform 0.2s ease;
}
.php-toast__close:hover {
    transform: scale(1.1);
}
.toast-success {
    border-color: rgba(40, 167, 69, 0.2);
    background: rgba(40, 167, 69, 0.08);
    color: #1f5f2f;
}
.toast-success .php-toast__icon {
    background: rgba(40, 167, 69, 0.16);
    color: #198754;
}
.toast-error {
    border-color: rgba(220, 53, 69, 0.2);
    background: rgba(220, 53, 69, 0.08);
    color: #7a1f25;
}
.toast-error .php-toast__icon {
    background: rgba(220, 53, 69, 0.16);
    color: #dc3545;
}
.toast-warning {
    border-color: rgba(255, 193, 7, 0.2);
    background: rgba(255, 193, 7, 0.12);
    color: #5d4a15;
}
.toast-warning .php-toast__icon {
    background: rgba(255, 193, 7, 0.18);
    color: #ffc107;
}
.toast-info {
    border-color: rgba(13, 110, 253, 0.2);
    background: rgba(13, 110, 253, 0.08);
    color: #173a77;
}
.toast-info .php-toast__icon {
    background: rgba(13, 110, 253, 0.16);
    color: #0d6efd;
}
@keyframes toast-fade-in {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}
@keyframes toast-fade-out {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(20px); }
}
@media (prefers-color-scheme: dark) {
    .php-toast-container {
        color: #f3f4f6;
    }
    .php-toast {
        background: rgba(17, 24, 39, 0.9);
        border-color: rgba(148, 163, 184, 0.18);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.35);
        color: #e2e8f0;
    }
    .php-toast__icon {
        background: rgba(148, 163, 184, 0.12);
    }
    .php-toast__close {
        color: #e2e8f0;
    }
}
@media (max-width: 640px) {
    .php-toast-container {
        left: 0.75rem;
        right: 0.75rem;
        top: 0.75rem;
    }
    .php-toast {
        width: auto;
        padding: 0.9rem 0.95rem;
        grid-template-columns: auto 1fr auto;
    }
}
</style>
<script>
(function () {
    const CONTAINER_ID = 'php-toast-container';
    const AUTO_CLOSE_MS = 4500;
    const MOVE_SELECTOR = '.php-toast';

    function createContainer() {
        let container = document.getElementById(CONTAINER_ID);
        if (!container) {
            container = document.createElement('div');
            container.id = CONTAINER_ID;
            container.className = 'php-toast-container';
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'true');
            document.body.appendChild(container);
        }
        return container;
    }

    function removeToast(toast) {
        if (!toast || toast.classList.contains('php-toast--hide')) {
            return;
        }
        toast.classList.add('php-toast--hide');
        toast.addEventListener('animationend', function () {
            toast.remove();
        }, { once: true });
    }

    function installToast(toast) {
        const container = createContainer();
        if (toast.parentElement !== container) {
            container.appendChild(toast);
        }

        const closeButton = toast.querySelector('.php-toast__close');
        if (closeButton) {
            closeButton.addEventListener('click', function () {
                removeToast(toast);
            });
        }

        setTimeout(function () {
            removeToast(toast);
        }, AUTO_CLOSE_MS);
    }

    function init() {
        document.querySelectorAll(MOVE_SELECTOR).forEach(function (toast) {
            if (toast.closest('#' + CONTAINER_ID)) {
                return;
            }
            installToast(toast);
        });
    }

    const observer = new MutationObserver(function () {
        init();
    });

    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    ready(function () {
        init();
        observer.observe(document.body, { childList: true, subtree: true });
    });
})();
</script>
<div id="php-toast-container" class="php-toast-container" aria-live="polite" aria-atomic="true"></div>
HTML;

        return $assets . $toastHtml;
    }

    return $toastHtml;
}

// Örnek kullanım:
// echo toast('Kayıt başarılı!', 'success');
// echo toast('Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
// echo toast('Bu bir uyarı mesajıdır.', 'warning');
// echo toast('Bilgi mesajı gösteriliyor.', 'info');
