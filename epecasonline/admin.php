<?php
require_once __DIR__ . '/includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

$pdo = get_db();
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statuses = $_POST['status'] ?? [];
    $notes = $_POST['note'] ?? [];
    $stmt = $pdo->prepare('UPDATE tasks SET status = :status, note = :note, updated_at = :updated_at WHERE id = :id');
    $now = date('Y-m-d H:i:s');
    $validStatuses = array_keys(STATUS_LABELS);

    $pdo->beginTransaction();
    foreach ($statuses as $id => $status) {
        if (!in_array($status, $validStatuses, true)) continue;
        $stmt->execute([
            ':status' => $status,
            ':note' => trim($notes[$id] ?? ''),
            ':updated_at' => $now,
            ':id' => (int) $id,
        ]);
    }
    $pdo->commit();
    $flash = 'Alterações salvas com sucesso.';
}

$tasks = fetch_all_tasks();
$modules = group_by_module($tasks);
$overall = overall_progress($tasks);

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <div class="section-eyebrow">Painel administrativo</div>
    <h4 style="text-transform:none;font-family:'Oswald',sans-serif;margin:0;">Atualizar status das tarefas</h4>
  </div>
  <div class="mono text-muted" style="font-size:0.85rem;">
    Progresso geral: <b style="color:var(--text)"><?= $overall['pct'] ?>%</b>
    (<?= $overall['done'] ?>/<?= $overall['total'] ?>)
  </div>
</div>

<?php if ($flash): ?>
  <div class="alert py-2 mono" style="background:rgba(63,178,127,0.15); color:#3fb27f; border:1px solid rgba(63,178,127,0.3); font-size:0.85rem;"><?= e($flash) ?></div>
<?php endif; ?>

<form method="post">
  <?php foreach ($modules as $m): $colorVar = 'var(--mod-' . strtolower($m['module_short']) . ')'; ?>
    <div class="panel mb-3" style="border-left:4px solid <?= $colorVar ?>;">
      <div class="panel-header">
        <span class="module-title" style="text-transform:none;">
          <span class="module-chip" style="background:<?= $colorVar ?>;"><?= e($m['module_short']) ?></span>
          &nbsp; <?= e(preg_replace('/^Módulo \d+\s*–\s*/u', '', $m['module'])) ?>
        </span>
        <span class="mono text-muted" style="font-size:0.8rem;"><?= fmt_date($m['start_date']) ?> – <?= fmt_date($m['end_date']) ?></span>
      </div>
      <div class="table-responsive">
        <table class="task-table">
          <thead>
            <tr>
              <th style="width:60px;">Nº</th>
              <th>Tarefa</th>
              <th style="width:160px;">Status</th>
              <th style="width:260px;">Observação (opcional)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($m['tasks'] as $t): ?>
              <tr>
                <td class="task-num"><?= e($t['task_num']) ?></td>
                <td>
                  <?= e($t['task_name']) ?>
                  <div class="text-muted" style="font-size:0.76rem;"><?= fmt_date_short($t['start_date']) ?> – <?= fmt_date_short($t['end_date']) ?> · <?= e($t['responsible']) ?></div>
                </td>
                <td>
                  <select name="status[<?= $t['id'] ?>]" class="form-select form-select-dark form-select-sm">
                    <?php foreach (STATUS_LABELS as $key => $label): ?>
                      <option value="<?= $key ?>" <?= $t['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td>
                  <input type="text" name="note[<?= $t['id'] ?>]" value="<?= e($t['note'] ?? '') ?>" class="form-control form-control-dark form-control-sm" placeholder="Ex: aguardando aprovação do cliente">
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="d-flex justify-content-end gap-2 mb-5">
    <a href="index.php" class="btn btn-outline-light2">Ver painel do cliente</a>
    <button type="submit" class="btn btn-accent">Salvar alterações</button>
  </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
