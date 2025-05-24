<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$board_id = $_POST['board_id'] ?? '';
$subject = $_POST['subject'] ?? '';
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

    // Create thread
    $stmt = $pdo->prepare('
        INSERT INTO threads (board_id, subject, content, image_path, ip_address) 
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute([$board_id, $subject, $content, $image_path, $ip_address]);

    $pdo->commit();
    
    // Redirect back to board
    $stmt = $pdo->prepare('SELECT board_code FROM boards WHERE id = ?');
    $stmt->execute([$board_id]);
    $board = $stmt->fetch();
    
    header('Location: board.php?board=' . $board['board_code']);
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    die('Error creating thread: ' . $e->getMessage());
}
