<?php
require_once 'config/database.php';

$board_code = $_GET['board'] ?? '';

// Get board information
$stmt = $pdo->prepare('SELECT * FROM boards WHERE board_code = ?');
$stmt->execute([$board_code]);
$board = $stmt->fetch();

if (!$board) {
    header('Location: index.php');
    exit;
}

// Get threads for this board
$stmt = $pdo->prepare('
    SELECT t.*, 
           (SELECT COUNT(*) FROM posts p WHERE p.thread_id = t.id) as reply_count,
           (SELECT image_path FROM posts p WHERE p.thread_id = t.id ORDER BY created_at DESC LIMIT 1) as last_image
    FROM threads t 
    WHERE t.board_id = ? 
    ORDER BY t.is_sticky DESC, t.last_bump DESC 
    LIMIT 20
');
$stmt->execute([$board['id']]);
$threads = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>/<?= htmlspecialchars($board_code) ?>/ - <?= htmlspecialchars($board['board_name']) ?></title>
    <style>
        body {
            font-family: arial,helvetica,sans-serif;
            font-size: 10pt;
            background: #EEF2FF;
            margin: 0;
            padding: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .thread-form {
            background: #D6DAF0;
            border: 1px solid #B7C5D9;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .threads {
            max-width: 800px;
            margin: 0 auto;
        }
        .thread {
            background: #D6DAF0;
            border: 1px solid #B7C5D9;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .thread-header {
            color: #117743;
            font-weight: bold;
        }
        .thread-content {
            margin: 10px 0;
        }
        .thread-image {
            float: left;
            margin: 0 20px 10px 0;
            max-width: 250px;
        }
        .thread-footer {
            clear: both;
            color: #666;
            font-size: 0.9em;
        }
        input[type="text"], textarea {
            width: 100%;
            margin-bottom: 10px;
            padding: 5px;
        }
        input[type="submit"] {
            background: #D6DAF0;
            border: 1px solid #B7C5D9;
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>/<?= htmlspecialchars($board_code) ?>/ - <?= htmlspecialchars($board['board_name']) ?></h1>
        <p><?= htmlspecialchars($board['description']) ?></p>
        <p><a href="index.php">← Back to index</a></p>
    </div>

    <div class="threads">
        <div class="thread-form">
            <h3>Create New Thread</h3>
            <form action="create_thread.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="board_id" value="<?= $board['id'] ?>">
                <input type="text" name="subject" placeholder="Subject (optional)">
                <textarea name="content" rows="5" required placeholder="Comment"></textarea>
                <input type="file" name="image" accept="image/*" required>
                <input type="submit" value="Post">
            </form>
        </div>

        <?php foreach ($threads as $thread): ?>
        <div class="thread">
            <div class="thread-header">
                <?php if ($thread['subject']): ?>
                    <span class="subject"><?= htmlspecialchars($thread['subject']) ?></span>
                <?php endif; ?>
                <span class="date"><?= htmlspecialchars($thread['created_at']) ?></span>
            </div>
            <?php if ($thread['image_path']): ?>
                <img class="thread-image" src="<?= htmlspecialchars($thread['image_path']) ?>" alt="Thread image">
            <?php endif; ?>
            <div class="thread-content">
                <?= nl2br(htmlspecialchars($thread['content'])) ?>
            </div>
            <div class="thread-footer">
                <span>Replies: <?= $thread['reply_count'] ?></span>
                <a href="thread.php?id=<?= $thread['id'] ?>">View Thread</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
