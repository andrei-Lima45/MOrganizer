<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

// ================= HELPERS =================
function jsonError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// ================= AUTH =================
if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
    jsonError('Não autorizado', 401);
}

require_once __DIR__ . '/../config/db.php';

$userEmail = $_SESSION['user']['email'] ?? null;
if (!$userEmail) {
    jsonError('Usuário inválido', 401);
}

// ================= GET USER =================
try {
    $pdo = getPDO();

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $userEmail]);

    $user = $stmt->fetch();

    if (!$user) {
        jsonError('Usuário não encontrado', 401);
    }

    $userId = (int)$user['id'];

} catch (Throwable $e) {
    jsonError('Falha ao consultar usuário.', 500);
}

// ================= INPUT =================
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? ($method === 'GET' ? 'list' : 'create');

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput ?: '', true);
if (!is_array($input)) $input = $_POST;

// ================= ROUTES =================
try {

    switch ($action) {

        // ================= LIST =================
        case 'list':
            $query = $pdo->prepare('
                SELECT id, title, description, status, due_date, due_time
                FROM tasks
                WHERE user_id = :uid AND deleted = 0
                ORDER BY created_at DESC
            ');

            $query->execute([':uid' => $userId]);
            $tasks = $query->fetchAll();

            echo json_encode(['tasks' => $tasks], JSON_UNESCAPED_UNICODE);
            exit;


        // ================= CREATE =================
        case 'create':
            $title = trim($input['title'] ?? '');
            $description = trim($input['description'] ?? $title);
            $due_date = $input['due_date'] ?? null;
            $due_time = $input['due_time'] ?? null;

            if ($title === '' && $description === '') {
                jsonError('Título obrigatório');
            }

            $insert = $pdo->prepare('
                INSERT INTO tasks (user_id, title, description, status, due_date, due_time)
                VALUES (:uid, :title, :description, :status, :due, :due_time)
            ');

            $insert->execute([
                ':uid' => $userId,
                ':title' => $title ?: $description,
                ':description' => $description,
                ':status' => 'todo',
                ':due' => $due_date,
                ':due_time' => $due_time
            ]);

            http_response_code(201);
            echo json_encode([
                'id' => $pdo->lastInsertId(),
                'title' => $title ?: $description,
                'description' => $description,
                'status' => 'todo',
                'due_date' => $due_date,
                'due_time' => $due_time
            ], JSON_UNESCAPED_UNICODE);
            exit;


        // ================= UPDATE =================
        case 'update':
            $id = (int)($input['id'] ?? 0);
            $title = trim($input['title'] ?? $input['description'] ?? '');
            $description = trim($input['description'] ?? $title);
            $status = $input['status'] ?? null;
            $due_date = $input['due_date'] ?? null;
            $due_time = $input['due_time'] ?? null;

            if ($id <= 0) jsonError('ID da tarefa inválido');
            if ($title === '') jsonError('Título obrigatório');

            if ($status !== null && !in_array($status, ['todo', 'inProgress', 'done'], true)) {
                jsonError('Status inválido');
            }

            $upd = $pdo->prepare('
                UPDATE tasks
                SET title = :title,
                    description = :description,
                    status = :status,
                    due_date = :due,
                    due_time = :due_time,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND user_id = :uid AND deleted = 0
            ');

            $upd->execute([
                ':title' => $title,
                ':description' => $description,
                ':status' => $status,
                ':due' => $due_date,
                ':due_time' => $due_time,
                ':id' => $id,
                ':uid' => $userId
            ]);

            echo json_encode(['updated' => true], JSON_UNESCAPED_UNICODE);
            exit;


        // ================= GET =================
        case 'get':
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) jsonError('ID da tarefa inválido');

            $query = $pdo->prepare('
                SELECT id, title, description, status, due_date, due_time
                FROM tasks
                WHERE id = :id AND user_id = :uid AND deleted = 0
            ');

            $query->execute([':id' => $id, ':uid' => $userId]);
            $task = $query->fetch();

            if (!$task) jsonError('Tarefa não encontrada');

            echo json_encode($task, JSON_UNESCAPED_UNICODE);
            exit;


        // ================= DELETE =================
        case 'delete':
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) jsonError('ID da tarefa inválido');

            $del = $pdo->prepare('
                UPDATE tasks
                SET deleted = 1,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND user_id = :uid AND deleted = 0
            ');

            $del->execute([':id' => $id, ':uid' => $userId]);

            if ($del->rowCount() === 0) {
                jsonError('Tarefa não encontrada ou já removida', 404);
            }

            echo json_encode(['deleted' => true], JSON_UNESCAPED_UNICODE);
            exit;


        // ================= DEFAULT =================
        default:
            jsonError('Ação inválida', 400);
    }

} catch (Throwable $e) {
    jsonError('Erro interno da API.', 500);
}