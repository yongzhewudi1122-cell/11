<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';

require_login();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_category') {
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            $stmt = $pdo->prepare('INSERT INTO categories (name, created_at) VALUES (:name, :created_at)');
            $stmt->execute([':name' => $name, ':created_at' => date('c')]);
            flash('分类已新增');
        }
        header('Location: /admin/index.php');
        exit;
    }

    if ($action === 'save_movie') {
        $id = (int)($_POST['id'] ?? 0);
        $payload = [
            ':title' => trim($_POST['title'] ?? ''),
            ':category_id' => (int)($_POST['category_id'] ?? 0) ?: null,
            ':region' => trim($_POST['region'] ?? ''),
            ':year' => (int)($_POST['year'] ?? 0) ?: null,
            ':score' => (float)($_POST['score'] ?? 0) ?: null,
            ':cover_url' => trim($_POST['cover_url'] ?? ''),
            ':description' => trim($_POST['description'] ?? ''),
            ':source_url' => trim($_POST['source_url'] ?? ''),
            ':updated_at' => date('c'),
        ];

        if ($id > 0) {
            $payload[':id'] = $id;
            $sql = 'UPDATE movies SET
                        title = :title,
                        category_id = :category_id,
                        region = :region,
                        year = :year,
                        score = :score,
                        cover_url = :cover_url,
                        description = :description,
                        source_url = :source_url,
                        updated_at = :updated_at
                    WHERE id = :id';
            $pdo->prepare($sql)->execute($payload);
            flash('影片已更新');
        } else {
            $payload[':created_at'] = date('c');
            $sql = 'INSERT INTO movies (title, category_id, region, year, score, cover_url, description, source_url, created_at, updated_at)
                    VALUES (:title, :category_id, :region, :year, :score, :cover_url, :description, :source_url, :created_at, :updated_at)';
            $pdo->prepare($sql)->execute($payload);
            flash('影片已新增');
        }

        header('Location: /admin/index.php');
        exit;
    }

    if ($action === 'delete_movie') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM movies WHERE id = :id')->execute([':id' => $id]);
            flash('影片已删除');
        }
        header('Location: /admin/index.php');
        exit;
    }
}

$flash = flash();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY id DESC')->fetchAll();
$movies = $pdo->query('SELECT m.id, m.title, m.year, m.score, c.name AS category_name FROM movies m LEFT JOIN categories c ON c.id = m.category_id ORDER BY m.id DESC')->fetchAll();
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = null;

if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM movies WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $editId]);
    $editing = $stmt->fetch();
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f3f4f6; }
        .container { max-width: 1100px; margin: 0 auto; padding: 20px; }
        .card { background: #fff; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        input, textarea, select, button { width: 100%; padding: 9px; border-radius: 8px; border: 1px solid #d1d5db; margin-bottom: 10px; }
        button { background: #111827; color: #fff; border-color: #111827; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        .top { display: flex; justify-content: space-between; align-items: center; }
        .msg { color: #16a34a; }
        .danger { background: #b91c1c; border-color: #b91c1c; }
        a.btn { padding: 6px 10px; border-radius: 6px; background: #374151; color: white; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <div class="top">
        <h1>影视 CMS 后台</h1>
        <div>
            <a class="btn" href="/">前台首页</a>
            <a class="btn" href="/admin/logout.php">退出</a>
        </div>
    </div>

    <?php if ($flash): ?><p class="msg"><?= e($flash) ?></p><?php endif; ?>

    <div class="grid">
        <section class="card">
            <h2><?= $editing ? '编辑影片' : '新增影片' ?></h2>
            <form method="post">
                <input type="hidden" name="action" value="save_movie">
                <input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">
                <input name="title" placeholder="影片名称" value="<?= e((string)($editing['title'] ?? '')) ?>" required>
                <select name="category_id">
                    <option value="">选择分类</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int)$category['id'] ?>" <?= (int)($editing['category_id'] ?? 0) === (int)$category['id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input name="region" placeholder="地区" value="<?= e((string)($editing['region'] ?? '')) ?>">
                <input name="year" type="number" placeholder="年份" value="<?= e((string)($editing['year'] ?? '')) ?>">
                <input name="score" type="number" step="0.1" min="0" max="10" placeholder="评分" value="<?= e((string)($editing['score'] ?? '')) ?>">
                <input name="cover_url" placeholder="封面 URL" value="<?= e((string)($editing['cover_url'] ?? '')) ?>">
                <input name="source_url" placeholder="播放地址 URL" value="<?= e((string)($editing['source_url'] ?? '')) ?>">
                <textarea name="description" rows="4" placeholder="简介"><?= e((string)($editing['description'] ?? '')) ?></textarea>
                <button type="submit">保存</button>
            </form>
        </section>

        <section class="card">
            <h2>分类管理</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_category">
                <input name="name" placeholder="新增分类名称" required>
                <button type="submit">新增分类</button>
            </form>
            <h3>分类列表</h3>
            <ul>
                <?php foreach ($categories as $category): ?>
                    <li><?= e($category['name']) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>

    <section class="card">
        <h2>影片列表</h2>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>名称</th>
                <th>分类</th>
                <th>年份</th>
                <th>评分</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($movies as $movie): ?>
                <tr>
                    <td><?= (int)$movie['id'] ?></td>
                    <td><?= e($movie['title']) ?></td>
                    <td><?= e((string)($movie['category_name'] ?? '未分类')) ?></td>
                    <td><?= e((string)($movie['year'] ?? '-')) ?></td>
                    <td><?= e((string)($movie['score'] ?? '-')) ?></td>
                    <td>
                        <a class="btn" href="/admin/index.php?edit=<?= (int)$movie['id'] ?>">编辑</a>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="delete_movie">
                            <input type="hidden" name="id" value="<?= (int)$movie['id'] ?>">
                            <button class="danger" type="submit" onclick="return confirm('确定删除该影片吗？')">删除</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
</body>
</html>
