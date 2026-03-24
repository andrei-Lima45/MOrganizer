<?php
session_start();
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    header('Location: login.php');
    exit;
}
$userName = isset($_SESSION['user']['nome']) && trim($_SESSION['user']['nome']) !== '' ? trim($_SESSION['user']['nome']) : (isset($_SESSION['user']['email']) ? trim($_SESSION['user']['email']) : 'Usuário');
if ($userName === '') {
    $userName = 'Usuário';
}
$userName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>MOrganizer | Tarefas</title>
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
        <a href="dashboard.php">Dashboard</a>
        <a href="editTasks.php">Nova Tarefa</a>
        <a href="logout.php" class="logout">Sair</a>
    </nav>
</header>
<main>
    <section class="card" aria-labelledby="create-task-title">
        <h2 id="create-task-title">Olá, <?php echo $userName; ?>. Vamos organizar seu dia?</h2>
        <p class="helper-text">Passo 1: escreva o nome da tarefa. Passo 2: opcionalmente adicione descrição e vencimento. Passo 3: clique em "Adicionar tarefa".</p>
        <div class="row">
            <label class="sr-only" for="taskInput">Titulo da tarefa</label>
            <input id="taskInput" type="text" class="input-wide" placeholder="Ex.: Pagar conta de luz" autocomplete="off" aria-describedby="task-help" />
            <label class="sr-only" for="descInput">Descricao da tarefa</label>
            <input id="descInput" type="text" class="input-wide" placeholder="Descrição (opcional)" autocomplete="off" />
        </div>
        <p id="task-help" class="helper-text">Dica: use um nome curto e claro, como "Comprar remedio".</p>
        <div class="field-row mt-sm">
            <div>
                <label for="dueDateInput">Data de Vencimento</label>
                <input id="dueDateInput" type="date" />
            </div>
            <div>
                <label for="dueTimeInput">Hora de Vencimento</label>
                <input id="dueTimeInput" type="time" />
            </div>
        </div>
        <div class="form-actions">
            <button id="addBtn" class="btn">Adicionar tarefa</button>
            <button id="clearBtn" class="btn ghost" type="button">Apagar todas</button>
        </div>
        <p class="msg" id="infoMsg" role="status" aria-live="polite"></p>
    </section>

    <button id="showGuideBtn" class="guide-toggle" type="button" hidden aria-controls="quickGuideCard" aria-expanded="false">Mostrar guia rápido</button>

    <section id="quickGuideCard" class="card help-card" aria-labelledby="quick-guide-title">
        <div class="help-card-header">
            <h2 id="quick-guide-title">Guia Rápido</h2>
            <button id="hideGuideBtn" class="guide-close" type="button" aria-label="Ocultar guia rapido">Ocultar</button>
        </div>
        <p class="helper-text">Resumo das ações principais para usar o MOrganizer sem dificuldade.</p>
        <ol class="helper-list">
            <li>Adicione uma tarefa no campo acima e clique em "Adicionar tarefa".</li>
            <li>Arraste o indicador ⠃ no topo do card para mover entre colunas.</li>
            <li>Ou use os botões "&lt;" e "&gt;" para mover passo a passo.</li>
            <li>Use o botão ✏️ para editar e 🗑️ para excluir a tarefa.</li>
            <li>Use o filtro de status para ver apenas o que importa agora.</li>
        </ol>
    </section>

    <section class="card tasks-section" aria-labelledby="list-title">
        <h2 id="list-title">Minhas Tarefas</h2>
        <div class="search-box">
            <label class="sr-only" for="searchInput">Pesquisar tarefas</label>
            <input id="searchInput" type="text" class="input-wide" placeholder="Pesquisar por nome ou descrição" autocomplete="off" />
        </div>

        <div class="filter-row">
            <label for="filterStatus" class="filter-label">Filtrar por status:</label>
            <select id="filterStatus" class="select-auto">
                <option value="">Todos</option>
                <option value="todo">Tarefas</option>
                <option value="inProgress">Em Progresso</option>
                <option value="done">Concluídas</option>
            </select>
        </div>

    <p class="helper-text">Arraste o indicador ⠃ de um card para outra coluna, ou use os botões &lt; e &gt;.</p>

    <div class="task-grid" role="region" aria-label="Colunas de tarefas">
        <div class="task-column card">
            <h3>Tarefas</h3>
            <div id="todoList" class="task-list" role="list" aria-label="Tarefas a fazer"></div>
        </div>
        <div class="task-column card">
            <h3>Em Progresso</h3>
            <div id="inProgressList" class="task-list" role="list" aria-label="Tarefas em progresso"></div>
        </div>
        <div class="task-column card">
            <h3>Concluídas</h3>
            <div id="doneList" class="task-list" role="list" aria-label="Tarefas concluidas"></div>
        </div>
    </div>
    </section>
</main>
<script src="ui.js"></script>
<script src="script.js"></script>
</body>
</html>