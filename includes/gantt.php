<?php
/**
 * Espera $tasks (array de tarefas) e $modules (agrupado) já definidos no escopo que inclui este arquivo.
 */
$ganttStart = new DateTime(min(array_column($tasks, 'start_date')));
$ganttEnd = new DateTime(max(array_column($tasks, 'end_date')));
$period = new DatePeriod($ganttStart, new DateInterval('P1D'), (clone $ganttEnd)->modify('+1 day'));
$days = iterator_to_array($period);
$todayStr = today_ymd();
?>
<div class="gantt-scroll">
<table class="gantt">
  <thead>
    <tr>
      <th class="label" style="text-align:left;padding-left:10px;">Tarefa</th>
      <?php foreach ($days as $d): ?>
        <th style="<?= $d->format('N') >= 6 ? 'background:var(--panel-3);' : '' ?>">
          <?= $d->format('N') == 1 ? $d->format('d/m') : '' ?>
        </th>
      <?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($modules as $m): ?>
      <?php foreach ($m['tasks'] as $t):
        $ts = new DateTime($t['start_date']);
        $te = new DateTime($t['end_date']);
      ?>
        <tr>
          <td class="label"><span class="mono" style="color:var(--mod-<?= strtolower($m['module_short']) ?>)"><?= e($t['task_num']) ?></span> &nbsp;<?= e(truncate_str($t['task_name'], 34)) ?></td>
          <?php foreach ($days as $d):
            $inRange = $d >= $ts && $d <= $te;
            $isWeekend = $d->format('N') >= 6;
            $isToday = $d->format('Y-m-d') === $todayStr;
            $classes = ['cell'];
            if ($inRange) $classes[] = $t['status'] === 'concluido' ? 'bar done' : 'bar';
            elseif ($isWeekend) $classes[] = 'weekend';
            if ($isToday) $classes[] = 'today';
          ?>
            <td class="<?= implode(' ', $classes) ?>" style="<?= $inRange ? '--mc: var(--mod-' . strtolower($m['module_short']) . ');' : '' ?>" title="<?= e($t['task_name']) ?>"></td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<div class="d-flex flex-wrap gap-3 mt-3">
  <div class="legend-item"><span class="legend-dot" style="background:var(--status-concluido)"></span>Concluída</div>
  <div class="legend-item"><span class="legend-dot" style="background:var(--accent)"></span>Prevista</div>
  <div class="legend-item"><span class="legend-dot" style="background:var(--panel-3)"></span>Fim de semana</div>
  <div class="legend-item"><span class="legend-dot" style="box-shadow: inset 0 0 0 2px var(--accent); background:transparent;"></span>Hoje</div>
</div>
