<?php
require_once 'config/database.php';

// Get all boards
$stmt = $pdo->query('SELECT * FROM boards ORDER BY board_code');
$boards = $stmt->fetchAll();

// Get recent threads (latest 8 for example)
$stmt = $pdo->query('SELECT t.*, b.board_code, b.board_name 
            FROM threads t 
            JOIN boards b ON t.board_id = b.id 
            ORDER BY t.created_at DESC 
            LIMIT 5');
$recent_threads = $stmt->fetchAll();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- HEAD ORIGINAL TIDAK BERUBAH -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="keywords" content="imageboard,forum,anonymous,chan" />
<meta name="description" content="Simple image-based bulletin board where anyone can post comments and share images anonymously." />
<title>4chan</title>
<link rel="stylesheet" type="text/css" href="./css/style.css" />
<script src="./js/preload.js" defer></script>
</head>
<body>
<div id="doc">
  <div id="hd">
    <div id="logo-fp">
      <a href="/" title="Home"><img alt="4chan" src="./assets/logo.png" height="200"></a>
    </div>
  </div>

<!-- BOARDS SECTION -->
<div class="box-outer top-box" id="boards">
  <div class="box-inner">
    <div class="boxbar">
      <h2>Boards</h2>
    </div>
    <div class="boxcontent">
        <?php foreach ($boards as $board): ?>
            <div class="column">
              <div class="board">
                <ul>
                  <li><a href="board.php?board=<?= htmlspecialchars($board['board_code']) ?>">
                    /<?= htmlspecialchars($board['board_code']) ?>/ - <?= htmlspecialchars($board['board_name']) ?>
                  </a></li>
                </ul>
              </div>
            </div>
        <?php endforeach; ?>
        <br class="clear-bug"/>
    </div>
  </div>
</div>

<!-- RECENT THREADS SECTION -->
<div class="box-outer top-box" id="recent-threads">
  <div class="box-inner">
    <div class="boxbar">
      <h2>Recent Threads</h2>
    </div>
    <div class="boxcontent">
      <div id="c-threads">
        <?php foreach ($recent_threads as $thread): ?>
          <div class="c-thread">
            <div class="c-board">/<?= htmlspecialchars($thread['board_code']) ?>/</div>
            <a href="thread.php?id=<?= $thread['id'] ?>" style="color: #34345C; text-decoration: none;">
                <?= htmlspecialchars($thread['subject'] ?: substr($thread['content'], 0, 100) . '...') ?>
            </a>
              <?php if (!empty($thread['thumbnail'])): ?>
                <img class="c-thumb" src="<?= htmlspecialchars($thread['thumbnail']) ?>" width="150" height="150" />
              <?php endif; ?>
            </a>
            <!-- <div class="c-teaser">
              <b><?= htmlspecialchars($thread['title']) ?></b>
            </div> -->
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- STATS SECTION -->
<div class="box-outer top-box" id="site-stats">
  <div class="box-inner">
    <div class="boxbar"><h2>Stats</h2></div>
    <div class="boxcontent">
      <div class="stat-cell"><b>Total Posts:</b> <?php
          $stmt = $pdo->query('SELECT COUNT(*) as count FROM posts');
          echo $stmt->fetch()['count'];
      ?></div>
      <div class="stat-cell"><b>Current Users:</b> <?php
          $stmt = $pdo->query('SELECT COUNT(*) as count FROM (
              SELECT DISTINCT ip_address FROM posts
              UNION
              SELECT DISTINCT ip_address FROM threads
          ) as users');
          echo $stmt->fetch()['count'];
      ?></div>
      <div class="stat-cell"><b>Active Content:</b> <?php
          $stmt = $pdo->query('SELECT COUNT(*) as count FROM threads');
          echo $stmt->fetch()['count'];
      ?></div>
    </div>
  </div>
</div>

<!-- FOOTER -->
<div id="ft">
  <ul>
    <li class="first"><a href="/">Home</a></li>
    <li><a href="/4channews.php">News</a></li>
    <li><a href="http://blog.4chan.org/">Blog</a></li>
    <li><a href="/faq">FAQ</a></li>
    <li><a href="/rules">Rules</a></li>
    <li><a href="/pass">Support</a></li>
    <li><a href="/advertise">Advertise</a></li>
    <li><a href="/press">Press</a></li>
    <li><a href="/japanese">日本語</a></li>
  </ul>
  <br class="clear-bug" />
  <div id="copyright">
    <a href="/faq#what4chan">About</a> &bull;
    <a href="/feedback">Feedback</a> &bull;
    <a href="/legal">Legal</a> &bull;
    <a href="/contact">Contact</a><br /><br />
    Copyright &copy; 2025 alipan and fauzy
  </div>
</div>

</body>
</html>
