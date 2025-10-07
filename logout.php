<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

session_regenerate_id(true);
$_SESSION = [];
session_destroy();

header('Location: /');
exit;
