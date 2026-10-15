<?php
// CLI helper to create an admin user in the local Campushub database.
// Usage: php create_admin_cli.php "Full Name" "email@example.test" "password"

define('APP_ROOT', true);
require_once __DIR__ . '/../config/db.php';

if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

if ($argc < 4) {
    echo "Usage: php create_admin_cli.php \"Full Name\" \"email@example.test\" \"password\"\n";
    exit(1);
}

$full_name = trim($argv[1]);
$email = trim($argv[2]);
$password = $argv[3];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email address.\n";
    exit(1);
}

global $mysqli;

$stmt = $mysqli->prepare('SELECT id FROM students WHERE email = ? LIMIT 1');
if (!$stmt) { echo "Prepare failed: " . $mysqli->error . "\n"; exit(1); }
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    echo "A user with that email already exists.\n";
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'admin';
$stmt2 = $mysqli->prepare('INSERT INTO students (full_name, email, password, role) VALUES (?,?,?,?)');
if (!$stmt2) { echo "Prepare failed: " . $mysqli->error . "\n"; exit(1); }
$stmt2->bind_param('ssss', $full_name, $email, $hash, $role);
if ($stmt2->execute()) {
    echo "Admin user created with ID: " . $mysqli->insert_id . "\n";
} else {
    echo "Insert failed: " . $stmt2->error . "\n";
}
