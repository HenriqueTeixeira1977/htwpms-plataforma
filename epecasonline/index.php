<?php
require_once __DIR__ . '/includes/functions.php';

$tasks = fetch_all_tasks();
$modules = group_by_module($tasks);
$overall = overall_progress($tasks);

$projectStart = min(array_column($tasks, 'start_date'));
$projectEnd = max(array_column($tasks, 'end_date'));
$totalInvestment = 0;
foreach ($modules as $m) { $totalInvestment += $m['investment']; }

$today = today_ymd();
$phase = project_phase($tasks);
$daysToEnd = days_between($today, $projectEnd);
$daysToStart = days_between($today, $projectStart);

// Gauge geometry
$r = 54; $circ = 2 * M_PI * $r;
$offset = $circ * (1 - $overall['pct'] / 100);

include __DIR__ . '/includes/header.php';
?>

<div class="row g-3 mb-4">
  <div class="col-lg-5">
    <div class="panel h-100 p-4">
      <div class="section-eyebrow">Progresso Geral</div>
      <div class="gauge-wrap">
        <svg class="gauge-svg" width="140" height="140" viewBox="0 0 140 140">
          <circle cx="70" cy="70" r="<?= $r ?>" fill="none" stroke="#242e3a" stroke-width="12"/>
          <circle cx="70" cy="70" r="<?= $r ?>" fill="none" stroke="var(--accent)" stroke-width="12"
                  stroke-linecap="round" stroke-dasharray="<?= $circ ?>" stroke-dashoffset="<?= $offset ?>"
                  transform="rotate(-90 70 70)"/>
          <text x="70" y="66" text-anchor="middle" class="gauge-value"><?= $overall['pct'] ?>%</text>
          <text x="70" y="86" text-anchor="middle" class="gauge-label">CONCLUÍDO</text>
        </svg>
        <div class="gauge-stats">
          <div class="mb-1"><span class="dot" style="background:var(--status-concluido)"></span><b><?= $overall['done'] ?></b> de <?= $overall['total'] ?> tarefas concluídas</div>
          <div class="mb-1"><span class="dot" style="background:var(--status-andamento)"></span><b><?= $overall['in_progress'] ?></b> em andamento</div>
          <div><span class="dot" style="background:var(--status-pendente)"></span><b><?= $overall['total'] - $overall['done'] - $overall['in_progress'] ?></b> pendentes</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="panel h-100 p-4">
      <div class="section-eyebrow">Linha do Tempo</div>
      <div class="row g-3 mono">
        <div class="col-6 col-md-3">
          <div class="text-muted" style="font-size:11px;letter-spacing:.06em;">INÍCIO</div>
          <div style="font-size:1.05rem;"><?= fmt_date($projectStart) ?></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="text-muted" style="font-size:11px;letter-spacing:.06em;">TÉRMINO PREVISTO</div>
          <div style="font-size:1.05rem;"><?= fmt_date($projectEnd) ?></div>
        </div>
        <div class="col-6 col-md-3">
          <div class="text-muted" style="font-size:11px;letter-spacing:.06em;">FASE ATUAL</div>
          <div style="font-size:1.05rem;">
            <?php if ($phase === 'antes'): ?>
              Inicia em <?= $daysToStart ?> dia(s)
            <?php elseif ($phase === 'depois'): ?>
              Prazo encerrado
            <?php else: ?>
              Em execução (<?= $daysToEnd ?> dia(s) restantes)
            <?php endif; ?>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="text-muted" style="font-size:11px;letter-spacing:.06em;">INVESTIMENTO TOTAL</div>
          <div style="font-size:1.05rem;"><?= fmt_brl($totalInvestment) ?></div>
        </div>
      </div>
      <hr style="border-color:var(--border);margin:18px 0 14px;">
      <div class="d-flex flex-wrap gap-3">
        <?php foreach ($modules as $m): $p = module_progress($m['tasks']); ?>
          <div class="legend-item">
            <span class="legend-dot" style="background:var(--mod-<?= strtolower($m['module_short']) ?>)"></span>
            <?= e($m['module_short']) ?> · <?= $p['pct'] ?>%
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div class="section-eyebrow mb-2">Módulos do Projeto</div>

<?php foreach ($modules as $m):
  $p = module_progress($m['tasks']);
  $colorVar = 'var(--mod-' . strtolower($m['module_short']) . ')';
  $collapseId = 'mod-' . strtolower($m['module_short']);
?>
  <div class="module-card" style="--mc: <?= $colorVar ?>;">
    <div class="module-head" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false" role="button">
      <span class="module-chip"><?= e($m['module_short']) ?></span>
      <span class="module-title"><?= e(preg_replace('/^Módulo \d+\s*–\s*/u', '', $m['module'])) ?></span>
      <span class="module-dates"><?= fmt_date_short($m['start_date']) ?> – <?= fmt_date_short($m['end_date']) ?></span>
      <span class="module-progress-mini"><div style="width:<?= $p['pct'] ?>%"></div></span>
      <span class="module-pct"><?= $p['pct'] ?>%</span>
      <svg class="chevron" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </div>
    <div class="collapse" id="<?= $collapseId ?>">
      <div class="table-responsive">
        <table class="task-table">
          <thead>
            <tr>
              <th style="width:60px;">Nº</th>
              <th>Tarefa</th>
              <th style="width:170px;">Responsável</th>
              <th style="width:140px;">Período</th>
              <th style="width:140px;">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($m['tasks'] as $t): ?>
              <tr>
                <td class="task-num"><?= e($t['task_num']) ?></td>
                <td><?= e($t['task_name']) ?></td>
                <td class="text-muted" style="font-size:0.82rem;"><?= e($t['responsible']) ?></td>
                <td class="task-dates"><?= fmt_date_short($t['start_date']) ?> – <?= fmt_date_short($t['end_date']) ?></td>
                <td>
                  <span class="status-badge <?= STATUS_BADGE_CLASS[$t['status']] ?>"><?= STATUS_LABELS[$t['status']] ?></span>
                  <?php if (!empty($t['note'])): ?>
                    <div class="text-muted" style="font-size:0.74rem;margin-top:3px;"><?= e($t['note']) ?></div>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<div class="panel p-4 mt-4">
  <div class="section-eyebrow mb-3">Cronograma Visual (Gantt)</div>
  <?php include __DIR__ . '/includes/gantt.php'; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
