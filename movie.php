<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = get_db()->prepare('SELECT m.*, c.name AS category_name FROM movies m LEFT JOIN categories c ON c.id = m.category_id WHERE m.id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$movie = $stmt->fetch();

if (!$movie) {
    http_response_code(404);
    echo '影片不存在';
    exit;
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($movie['title']) ?> - 影视 CMS</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f3f4f6; }
        .container { max-width: 900px; margin: 30px auto; padding: 20px; }
        .card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,.05); }
        .layout { display: grid; grid-template-columns: 280px 1fr; gap: 20px; }
        img { width: 100%; border-radius: 8px; height: 390px; object-fit: cover; }
        a.btn { display: inline-block; margin-top: 12px; padding: 10px 12px; border-radius: 8px; background: #111827; color: white; text-decoration: none; }
        .meta { color: #4b5563; margin: 6px 0; }
    </style>
</head>
<body>
<div class="container">
    <div class="card layout">
        <img src="<?= e($movie['cover_url'] ?: 'https://dummyimage.com/280x400/e5e7eb/374151&text=No+Cover') ?>" alt="<?= e($movie['title']) ?>">
        <div>
            <h1><?= e($movie['title']) ?></h1>
            <p class="meta">分类：<?= e((string)($movie['category_name'] ?? '未分类')) ?></p>
            <p class="meta">地区：<?= e((string)($movie['region'] ?? '-')) ?></p>
            <p class="meta">年份：<?= e((string)($movie['year'] ?? '-')) ?></p>
            <p class="meta">评分：<?= e((string)($movie['score'] ?? '-')) ?></p>
            <h3>剧情简介</h3>
            <p><?= nl2br(e((string)($movie['description'] ?? '暂无简介'))) ?></p>
            <?php if (!empty($movie['source_url'])): ?>
                <a class="btn" target="_blank" rel="noopener" href="<?= e($movie['source_url']) ?>">在线播放</a>
            <?php endif; ?>
            <a class="btn" href="/">返回首页</a>
        </div>
    </div>
</div>
</body>
</html>
