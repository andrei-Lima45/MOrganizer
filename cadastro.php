<?php
/**
 * ARQUIVO: cadastro.php
 * DESCRIÇÃO: Página de registro de novos usuários
 * FLUXO:
 *   1. Usuário preenche nome, email e senha
 *   2. Sistema valida dados
 *   3. Senha é criptografada com password_hash()
 *   4. Usuário é inserido no banco
 *   5. Se email já existe: mostra erro
 */

// Inicia sessão
session_start();

// Importa conexão com banco de dados
require_once __DIR__ . '/db.php';

// Tenta criar tabela de usuários (se não existir)
try {
    ensureUsersTable();
} catch (Exception $e) {
    // Ignora erros se tabela já existe
}

// Variáveis para mensagens
$error = '';
$success = '';

/**
 * Processa formulário de registro se submetido via POST
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extrai dados do formulário
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['pass'] ?? '';

    // Valida campos obrigatórios
    if ($email === '' || $senha === '') {
        $error = 'Preencha email e senha.';
    } else {
        // Conecta ao banco
        $pdo = getPDO();
        
        // Prepara statement para inserção
        $stmt = $pdo->prepare('INSERT INTO users (nome, email, pass) VALUES (:nome, :email, :pass)');
        
        // Criptografa a senha (impossível recuperar - one-way hash)
        // PASSWORD_DEFAULT usa algoritmo mais seguro disponível
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        
        try {
            // Executa inserção com dados parametrizados (protege contra SQL injection)
            $stmt->execute([':nome' => $nome, ':email' => $email, ':pass' => $hash]);
            
            // Usuário criado com sucesso
            $success = 'Usuário cadastrado com sucesso! <a href="login.php" style="color:var(--accent);text-decoration:none">Faça login aqui</a>';
        } catch (PDOException $e) {
            /**
             * Trata erros de inserção
             * Código 23000 = Violação de constraint (ex: email duplicado)
             */
            if ($e->getCode() == 23000) {
                $error = 'Email já cadastrado.';
            } else {
                $error = 'Erro ao cadastrar usuário.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
        <title>MOrganizer | Cadastro</title>
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
        <a href="login.php">Login</a>
      </nav>
    </header>

    <main>
        <section id="signup-card" class="card">
            <h2>Cadastro de Usuário</h2>
            <p class="helper-text">Preencha os dados abaixo. Apenas email e senha sao obrigatorios.</p>
            <form method="post" aria-describedby="signup-help">
                <p id="signup-help" class="sr-only">Formulario de cadastro de conta</p>
                <div>
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" autocomplete="name" required>
                </div>
                <div>
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" autocomplete="email" required>
                </div>
                <div>
                    <label for="pass">Senha *</label>
                    <input type="password" id="pass" name="pass" autocomplete="new-password" required>
                </div>
                <div>
                    <label for="data">Data de Nascimento</label>
                    <input type="date" id="data" name="data">
                </div>
                <div>
                    <label for="sexo">Sexo</label>
                    <select id="sexo" name="sexo">
                        <option value="">Selecione...</option>
                        <option value="M">Masculino</option>
                        <option value="F">Feminino</option>
                        <option value="O">Outro</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn">Cadastrar</button>
                </div>
                <small>* Campos obrigatórios</small>
                <?php if (!empty($error)) echo "<p class='msg err' role='alert'>$error</p>"; ?>
                <?php if (!empty($success)) echo "<p class='msg ok' role='status'>$success</p>"; ?>
            </form>
            <p style="margin-top:12px">Já tem conta? <a href="login.php" style="color:var(--accent);text-decoration:none">Faça login</a></p>
        </section>
    </main>
<script src="ui.js"></script>
</body>
</html>
