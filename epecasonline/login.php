<?php
require_once __DIR__ . '/includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    if ($user === ADMIN_USER && password_verify($pass, ADMIN_PASS_HASH)) {
        $_SESSION['is_admin'] = true;
        header('Location: admin.php');
        exit;
    }
    $error = 'Usuário ou senha inválidos.';
}

include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-5 col-lg-4">
    <div class="panel p-4 mt-4">
      <div class="section-eyebrow">Acesso restrito</div>
      <h4 class="mb-3" style="text-transform:none;font-family:'Oswald',sans-serif;">Área da agência</h4>
      <?php if ($error): ?>
        <div class="alert alert-danger py-2 mono" style="font-size:0.85rem;"><?= e($error) ?></div>
      <?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label text-muted" style="font-size:0.82rem;">Usuário</label>
          <input type="text" name="username" class="form-control form-control-dark" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label text-muted" style="font-size:0.82rem;">Senha</label>
          <input type="password" name="password" class="form-control form-control-dark" required>
        </div>
        <button type="submit" class="btn btn-accent w-100">Entrar</button>
      </form>
      <div class="mt-3 text-center">
        <a href="index.php" class="text-muted" style="font-size:0.82rem;">&larr; Voltar ao acompanhamento</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
