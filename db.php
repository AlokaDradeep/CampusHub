<?php
// Prevent direct access
if (!defined('APP_ROOT')) { die('Direct access not allowed'); }

$db_host = 'localhost';
$db_user = '';
$db_pass = '';
$db_name = 'campushub';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

// set charset
$mysqli->set_charset('utf8mb4');

// Helper to prepare statements more concisely
function db_prepare($query) {
    global $mysqli;
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        die('MySQL prepare error: ' . $mysqli->error);
    }
    return $stmt;
}

?>
