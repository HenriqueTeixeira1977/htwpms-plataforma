<?php
require_once __DIR__ . '/config.php';

function get_db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $isNew = !file_exists(DB_PATH);
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            module TEXT NOT NULL,
            module_short TEXT NOT NULL,
            module_order INTEGER NOT NULL,
            task_num TEXT NOT NULL,
            task_name TEXT NOT NULL,
            responsible TEXT NOT NULL,
            duration INTEGER NOT NULL,
            start_date TEXT NOT NULL,
            end_date TEXT NOT NULL,
            module_investment REAL NOT NULL,
            status TEXT NOT NULL DEFAULT 'pendente',
            note TEXT DEFAULT '',
            updated_at TEXT
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS project_meta (
            k TEXT PRIMARY KEY,
            v TEXT
        )
    ");

    $count = (int) $pdo->query('SELECT COUNT(*) FROM tasks')->fetchColumn();
    if ($count === 0) {
        seed_tasks($pdo);
    }

    return $pdo;
}

function seed_tasks(PDO $pdo): void
{
    $tasks = require __DIR__ . '/seed_data.php';
    $stmt = $pdo->prepare("
        INSERT INTO tasks
            (module, module_short, module_order, task_num, task_name, responsible, duration, start_date, end_date, module_investment, status, updated_at)
        VALUES
            (:module, :module_short, :module_order, :task_num, :task_name, :responsible, :duration, :start_date, :end_date, :module_investment, 'pendente', :updated_at)
    ");
    $now = date('Y-m-d H:i:s');
    foreach ($tasks as $t) {
        $stmt->execute([
            ':module' => $t['module'],
            ':module_short' => $t['module_short'],
            ':module_order' => $t['module_order'],
            ':task_num' => $t['task_num'],
            ':task_name' => $t['task_name'],
            ':responsible' => $t['responsible'],
            ':duration' => $t['duration'],
            ':start_date' => $t['start_date'],
            ':end_date' => $t['end_date'],
            ':module_investment' => $t['module_investment'],
            ':updated_at' => $now,
        ]);
    }
}
