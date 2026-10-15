<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../config/db.php';

$email = 'testuser@campushub.test';
$password = 'password123';

$stmt = $mysqli->prepare('SELECT id,full_name,password,role,created_at FROM students WHERE email = ? LIMIT 1');
if (!$stmt) { echo "Prepare failed: " . $mysqli->error . "\n"; exit(1); }
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 1) {
    $row = $res->fetch_assoc();
    echo "Found user: " . $row['id'] . " - " . $row['full_name'] . " (" . $row['role'] . ")\n";
    echo "Hash: " . $row['password'] . "\n";
    echo "Verify password123: ";
    echo (password_verify($password, $row['password']) ? "OK\n" : "FAIL\n");
} else {
    echo "User not found\n";
}
