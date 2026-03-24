<?php
/**
 * ARQUIVO: dashboard.php
 * DESCRIÇÃO: Página inicial/protegida após login
 * ACESSO: Apenas para usuários autenticados
 * FUNCIONALIDADE: Menu de navegação para funcionalidades do sistema
 */

// Inicia sessão
session_start();

/**
 * Valida sessão
 * Se usuário NÃO está logado: redireciona para login
 * Se está logado: $_SESSION['logged'] === true e $_SESSION['user'] contém dados
 */
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    header('Location: login.php');
    exit;
}

// Extrai nome do usuário da sessão (com fallback para 'Usuário' se não encontrar)
$userName = $_SESSION['user']['nome'] ?? $_SESSION['nome'] ?? 'Usuário';

// Sanitiza nome para exibir no HTML (previne XSS)
$userName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');

?><!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="styles.css" />
<title>MOrganizer | Dashboard</title>
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
  <nav class="menu" aria-label="Navegacao principal">
    <button class="theme-toggle" type="button" aria-pressed="false">Modo escuro</button>
    <a href="tasks.php">Listas</a>
    <a href="logout.php" class="logout">Sair</a>
  </nav>
</header>

<main>
  <section class="card" aria-labelledby="welcome-title">
    <h2 id="welcome-title">Bem-vindo, <?php echo $userName; ?>!</h2>
    <p>Seu espaço no MOrganizer para acompanhar tarefas e prioridades.</p>
    <div class="row">
      <a href="tasks.php" class="btn">Abrir Minhas Tarefas</a>
      <a href="editTasks.php" class="btn secondary">Criar Nova Tarefa</a>
      <a href="logout.php" class="btn btn-logout">Sair</a>
    </div>
  </section>

  <section class="card help-card">
    <h2>Como usar em 3 passos</h2>
    <ol class="helper-list">
      <li>Clique em "Criar Nova Tarefa".</li>
      <li>Preencha apenas o título e salve. O restante é opcional.</li>
      <li>Na tela de listas, mova para "Em Progresso" e depois "Concluídas".</li>
    </ol>
    <p class="helper-text">Dica: se esquecer algo, você pode editar a tarefa depois no ícone de lápis.</p>
  </section>
</main>
<script src="ui.js"></script>
</body>
</html>