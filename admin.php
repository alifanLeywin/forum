<?php
require_once 'config/database.php';

// Basic authentication (you should implement proper authentication)
$admin_password = 'admin123'; // Change this to a secure password
session_start();

if (!isset($_SESSION['admin']) && (!isset($_POST['password']) || $_POST['password'] !== $admin_password)) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login</title>
        <style>
            body {
                font-family: arial,helvetica,sans-serif;
                background: #EEF2FF;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .login-form {
                background: #D6DAF0;
                padding: 20px;
                border: 1px solid #B7C5D9;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        <div class="login-form">
            <h2>Admin Login</h2>
            <form method="POST">
                <input type="password" name="password" required>
                <input type="submit" value="Login">
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
} else if (isset($_POST['password']) && $_POST['password'] === $admin_password) {
    $_SESSION['admin'] = true;
}

// Handle actions
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'delete_thread':
            $stmt = $pdo->prepare('DELETE FROM threads WHERE id = ?');
            $stmt->execute([$_POST['thread_id']]);
            break;
            
        case 'delete_post':
            $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
            $stmt->execute([$_POST['post_id']]);
            break;
            
        case 'ban_ip':
            $stmt = $pdo->prepare('
                INSERT INTO bans (ip_address, reason, expires_at) 
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? DAY))
            ');
            $stmt->execute([
                $_POST['ip_address'],
                $_POST['reason'],
                $_POST['ban_duration']
            ]);
            break;
            
        case 'resolve_report':
            $stmt = $pdo->prepare('UPDATE reports SET status = ? WHERE id = ?');
            $stmt->execute(['resolved', $_POST['report_id']]);
            break;
    }
}

// Get pending reports
$stmt = $pdo->query('
    SELECT r.*, 
           t.subject as thread_subject,
           t.content as thread_content,
           p.content as post_content
    FROM reports r
    LEFT JOIN threads t ON r.thread_id = t.id
    LEFT JOIN posts p ON r.post_id = p.id
    WHERE r.status = "pending"
    ORDER BY r.created_at DESC
');
$reports = $stmt->fetchAll();

// Get recent bans
$stmt = $pdo->query('
    SELECT * FROM bans 
    ORDER BY created_at DESC 
    LIMIT 10
');
$bans = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <style>
        body {
            font-family: arial,helvetica,sans-serif;
            background: #EEF2FF;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .panel {
            background: #D6DAF0;
            border: 1px solid #B7C5D9;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .report {
            border-bottom: 1px solid #B7C5D9;
            padding: 10px 0;
        }
        .actions {
            margin-top: 10px;
        }
        .actions form {
            display: inline-block;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Panel</h1>
        
        <div class="panel">
            <h2>Pending Reports</h2>
            <?php foreach ($reports as $report): ?>
            <div class="report">
                <p><strong>Reported:</strong> <?= htmlspecialchars($report['created_at']) ?></p>
                <p><strong>Reason:</strong> <?= htmlspecialchars($report['reason']) ?></p>
                <p><strong>Reporter IP:</strong> <?= htmlspecialchars($report['ip_address']) ?></p>
                
                <?php if ($report['thread_id']): ?>
                    <p><strong>Reported Thread:</strong></p>
                    <p>Subject: <?= htmlspecialchars($report['thread_subject']) ?></p>
                    <p>Content: <?= htmlspecialchars($report['thread_content']) ?></p>
                <?php endif; ?>
                
                <?php if ($report['post_id']): ?>
                    <p><strong>Reported Post:</strong></p>
                    <p>Content: <?= htmlspecialchars($report['post_content']) ?></p>
                <?php endif; ?>
                
                <div class="actions">
                    <?php if ($report['thread_id']): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="delete_thread">
                        <input type="hidden" name="thread_id" value="<?= $report['thread_id'] ?>">
                        <input type="submit" value="Delete Thread">
                    </form>
                    <?php endif; ?>
                    
                    <?php if ($report['post_id']): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="delete_post">
                        <input type="hidden" name="post_id" value="<?= $report['post_id'] ?>">
                        <input type="submit" value="Delete Post">
                    </form>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="ban_ip">
                        <input type="hidden" name="ip_address" value="<?= htmlspecialchars($report['ip_address']) ?>">
                        <input type="text" name="reason" placeholder="Ban reason" required>
                        <input type="number" name="ban_duration" placeholder="Ban duration (days)" required>
                        <input type="submit" value="Ban IP">
                    </form>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="resolve_report">
                        <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                        <input type="submit" value="Mark Resolved">
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="panel">
            <h2>Recent Bans</h2>
            <table width="100%">
                <tr>
                    <th>IP Address</th>
                    <th>Reason</th>
                    <th>Expires</th>
                    <th>Created</th>
                </tr>
                <?php foreach ($bans as $ban): ?>
                <tr>
                    <td><?= htmlspecialchars($ban['ip_address']) ?></td>
                    <td><?= htmlspecialchars($ban['reason']) ?></td>
                    <td><?= htmlspecialchars($ban['expires_at']) ?></td>
                    <td><?= htmlspecialchars($ban['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>
