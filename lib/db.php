<?php

declare(strict_types=1);

function get_db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dbDir = __DIR__ . '/../data';
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0775, true);
    }

    $dbPath = $dbDir . '/cms.sqlite';
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    initialize_schema($pdo);

    return $pdo;
}

function initialize_schema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            created_at TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            created_at TEXT NOT NULL
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS movies (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            category_id INTEGER,
            region TEXT,
            year INTEGER,
            score REAL,
            cover_url TEXT,
            description TEXT,
            source_url TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            FOREIGN KEY(category_id) REFERENCES categories(id)
        )'
    );

    $adminExists = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    if ($adminExists === 0) {
        $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash, created_at) VALUES (:username, :password_hash, :created_at)');
        $stmt->execute([
            ':username' => 'admin',
            ':password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            ':created_at' => date('c'),
        ]);
    }

    $catExists = (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    if ($catExists === 0) {
        $now = date('c');
        $stmt = $pdo->prepare('INSERT INTO categories (name, created_at) VALUES (:name, :created_at)');
        foreach (['电影', '电视剧', '综艺', '动漫'] as $name) {
            $stmt->execute([':name' => $name, ':created_at' => $now]);
        }
    }

    $movieExists = (int)$pdo->query('SELECT COUNT(*) FROM movies')->fetchColumn();
    if ($movieExists === 0) {
        $now = date('c');
        $stmt = $pdo->prepare('INSERT INTO movies (title, category_id, region, year, score, cover_url, description, source_url, created_at, updated_at)
            VALUES (:title, :category_id, :region, :year, :score, :cover_url, :description, :source_url, :created_at, :updated_at)');
        $stmt->execute([
            ':title' => '示例影片：流浪地球',
            ':category_id' => 1,
            ':region' => '中国大陆',
            ':year' => 2019,
            ':score' => 7.9,
            ':cover_url' => 'https://dummyimage.com/280x400/1f2937/ffffff&text=Movie',
            ':description' => '这是系统自动生成的示例数据，用于展示影视 CMS 首页和详情页。',
            ':source_url' => 'https://example.com/play/1',
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
    }
}
