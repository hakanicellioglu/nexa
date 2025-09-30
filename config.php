<?php
/**
 * Database configuration for WAMP environment.
 *
 * This file creates a mysqli connection to the `nexa` database using
 * typical default WAMP credentials. Update the credentials if your
 * local environment differs.
 */

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'nexa';

$mysqli = new mysqli($host, $username, $password, $database);

if ($mysqli->connect_errno) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

// Ensure UTF-8 encoding is used for the connection.
if (! $mysqli->set_charset('utf8mb4')) {
    die('Error loading character set utf8mb4: ' . $mysqli->error);
}

?>
