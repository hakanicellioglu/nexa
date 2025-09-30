<?php
// Flash message partial for Nexa platform
// Usage: set $_SESSION['flash'] to a string or array of messages before including this file.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$flashMessages = $_SESSION['flash'] ?? [];

if (empty($flashMessages)) {
    return;
}

unset($_SESSION['flash']);

if (!is_array($flashMessages)) {
    $flashMessages = [
        [
            'type' => 'info',
            'message' => (string) $flashMessages,
        ],
    ];
} elseif (isset($flashMessages['message'])) {
    $flashMessages = [
        [
            'type' => $flashMessages['type'] ?? 'info',
            'message' => (string) $flashMessages['message'],
        ],
    ];
} else {
    $flashMessages = array_map(
        function ($flash) {
            if (is_string($flash)) {
                return [
                    'type' => 'info',
                    'message' => $flash,
                ];
            }

            return [
                'type' => $flash['type'] ?? 'info',
                'message' => (string) ($flash['message'] ?? ''),
            ];
        },
        $flashMessages
    );
}
?>
<div class="flash-messages" role="status" aria-live="polite">
    <?php foreach ($flashMessages as $flash): ?>
        <?php if (empty($flash['message'])) { continue; } ?>
        <div
            class="flash-message flash-message--<?php echo htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8'); ?>"
            data-duration="5000"
        >
            <span class="flash-message__text">
                <?php echo htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>
    <?php endforeach; ?>
</div>
<style>
    .flash-messages {
        position: fixed;
        top: 1.5rem;
        right: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        max-width: min(90vw, 360px);
        z-index: 9999;
        pointer-events: none;
    }

    .flash-message {
        pointer-events: auto;
        padding: 0.875rem 1.125rem;
        border-radius: 0.75rem;
        background-color: rgba(34, 197, 94, 0.12);
        border: 1px solid rgba(34, 197, 94, 0.35);
        color: #047857;
        font-weight: 600;
        box-shadow: 0 12px 24px -12px rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(10px);
        transition: opacity 220ms ease, transform 220ms ease;
    }

    .flash-message--info {
        background-color: rgba(59, 130, 246, 0.12);
        border-color: rgba(59, 130, 246, 0.35);
        color: #1d4ed8;
    }

    .flash-message--warning {
        background-color: rgba(251, 191, 36, 0.18);
        border-color: rgba(217, 119, 6, 0.35);
        color: #b45309;
    }

    .flash-message--error,
    .flash-message--danger {
        background-color: rgba(239, 68, 68, 0.14);
        border-color: rgba(220, 38, 38, 0.35);
        color: #b91c1c;
    }

    .flash-message--success {
        background-color: rgba(34, 197, 94, 0.12);
        border-color: rgba(34, 197, 94, 0.35);
        color: #047857;
    }

    .flash-message--is-exiting {
        opacity: 0;
        transform: translateY(-10px);
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var flashMessages = document.querySelectorAll('.flash-messages .flash-message');

        flashMessages.forEach(function (message, index) {
            var duration = Number(message.dataset.duration || 0);
            var timeout = duration > 0 ? duration : 5000;

            setTimeout(function () {
                message.classList.add('flash-message--is-exiting');

                message.addEventListener('transitionend', function () {
                    message.remove();
                }, { once: true });
            }, timeout + index * 250);
        });
    });
</script>
