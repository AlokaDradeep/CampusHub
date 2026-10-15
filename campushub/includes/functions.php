<?php
if (!defined('APP_ROOT')) { die('Direct access not allowed'); }
session_start();

require_once __DIR__ . '/../config/db.php';

function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

function format_date($date) {
    $ts = strtotime($date);
    return date('F j, Y', $ts);
}

function get_event_count() {
    global $mysqli;
    $stmt = db_prepare("SELECT COUNT(*) as cnt FROM events WHERE status = ?");
    $s = 'active';
    $stmt->bind_param('s', $s);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)$row['cnt'];
}

function get_student_count() {
    global $mysqli;
    $stmt = db_prepare("SELECT COUNT(*) as cnt FROM students");
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)$row['cnt'];
}

function get_active_announcements_xml($limit = 3) {
    $xml_file = __DIR__ . '/../xml/announcements.xml';
    if (!file_exists($xml_file)) return [];
    $ann = simplexml_load_file($xml_file);
    $items = [];
    foreach ($ann->announcement as $a) {
        $items[] = $a;
        if (count($items) >= $limit) break;
    }
    return $items;
}

?>
