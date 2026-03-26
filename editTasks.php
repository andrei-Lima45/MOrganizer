<?php
session_start();
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    header('Location: login.php');
    exit;
}
$userName = trim($_SESSION['user']['nome'] ?? $_SESSION['user']['email'] ?? 'Usuário');
if ($userName === '') {
    $userName = 'Usuário';
}
$userName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>MOrganizer | Editar Tarefa</title>
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
            <a href="tasks.php">Listas</a>
            <a href="logout.php" class="logout">Sair</a>
        </nav>
        </header>
<main>
    <section class="card" aria-labelledby="edit-title">
        <h2 id="edit-title"><?php echo isset($_GET['id']) ? 'Editar' : 'Criar Nova'; ?> Tarefa</h2>
        <p class="helper-text">Preencha o título (obrigatório). Descrição, data e hora são opcionais.</p>
        <form id="editTaskForm" aria-describedby="edit-help">
            <p id="edit-help" class="sr-only">Formulario para criar ou editar tarefa</p>
            <div>
                <label for="taskTitle">Título</label>
                <input type="text" id="taskTitle" name="title" required placeholder="Ex.: Marcar consulta">
            </div> 
            <div>
                <label for="taskDescription">Descrição (opcional)</label>
                <input type="text" id="taskDescription" name="description" placeholder="Detalhes para lembrar depois">
            </div>
            <div>
                <label for="taskDueDate">Data de Vencimento</label>
                <input type="date" id="taskDueDate" name="due_date">
            </div>
            <div>
                <label for="taskDueTime">Hora de Vencimento</label>
                <input type="time" id="taskDueTime" name="due_time">
            </div>
            <div>
                <label for="taskStatus">Status</label>
                <select id="taskStatus" name="status">
                    <option value="todo">Tarefas</option>
                    <option value="inProgress">Em Progresso</option>
                    <option value="done">Concluídas</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Salvar</button>
                <button type="button" class="btn ghost" onclick="window.history.back()">Cancelar</button>
            </div>
        </form>
        <p class="msg" id="editInfoMsg" role="status" aria-live="polite"></p>  
    </section>
</main>
<script src="js/ui.js"></script>
<script src="js/script.js"></script>
</body>
</html>