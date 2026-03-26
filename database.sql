-- ============================================
-- BANCO DE DADOS: MOrganizer
-- ============================================

-- Criar banco (opcional)
CREATE DATABASE IF NOT EXISTS morganizer;
USE morganizer;

-- ============================================
-- TABELA DE USUÁRIOS
-- ============================================

CREATE TABLE IF NOT EXISTS users (
id INT AUTO_INCREMENT PRIMARY KEY,
nome VARCHAR(100) NOT NULL,
email VARCHAR(100) NOT NULL UNIQUE,
pass VARCHAR(255) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABELA DE TAREFAS
-- ============================================

CREATE TABLE IF NOT EXISTS tasks (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
title VARCHAR(255) DEFAULT NULL,
description TEXT DEFAULT NULL,
status ENUM('todo','inProgress','done') DEFAULT 'todo',
due_date DATE DEFAULT NULL,
due_time TIME DEFAULT NULL,
deleted TINYINT(1) DEFAULT 0,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP NULL DEFAULT NULL,

```
-- Relacionamento
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
```

);

-- ============================================
-- DADOS DE TESTE (OPCIONAL)
-- ============================================

-- Usuário de exemplo
INSERT INTO users (nome, email, pass)
VALUES ('Teste', '[teste@teste.com](mailto:teste@teste.com)', '$2y$10$abcdefghijklmnopqrstuv');

-- Tarefa de exemplo
INSERT INTO tasks (user_id, title, description, status)
VALUES (1, 'Primeira tarefa', 'Exemplo de tarefa', 'todo');
