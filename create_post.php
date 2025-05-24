<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$thread_id = $_POST['thread_id'] ?? '';
$content = $_POST['content'] ?? '';
$ip_address = $_SERVER['REMOTE_ADDR'];

// Check for bans
$stmt = $pdo->prepare('SELECT * FROM bans WHERE ip_address = ? AND (expires_at > NOW() OR expires_at IS NULL)');
$stmt->execute([$ip_address]);
if ($stmt->fetch()) {
    die('You are banned from posting.');
}

// Handle file upload
$image_path = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        die('Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.');
    }

    $new_filename = uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        $image_path = $upload_path;
    } else {
        die('Failed to upload image.');
    }
}

try {
    $pdo->beginTransaction();

    // Create post
    $stmt = $pdo->prepare('
        INSERT INTO posts (thread_id, content, image_path, ip_address) 
        VALUES (?, ?, ?, ?)
    ');
    $stmt->execute([$thread_id, $content, $image_path, $ip_address]);

    // Update thread's last_bump time
    $stmt = $pdo->prepare('UPDATE threads SET last_bump = CURRENT_TIMESTAMP WHERE id = ?');
    $stmt->execute([$thread_id]);

    $pdo->commit();
    
    header('Location: thread.php?id=' . $thread_id);
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die('Error creating post: ' . $e->getMessage());
}
