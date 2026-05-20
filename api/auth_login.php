<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

require __DIR__ . '/../config/db.php';

$input    = json_decode(file_get_contents('php://input'), true) ?? [];
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (!$username || !$password) {
    http_response_code(422);
    echo json_encode(['message' => 'Username and password are required.']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'active' LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Invalid username or password.']);
    exit;
}

session_start();
$_SESSION['mc_user'] = [
    'id'       => $user['id'],
    'name'     => $user['full_name'],
    'username' => $user['username'],
    'role'     => $user['role'],
];

$words    = preg_split('/\s+/', trim($user['full_name']));
$initials = strtoupper(implode('', array_map(fn($w) => $w[0] ?? '', array_slice($words, 0, 2))));

echo json_encode([
    'token' => session_id(),
    'user'  => [
        'id'       => $user['id'],
        'name'     => $user['full_name'],
        'username' => $user['username'],
        'role'     => $user['role'],
        'initials' => $initials ?: 'U',
    ],
]);
