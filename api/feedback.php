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

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($method == 'GET') {
    $rows = $pdo->query(
        "SELECT id, client_name, email, event_type, has_booked, star_rating,
                comments, liked_tags, date_submitted, status, created_at
         FROM feedback ORDER BY created_at DESC"
    )->fetchAll();
    echo json_encode($rows);
    exit;
}

if ($method == 'PATCH') {
    if (!$id) { http_response_code(400); echo json_encode(['message' => 'Missing feedback ID']); exit; }

    $d      = json_decode(file_get_contents('php://input'), true) ?? [];
    $status = $d['status'] ?? 'read';

    if (!in_array($status, ['new', 'read'])) {
        http_response_code(422);
        echo json_encode(['message' => 'Invalid status value.']);
        exit;
    }

    $pdo->prepare("UPDATE feedback SET status = ? WHERE id = ?")->execute([$status, $id]);
    $updated = $pdo->query("SELECT * FROM feedback WHERE id = $id")->fetch();
    echo json_encode($updated ?: ['message' => 'Feedback not found']);
    exit;
}

if ($method == 'DELETE') {
    if (!$id) { http_response_code(400); echo json_encode(['message' => 'Missing feedback ID']); exit; }
    $pdo->prepare("DELETE FROM feedback WHERE id = ?")->execute([$id]);
    http_response_code(204);
    exit;
}

http_response_code(405);
echo json_encode(['message' => 'Method not allowed']);
