<?php
require_once 'db.php';

// Validate token
$token = $_GET['token'] ?? '';
$stmt = $conn->prepare(
    "SELECT user_id FROM sessions 
     WHERE token=? AND expires_at > NOW()"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$session = $result->fetch_assoc();

if (!$session) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = $session['user_id'];

// Get last 20 conversions
$stmt = $conn->prepare(
    "SELECT from_currency, to_currency,
            amount, converted_amount,
            rate, created_at
     FROM conversion_history
     WHERE user_id=?
     ORDER BY created_at DESC
     LIMIT 20"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}

echo json_encode($history);
?>