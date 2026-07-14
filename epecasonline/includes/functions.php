<?php
require_once __DIR__ . '/db.php';

const STATUS_LABELS = [
    'pendente'    => 'Pendente',
    'andamento'   => 'Em andamento',
    'concluido'   => 'Concluído',
];

const STATUS_BADGE_CLASS = [
    'pendente'    => 'status-pendente',
    'andamento'   => 'status-andamento',
    'concluido'   => 'status-concluido',
];

const MODULE_COLOR_VARS = [
    'M1' => '--mod-m1',
    'M2' => '--mod-m2',
    'M3' => '--mod-m3',
    'M4' => '--mod-m4',
    'M5' => '--mod-m5',
];

function fetch_all_tasks(): array
{
    $pdo = get_db();
    return $pdo->query('SELECT * FROM tasks ORDER BY module_order ASC, start_date ASC, task_num ASC')->fetchAll(PDO::FETCH_ASSOC);
}

function group_by_module(array $tasks): array
{
    $modules = [];
    foreach ($tasks as $t) {
        $key = $t['module_short'];
        if (!isset($modules[$key])) {
            $modules[$key] = [
                'module_short' => $t['module_short'],
                'module' => $t['module'],
                'module_order' => $t['module_order'],
                'investment' => $t['module_investment'],
                'tasks' => [],
                'start_date' => $t['start_date'],
                'end_date' => $t['end_date'],
            ];
        }
        $modules[$key]['tasks'][] = $t;
        if ($t['start_date'] < $modules[$key]['start_date']) {
            $modules[$key]['start_date'] = $t['start_date'];
        }
        if ($t['end_date'] > $modules[$key]['end_date']) {
            $modules[$key]['end_date'] = $t['end_date'];
        }
    }
    uasort($modules, fn($a, $b) => $a['module_order'] <=> $b['module_order']);
    return $modules;
}

function module_progress(array $moduleTasks): array
{
    $total = count($moduleTasks);
    $done = 0;
    $inProgress = 0;
    foreach ($moduleTasks as $t) {
        if ($t['status'] === 'concluido') $done++;
        if ($t['status'] === 'andamento') $inProgress++;
    }
    $pct = $total > 0 ? round(($done / $total) * 100) : 0;
    return ['total' => $total, 'done' => $done, 'in_progress' => $inProgress, 'pct' => $pct];
}

function overall_progress(array $tasks): array
{
    return module_progress($tasks);
}

function module_status(array $moduleTasks): string
{
    $p = module_progress($moduleTasks);
    if ($p['pct'] === 100) return 'concluido';
    if ($p['done'] > 0 || $p['in_progress'] > 0) return 'andamento';
    return 'pendente';
}

function fmt_date(string $ymd): string
{
    $d = DateTime::createFromFormat('Y-m-d', $ymd);
    return $d ? $d->format('d/m/Y') : $ymd;
}

function fmt_date_short(string $ymd): string
{
    $d = DateTime::createFromFormat('Y-m-d', $ymd);
    return $d ? $d->format('d/m') : $ymd;
}

function fmt_brl(float $v): string
{
    return 'R$ ' . number_format($v, 2, ',', '.');
}

function today_ymd(): string
{
    return date('Y-m-d');
}

function project_phase(array $tasks): string
{
    $today = today_ymd();
    $start = min(array_column($tasks, 'start_date'));
    $end = max(array_column($tasks, 'end_date'));
    if ($today < $start) return 'antes';
    if ($today > $end) return 'depois';
    return 'durante';
}

function days_between(string $a, string $b): int
{
    $d1 = new DateTime($a);
    $d2 = new DateTime($b);
    return (int) $d1->diff($d2)->format('%r%a');
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function truncate_str(string $s, int $len): string
{
    // Trunca respeitando caracteres multibyte (UTF-8), sem depender da extensão mbstring.
    $chars = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY);
    if ($chars === false) {
        return $s; // string inválida como UTF-8: devolve sem alterar
    }
    if (count($chars) <= $len) {
        return $s;
    }
    return implode('', array_slice($chars, 0, $len)) . '…';
}
