<?php

session_start();

require_once __DIR__ . '/config/db.php';


$error = '';
$nome_val = '';
$email_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['pass'] ?? '';

    $nome_val = htmlspecialchars($nome, ENT_QUOTES);
    $email_val = htmlspecialchars($email, ENT_QUOTES);

    if ($email === '' || $pass === '') {
        $error = 'Preencha email e senha.';
    } else {
        try {
            $pdo = getPDO();
            
            $stmt = $pdo->prepare('SELECT id, nome, email, pass FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass, $user['pass'])) {
                $_SESSION['logged'] = true;  
                $_SESSION['user'] = ['nome' => $user['nome'], 'email' => $user['email']]; 
   
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Email ou senha inválidos!';
            }
        } catch (Exception $e) {
            $error = 'Erro ao acessar o banco de dados.';
        }
    }
}
?><!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="css/styles.css" />
<title>MOrganizer | Login</title>
</head>
<body>
<header>
  <div class="brand">
    <span class="brand-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" role="img" aria-label="MOrganizer icon">
        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8">
          <rect x="3" y="4" width="18" height="17" rx="3.2" />
          <path d="M8 2.8v2.6M12 2.8v2.6M16 2.8v2.6" />
          <rect x="6.1" y="8" width="3.4" height="3.4" rx="0.8" />
          <path d="M6.8 9.7l1.2 1.2 1.9-2" />
          <line x1="11.2" y1="9.8" x2="17.1" y2="9.8" />
          <line x1="11.2" y1="11.3" x2="15.9" y2="11.3" />
          <rect x="6.1" y="13.2" width="3.4" height="3.4" rx="0.8" />
          <path d="M6.8 14.8l1.2 1.2 1.9-2" />
          <line x1="11.2" y1="15" x2="17.1" y2="15" />
          <line x1="11.2" y1="16.5" x2="15.9" y2="16.5" />
        </g>
      </svg>
    </span>
    <span class="brand-name" aria-label="MOrganizer">MOrganizer</span>
  </div>
  <nav class="menu" aria-label="Acoes de tema">
    <button class="theme-toggle" type="button" aria-pressed="false">Modo escuro</button>
  </nav>
</header>
<main>
  <section id="login-card" class="card">
    <h2>Entrar</h2>
    <p class="helper-text">Use seu email e senha cadastrados para acessar suas tarefas.</p>
    <form method="POST" aria-describedby="login-help">
      <p id="login-help" class="sr-only">Formulario de autenticacao</p>
      <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="<?php echo $email_val; ?>" autocomplete="email" required>
      </div>
      <div>
        <label for="pass">Senha</label>
        <input id="pass" type="password" name="pass" autocomplete="current-password" required>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn">Entrar</button>
      </div>
      <?php if (!empty($error)) echo "<p class='msg err' role='alert'>$error</p>"; ?>
    </form>
    <p class="mt-sm">Não tem conta? <a href="cadastro.php" class="link-accent">Cadastre-se</a></p>
  </section>
</main>
<script src="js/ui.js"></script>
</body>
</html>