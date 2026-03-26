<?php
function getPDO()
{
    // Configurações do banco de dados
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '3307';
    $db   = getenv('DB_NAME') ?: 'morganizer';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
    
    // String de conexão (DSN - Data Source Name)
    $dsnWithDb  = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $dsnNoDb = "mysql:host={$host};port={$port};charset=utf8mb4";
    
    // Opções de configuração do PDO
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,     // Lança exceções em erros SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retorna resultados como array associativo
        PDO::ATTR_EMULATE_PREPARES => false,             // Usa prepared statements reais do servidor
    ];

    try {
        return new PDO($dsnWithDb, $user, $pass, $opts);
    } catch (PDOException $e) {
        $pdo = new PDO($dsnNoDb, $user, $pass, $opts);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$db}`");
        return $pdo;
    }
}

function ensureUsersTable()
{
    $pdo = getPDO();
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) DEFAULT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        pass VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);
}

function ensureTaskTable()
{
    $pdo = getPDO();

    $pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) DEFAULT NULL,
        description TEXT DEFAULT NULL,
        status ENUM('todo','inProgress','done') NOT NULL DEFAULT 'todo',
        due_date DATE DEFAULT NULL,
        due_time TIME DEFAULT NULL,
        deleted TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX (user_id),
        INDEX (status),
        INDEX (deleted)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Ajuste em esquemas antigos (tasks sem coluna title/due_date)
    $columnExists = (bool)$pdo->query("SHOW COLUMNS FROM tasks LIKE 'title'")->fetch();
    if (!$columnExists) {
        $pdo->exec("ALTER TABLE tasks ADD COLUMN title VARCHAR(255) DEFAULT NULL AFTER user_id");
    }

    $dueExists = (bool)$pdo->query("SHOW COLUMNS FROM tasks LIKE 'due_date'")->fetch();
    if (!$dueExists) {
        $pdo->exec("ALTER TABLE tasks ADD COLUMN due_date DATE DEFAULT NULL AFTER status");
    }

    $dueTimeExists = (bool)$pdo->query("SHOW COLUMNS FROM tasks LIKE 'due_time'")->fetch();
    if (!$dueTimeExists) {
        $pdo->exec("ALTER TABLE tasks ADD COLUMN due_time TIME DEFAULT NULL AFTER due_date");
    }

    $webChanged = (bool)$pdo->query("SHOW COLUMNS FROM tasks LIKE 'description'")->fetch();
    if (!$webChanged) {
        // Caso a tabela original seja a antiga de outros nomes, não fazemos nada
    }
}

function completeTaskTable()
{
    $pdo = getPDO();
    $sql = "CREATE TABLE IF NOT EXISTS completed_tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        task_name VARCHAR(255) NOT NULL,
        task_category VARCHAR(100) DEFAULT NULL,
        data_limite date DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
        INDEX (task_id),
        INDEX (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);
}
