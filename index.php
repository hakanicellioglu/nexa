<?php
// Entry point for Nexa platform

session_start();

if (!empty($_SESSION['user']) || !empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

header('Location: login.php');
exit;

