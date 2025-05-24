<?php
require_once 'config/database.php';

// Get all boards
$stmt = $pdo->query('SELECT * FROM boards ORDER BY board_code');
$boards = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>/g/ - Forum Gelo</title>
    <style>
        body {
            font-family: arial,helvetica,sans-serif;
            font-size: 10pt;
            background: #EEF2FF;
            color: #000000;
            margin: 0;
            padding: 8px;
        }

        .header {
            text-align: center;
            color: #AF0A0F;
            font-size: 24px;
            font-weight: bold;
            font-family: Tahoma,sans-serif;
            letter-spacing: -2px;
            margin-top: 8px;
            margin-bottom: 16px;
        }

        .header a {
            color: #34345C;
            text-decoration: none;
        }

        .header a:hover {
            color: #ff0000;
        }

        .boards {
            background: #D6DAF0;
            border: 1px solid #B7C5D9;
            border-radius: 3px;
            color: #34345C;
            padding: 16px;
            margin: 8px auto;
            max-width: 800px;
        }

        .boards h2 {
            color: #34345C;
            font-size: 12pt;
            margin-bottom: 8px;
            border-bottom: 1px solid #B7C5D9;
            padding-bottom: 4px;
        }

        .board-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .board {
            background: #EEF2FF;
            border: 1px solid #B7C5D9;
            border-radius: 3px;
            padding: 6px 12px;
            width: calc(25% - 6px);
        }

        .board:hover {
            background: #D6DAF0;
        }

        .board a {
            color: #34345C;
            text-decoration: none;
            font-weight: bold;
        }

        .board a:hover {
            color: #ff0000;
        }

        .board-description {
            color: #707070;
            font-size: 9pt;
            margin-top: 2px;
        }

        .stats {
            background: #D6DAF0;
            border: 1px solid #B7C5D9;
            border-radius: 3px;
            margin: 8px auto;
            padding: 8px;
            max-width: 800px;
            font-size: 9pt;
            color: #707070;
        }

        .stat-list {
            display: flex;
            justify-content: space-around;
            text-align: center;
        }

        .stat-item {
            padding: 4px 8px;
        }

        .stat-item b {
            color: #34345C;
            display: block;
            font-size: 14px;
        }

        .recent-posts {
            background: #D6DAF0;
            border: 1px solid #B7C5D9;
            border-radius: 3px;
            margin: 8px auto;
            padding: 8px;
            max-width: 800px;
        }

        .recent-posts h2 {
            color: #34345C;
            font-size: 11pt;
            margin-bottom: 8px;
            border-bottom: 1px solid #B7C5D9;
            padding-bottom: 4px;
        }

        .post-item {
            background: #EEF2FF;
            border: 1px solid #B7C5D9;
            border-radius: 3px;
            padding: 6px;
            margin-bottom: 4px;
            font-size: 9pt;
        }

        .post-item:hover {
            background: #D6DAF0;
        }

        .post-board {
            color: #34345C;
            font-weight: bold;
        }

        .post-date {
            color: #707070;
            font-size: 9pt;
        }

        .footer {
            text-align: center;
            font-size: 9pt;
            color: #707070;
            margin-top: 16px;
            padding: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>[Forum Gelo]</h1>
    </div>

    <div class="stats">
        <div class="stat-list">
            <div class="stat-item">
                <b><?php
                    $stmt = $pdo->query('SELECT COUNT(*) as count FROM (SELECT DISTINCT ip_address FROM posts UNION SELECT DISTINCT ip_address FROM threads) as users');
                    echo $stmt->fetch()['count'];
                ?></b>
                User(s) Online
            </div>
            <div class="stat-item">
                <b><?php
                    $stmt = $pdo->query('SELECT COUNT(*) as count FROM posts');
                    echo $stmt->fetch()['count'];
                ?></b>
                Posts
            </div>
            <div class="stat-item">
                <b><?php
                    $stmt = $pdo->query('SELECT COUNT(*) as count FROM threads');
                    echo $stmt->fetch()['count'];
                ?></b>
                Threads
            </div>
        </div>
    </div>

    <div class="boards">
        <h2>Boards</h2>
        <div class="board-list">
            <?php foreach ($boards as $board): ?>
            <div class="board">
                <a href="board.php?board=<?= htmlspecialchars($board['board_code']) ?>">
                    /<?= htmlspecialchars($board['board_code']) ?>/ - <?= htmlspecialchars($board['board_name']) ?>
                </a>
                <div class="board-description">
                    <?= htmlspecialchars($board['description']) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="recent-posts">
        <h2>Recent Posts</h2>
        <?php
        $stmt = $pdo->query('
            SELECT t.*, b.board_code, b.board_name 
            FROM threads t 
            JOIN boards b ON t.board_id = b.id 
            ORDER BY t.created_at DESC 
            LIMIT 5
        ');
        $recent_threads = $stmt->fetchAll();
        
        foreach ($recent_threads as $thread):
        ?>
        <div class="post-item">
            <span class="post-board">/<?= htmlspecialchars($thread['board_code']) ?>/</span> - 
            <a href="thread.php?id=<?= $thread['id'] ?>" style="color: #34345C; text-decoration: none;">
                <?= htmlspecialchars($thread['subject'] ?: substr($thread['content'], 0, 100) . '...') ?>
            </a>
            <span class="post-date"><?= timeAgo(strtotime($thread['created_at'])) ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="footer">
        <p>[<?= date('Y') ?>] Forum Gelo - All trademarks and copyrights belong to their respective owners.</p>
    </div>
    </div>

    <script>
        // Animasi sederhana untuk stat cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-10px)';
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>

<?php
function timeAgo($timestamp) {
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return "Baru saja";
    } elseif ($difference < 3600) {
        return floor($difference/60) . " menit yang lalu";
    } elseif ($difference < 86400) {
        return floor($difference/3600) . " jam yang lalu";
    } elseif ($difference < 2592000) {
        return floor($difference/86400) . " hari yang lalu";
    } else {
        return date('j M Y', $timestamp);
    }
}
?>
