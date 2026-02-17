<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/functions.php';

$pdo = get_db();
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$keyword = trim($_GET['keyword'] ?? '');

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY id ASC')->fetchAll();

$sql = 'SELECT m.*, c.name AS category_name
        FROM movies m
        LEFT JOIN categories c ON c.id = m.category_id
        WHERE 1=1';
$params = [];

if ($categoryId > 0) {
    $sql .= ' AND m.category_id = :category_id';
    $params[':category_id'] = $categoryId;
}

if ($keyword !== '') {
    $sql .= ' AND (m.title LIKE :keyword OR m.description LIKE :keyword)';
    $params[':keyword'] = '%' . $keyword . '%';
}

$sql .= ' ORDER BY m.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movies = $stmt->fetchAll();
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>影视 PHP CMS</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f3f4f6; }
        .container { max-width: 1100px; margin: 0 auto; padding: 20px; }
        .card { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 2px 10px rgba(0,0,0,.05); }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
        .movie img { width: 100%; height: 310px; object-fit: cover; border-radius: 8px; }
        .movie h3 { margin: 10px 0 6px; font-size: 18px; }
        .toolbar { display: flex; gap: 10px; margin-bottom: 18px; flex-wrap: wrap; }
        input, select, button, a.btn { padding: 10px 12px; border-radius: 8px; border: 1px solid #d1d5db; text-decoration: none; }
        button, .btn { background: #111827; color: #fff; border-color: #111827; cursor: pointer; }
        .meta { color: #6b7280; font-size: 14px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
    </style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <h1>影视 PHP CMS</h1>
        <a class="btn" href="/admin/login.php">后台管理</a>
    </div>

    <form class="card toolbar" method="get">
        <input type="text" name="keyword" placeholder="搜索影片名称或简介" value="<?= e($keyword) ?>">
        <select name="category">
            <option value="0">全部分类</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int)$category['id'] ?>" <?= $categoryId === (int)$category['id'] ? 'selected' : '' ?>>
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">筛选</button>
    </form>

    <div class="grid">
        <?php foreach ($movies as $movie): ?>
            <article class="card movie">
                <img src="<?= e($movie['cover_url'] ?: 'https://dummyimage.com/280x400/e5e7eb/374151&text=No+Cover') ?>" alt="<?= e($movie['title']) ?>">
                <h3><?= e($movie['title']) ?></h3>
                <p class="meta"><?= e((string)($movie['category_name'] ?? '未分类')) ?> · <?= e((string)($movie['year'] ?? '-')) ?> · <?= e((string)($movie['region'] ?? '-')) ?></p>
                <p class="meta">评分：<?= e((string)($movie['score'] ?? '-')) ?></p>
                <a class="btn" href="/movie.php?id=<?= (int)$movie['id'] ?>">查看详情</a>
            </article>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
