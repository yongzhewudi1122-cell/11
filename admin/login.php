<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';

start_session();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (login($username, $password)) {
        flash('登录成功');
        header('Location: /admin/index.php');
        exit;
    }

    $error = '账号或密码错误';
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台登录</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; }
        .box { width: 340px; margin: 100px auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        input, button { width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 8px; border: 1px solid #d1d5db; }
        button { background: #111827; color: #fff; border-color: #111827; }
        .error { color: #dc2626; }
    </style>
</head>
<body>
<div class="box">
    <h2>影视 CMS 后台</h2>
    <p>默认账号：admin / admin123</p>
    <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
        <input name="username" placeholder="用户名" required>
        <input name="password" type="password" placeholder="密码" required>
        <button type="submit">登录</button>
    </form>
</div>
</body>
</html>
