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
        <div class="flash-message flash-message--<?php echo htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8'); ?>">
            <span class="flash-message__text">
                <?php echo htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>
    <?php endforeach; ?>
</div>
