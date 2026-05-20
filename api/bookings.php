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

// Allowed status transitions
$STATUS_FLOW = [
    'pending'   => ['confirmed', 'cancelled'],
    'confirmed' => ['completed', 'cancelled'],
    'completed' => [],
    'cancelled' => ['pending'],
];

// ── GET ───────────────────────────────────────────────────────────────────
if ($method == 'GET') {
    $rows = $pdo->query(
        "SELECT id, client_id, client_name, email, phone, alt_phone, event_type, event_date,
                event_time, guest_count, package, venue, duration, decoration, theme,
                special_requests, referral, status, created_at
         FROM bookings ORDER BY event_date ASC"
    )->fetchAll();
    echo json_encode($rows);
    exit;
}

// ── POST ──────────────────────────────────────────────────────────────────
if ($method == 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];

    foreach (['client_name', 'event_type', 'event_date', 'package', 'venue'] as $f) {
        if (empty(trim($d[$f] ?? ''))) {
            http_response_code(422);
            echo json_encode(['message' => "Field '$f' is required."]);
            exit;
        }
    }

    // New bookings always start as pending
    $d['status'] = 'pending';

    do {
        $client_id = 'MC-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $dup = $pdo->prepare("SELECT id FROM bookings WHERE client_id = ?");
        $dup->execute([$client_id]);
    } while ($dup->rowCount() > 0);

    $stmt = $pdo->prepare("INSERT INTO bookings
        (client_id, client_name, email, phone, alt_phone, event_type, event_date, event_time,
         guest_count, package, venue, duration, decoration, theme, special_requests, referral, status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'pending')");

    $stmt->execute([
        $client_id,
        trim($d['client_name']),
        trim($d['email']           ?? ''),
        trim($d['phone']           ?? ''),
        trim($d['alt_phone']       ?? ''),
        $d['event_type'],
        $d['event_date'],
        $d['event_time']           ?? null,
        (int)($d['guest_count']    ?? 0),
        $d['package'],
        trim($d['venue']),
        $d['duration']             ?? null,
        $d['decoration']           ?? 'no',
        $d['theme']                ?? null,
        $d['special_requests']     ?? null,
        $d['referral']             ?? null,
    ]);

    $new = $pdo->query("SELECT * FROM bookings WHERE id = " . $pdo->lastInsertId())->fetch();
    http_response_code(201);
    echo json_encode($new);
    exit;
}

// ── PUT ───────────────────────────────────────────────────────────────────
if ($method == 'PUT') {
    if (!$id) { http_response_code(400); echo json_encode(['message' => 'Missing booking ID']); exit; }

    $d = json_decode(file_get_contents('php://input'), true) ?? [];

    // Fetch current booking to enforce transition rules
    $cur = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $cur->execute([$id]);
    $current = $cur->fetch();

    if (!$current) {
        http_response_code(404);
        echo json_encode(['message' => 'Booking not found']);
        exit;
    }

    $currentStatus = $current['status'];
    $newStatus     = $d['status'] ?? $currentStatus;

    // Block edits entirely on completed bookings (except status change by admin, handled below)
    if ($currentStatus == 'completed') {
        http_response_code(422);
        echo json_encode(['message' => 'Completed bookings cannot be edited.']);
        exit;
    }

    // Validate status transition
    if ($newStatus != $currentStatus) {
        $allowed = $STATUS_FLOW[$currentStatus] ?? [];
        if (!in_array($newStatus, $allowed)) {
            http_response_code(422);
            echo json_encode([
                'message' => "Status cannot go from '$currentStatus' to '$newStatus'. " .
                             (count($allowed) ? "Allowed: " . implode(', ', $allowed) . "." : "No further changes allowed.")
            ]);
            exit;
        }
    }

    $stmt = $pdo->prepare("UPDATE bookings SET
        client_name      = ?,
        email            = ?,
        phone            = ?,
        event_type       = ?,
        event_date       = ?,
        event_time       = ?,
        guest_count      = ?,
        package          = ?,
        venue            = ?,
        status           = ?,
        duration         = ?,
        decoration       = ?,
        theme            = ?,
        special_requests = ?
        WHERE id = ?");

    $stmt->execute([
        trim($d['client_name']     ?? $current['client_name']),
        trim($d['email']           ?? $current['email'] ?? ''),
        trim($d['phone']           ?? $current['phone'] ?? ''),
        $d['event_type']           ?? $current['event_type'],
        $d['event_date']           ?? $current['event_date'],
        $d['event_time']           ?? $current['event_time'],
        (int)($d['guest_count']    ?? $current['guest_count']),
        $d['package']              ?? $current['package'],
        trim($d['venue']           ?? $current['venue']),
        $newStatus,
        $d['duration']             ?? $current['duration'],
        $d['decoration']           ?? $current['decoration'],
        $d['theme']                ?? $current['theme'],
        $d['special_requests']     ?? $current['special_requests'],
        $id,
    ]);

    $updated = $pdo->query("SELECT * FROM bookings WHERE id = $id")->fetch();
    echo json_encode($updated);
    exit;
}

// ── DELETE ────────────────────────────────────────────────────────────────
if ($method == 'DELETE') {
    if (!$id) { http_response_code(400); echo json_encode(['message' => 'Missing booking ID']); exit; }
    $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$id]);
    http_response_code(204);
    exit;
}

http_response_code(405);
echo json_encode(['message' => 'Method not allowed']);
