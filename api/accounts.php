<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;

session_start();
if (!isset($_SESSION['mc_user'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized']);
    exit;
}

require __DIR__ . '/../config/db.php';

$method  = $_SERVER['REQUEST_METHOD'];
$id      = isset($_GET['id']) ? (int) $_GET['id'] : null;
$isAdmin = ($_SESSION['mc_user']['role'] == 'admin');

if ($method == 'GET') {
    $rows = $pdo->query(
        "SELECT id, full_name, username, email, role, status, created_at FROM users ORDER BY created_at ASC"
    )->fetchAll();
    echo json_encode($rows);
    exit;
}

if (!$isAdmin) {
    http_response_code(403);
    echo json_encode(['message' => 'Admin access required.']);
    exit;
}

if ($method == 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];

    foreach (['full_name', 'username', 'password'] as $f) {
        if (empty(trim($d[$f] ?? ''))) {
            http_response_code(422);
            echo json_encode(['message' => "Field '$f' is required."]);
            exit;
        }
    }

    $chk = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $chk->execute([trim($d['username'])]);
    if ($chk->rowCount() > 0) {
        http_response_code(422);
        echo json_encode(['message' => 'Username already taken.']);
        exit;
    }

    $hash = password($d['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password, role, status) VALUES (?,?,?,?,?,?)");
    $stmt->execute([
        trim($d['full_name']),
        trim($d['username']),
        trim($d['email']  ?? ''),
        $hash,
        in_array($d['role']   ?? '', ['admin', 'staff']) ? $d['role']   : 'staff',
        in_array($d['status'] ?? '', ['active', 'inactive']) ? $d['status'] : 'active',
    ]);

    $new = $pdo->query("SELECT id, full_name, username, email, role, status FROM users WHERE id = " . $pdo->lastInsertId())->fetch();
    http_response_code(201);
    echo json_encode($new);
    exit;
}

if ($method == 'PUT') {
    if (!$id) { http_response_code(400); echo json_encode(['message' => 'Missing account ID']); exit; }

    $d      = json_decode(file_get_contents('php://input'), true) ?? [];
    $sets   = [];
    $params = [];

    if (!empty($d['full_name'])) { $sets[] = 'full_name = ?';     $params[] = trim($d['full_name']); }
    if (!empty($d['username']))  { $sets[] = 'username = ?';      $params[] = trim($d['username']); }
    if (isset($d['email']))      { $sets[] = 'email = ?';         $params[] = trim($d['email']); }
    if (!empty($d['role']))      { $sets[] = 'role = ?';          $params[] = $d['role']; }
    if (!empty($d['status']))    { $sets[] = 'status = ?';        $params[] = $d['status']; }
    if (!empty($d['password']))  { $sets[] = 'password = ?'; $params[] = password($d['password'], PASSWORD_DEFAULT); }

    if (empty($sets)) {
        http_response_code(422);
        echo json_encode(['message' => 'No fields to update.']);
        exit;
    }

    $params[] = $id;
    $pdo->prepare("UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?")->execute($params);
    $updated = $pdo->query("SELECT id, full_name, username, email, role, status FROM users WHERE id = $id")->fetch();
    echo json_encode($updated ?: ['message' => 'Account not found']);
    exit;
}

if ($method == 'DELETE') {
    if (!$id) { http_response_code(400); echo json_encode(['message' => 'Missing account ID']); exit; }

    if ($id == (int) $_SESSION['mc_user']['id']) {
        http_response_code(422);
        echo json_encode(['message' => 'You cannot delete your own account.']);
        exit;
    }

    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    http_response_code(204);
    exit;
}

http_response_code(405);
echo json_encode(['message' => 'Method not allowed']);
