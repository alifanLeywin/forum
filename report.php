<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$post_id = $_POST['post_id'] ?? null;
$thread_id = $_POST['thread_id'] ?? null;
$reason = $_POST['reason'] ?? '';
$ip_address = $_SERVER['REMOTE_ADDR'];

if (!$reason) {
    die('Please provide a reason for the report.');
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO reports (post_id, thread_id, reason, ip_address) 
        VALUES (?, ?, ?, ?)
    ');
    $stmt->execute([$post_id, $thread_id, $reason, $ip_address]);

    if ($thread_id) {
        header('Location: thread.php?id=' . $thread_id);
    } else {
        header('Location: index.php');
    }
    exit;
} catch (Exception $e) {
    die('Error creating report: ' . $e->getMessage());
}
