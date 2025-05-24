<?php
require_once 'config/database.php';

$thread_id = $_GET['id'] ?? '';

// Get thread information
$stmt = $pdo->prepare('
    SELECT t.*, b.board_code, b.board_name 
    FROM threads t 
    JOIN boards b ON t.board_id = b.id 
    WHERE t.id = ?
');
$stmt->execute([$thread_id]);
$thread = $stmt->fetch();

if (!$thread) {
    header('Location: index.php');
    exit;
}

// Get posts for this thread
$stmt = $pdo->prepare('SELECT * FROM posts WHERE thread_id = ? ORDER BY created_at');
$stmt->execute([$thread_id]);
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thread #<?= htmlspecialchars($thread_id) ?> - /<?= htmlspecialchars($thread['board_code']) ?>/</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2B3A67;
            --secondary-color: #496A81;
            --background-color: #F0F2F5;
            --post-bg: #FFFFFF;
            --border-color: #E4E6EB;
            --text-primary: #1C1E21;
            --text-secondary: #65676B;
            --accent-color: #1A73E8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: var(--text-primary);
            background: var(--background-color);
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--post-bg);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: var(--primary-color);
            font-size: 24px;
            margin-bottom: 10px;
        }

        .header a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .header a:hover {
            color: var(--secondary-color);
        }

        .thread, .post {
            background: var(--post-bg);
            border: 1px solid var(--border-color);
            padding: 24px;
            margin-bottom: 20px;
            border-radius: 12px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .thread:hover, .post:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .thread-header, .post-header {
            color: var(--text-secondary);
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .subject {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 16px;
        }

        .date {
            color: var(--text-secondary);
            font-size: 12px;
        }

        .comment-count {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-left: 15px;
            font-size: 12px;
        }

        .comment-count a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s;
        }

        .comment-count a:hover {
            color: var(--accent-color);
        }

        .comment-count i {
            color: var(--accent-color);
            font-size: 14px;
        }

        .post-form {
            background: var(--post-bg);
            border: 1px solid var(--border-color);
            padding: 24px;
            margin: 20px auto;
            border-radius: 12px;
            max-width: 800px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .post-form h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 600;
        }

        .thread-image, .post-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
            display: block;
        }

        .content {
            color: var(--text-primary);
            line-height: 1.6;
            margin: 15px 0;
        }

        input[type="text"], textarea {
            width: 100%;
            margin-bottom: 15px;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
            background: var(--background-color);
        }

        input[type="text"]:focus, textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(26,115,232,0.2);
        }

        input[type="submit"], button {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        input[type="submit"]:hover, button:hover {
            background: #1557B0;
        }

        input[type="file"] {
            margin: 10px 0;
        }
        

    .report-btn {
        background: var(--accent-color);
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        transition: background-color 0.2s;
    }

    .report-btn:hover {
        background: #1557B0;
    }

    .report-form {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: var(--post-bg);
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        z-index: 1000;
        width: 90%;
        max-width: 500px;
    }

    .report-form h3 {
        color: var(--primary-color);
        margin-bottom: 15px;
        font-weight: 600;
    }

    .report-form textarea {
        width: 100%;
        margin-bottom: 15px;
        padding: 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        resize: vertical;
    }

    .report-form button {
        margin-right: 10px;
    }

    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 999;
    }
    </style>
    <script>
    function showReportForm(postId, threadId) {
        document.getElementById('overlay').style.display = 'block';
        document.getElementById('reportForm').style.display = 'block';
        document.getElementById('reportPostId').value = postId || '';
        document.getElementById('reportThreadId').value = threadId || '';
    }

    function hideReportForm() {
        document.getElementById('overlay').style.display = 'none';
        document.getElementById('reportForm').style.display = 'none';
    }
    </script>
</head>
<body>
    <div class="header">
        <h1>Thread #<?= htmlspecialchars($thread_id) ?> - /<?= htmlspecialchars($thread['board_code']) ?>/</h1>
        <p><a href="board.php?board=<?= htmlspecialchars($thread['board_code']) ?>">← Back to board</a></p>
    </div>

    <div class="thread">
        <div class="thread-header">
            <div>
                <?php if ($thread['subject']): ?>
                    <span class="subject"><?= htmlspecialchars($thread['subject']) ?></span>
                <?php endif; ?>
                <span class="comment-count">
                    <i class="fas fa-comments"></i>
                    <a href="#disqus_thread" class="disqus-comment-count" data-disqus-identifier="thread-<?= $thread_id ?>">0 Comments</a>
                </span>
            </div>
            <span class="date"><?= htmlspecialchars($thread['created_at']) ?></span>
        </div>
        <?php if ($thread['image_path']): ?>
            <img class="thread-image" src="<?= htmlspecialchars($thread['image_path']) ?>" alt="Thread image">
        <?php endif; ?>
        <div class="content">
            <?= nl2br(htmlspecialchars($thread['content'])) ?>
        </div>
    </div>

    <?php foreach ($posts as $post): ?>
    <div class="post">
        <div class="post-header">
            <span class="date"><?= htmlspecialchars($post['created_at']) ?></span>
            <button onclick="showReportForm(<?= $post['id'] ?>, null)" class="report-btn">Report</button>
        </div>
        <?php if ($post['image_path']): ?>
            <img class="post-image" src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post image">
        <?php endif; ?>
        <div class="content">
            <?= nl2br(htmlspecialchars($post['content'])) ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div id="overlay" class="overlay" onclick="hideReportForm()"></div>
    <div id="reportForm" class="report-form">
        <h3>Report Content</h3>
        <form action="report.php" method="POST">
            <input type="hidden" name="post_id" id="reportPostId">
            <input type="hidden" name="thread_id" id="reportThreadId">
            <textarea name="reason" rows="4" required placeholder="Reason for report"></textarea><br>
            <input type="submit" value="Submit Report">
            <button type="button" onclick="hideReportForm()">Cancel</button>
        </form>
    </div>

    <!-- Disqus Comments -->
    <div class="thread" style="margin-top: 20px;">
        <div id="disqus_thread"></div>
    </div>
    <script>
        /**
        *  RECOMMENDED CONFIGURATION VARIABLES: EDIT AND UNCOMMENT THE SECTION BELOW TO INSERT DYNAMIC VALUES FROM YOUR PLATFORM OR CMS.
        *  LEARN WHY DEFINING THESE VARIABLES IS IMPORTANT: https://disqus.com/admin/universalcode/#configuration-variables    */
        /*
        var disqus_config = function () {
        this.page.url = PAGE_URL;  // Replace PAGE_URL with your page's canonical URL variable
        this.page.identifier = PAGE_IDENTIFIER; // Replace PAGE_IDENTIFIER with your page's unique identifier variable
        };
        */
        (function() { // DON'T EDIT BELOW THIS LINE
        var d = document, s = d.createElement('script');
        s.src = 'https://forum-eax3y18vcb.disqus.com/embed.js';
        s.setAttribute('data-timestamp', +new Date());
        (d.head || d.body).appendChild(s);
        })();
    </script>
    <!-- Script untuk menampilkan jumlah komentar -->
    <script id="dsq-count-scr" src="//forum-eax3y18vcb.disqus.com/count.js" async></script>
    <noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
</body>
</html>
