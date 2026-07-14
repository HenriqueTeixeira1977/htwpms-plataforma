<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$isAdmin = !empty($_SESSION['is_admin']);
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(PROJETO_NOME) ?> — Acompanhamento</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="topbar">
  <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <div class="kicker">Painel de Acompanhamento · <?= e(AGENCIA_NOME) ?></div>
      <h1><?= e(PROJETO_NOME) ?></h1>
      <div class="client-tag">Cliente: <?= e(CLIENTE_NOME) ?></div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <?php if ($isAdmin): ?>
        <a href="admin.php" class="btn btn-sm btn-accent">Painel Admin</a>
        <a href="logout.php" class="btn btn-sm btn-outline-light2">Sair</a>
      <?php elseif ($currentPage !== 'login.php'): ?>
        <a href="login.php" class="btn btn-sm btn-outline-light2">Área da agência</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="container py-4">
