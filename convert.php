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
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Get parameters
$from   = $_GET['from']   ?? '';
$to     = $_GET['to']     ?? '';
$amount = $_GET['amount'] ?? 1;

// Validate
if (!$from || !$to || !is_numeric($amount)) {
    echo json_encode(["error" => "Invalid parameters"]);
    exit;
}

// Get rate
$stmt = $conn->prepare(
    "SELECT rate, last_updated FROM exchange_rates 
     WHERE base_currency=? AND target_currency=?"
);
$stmt->bind_param("ss", $from, $to);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    $converted = $amount * $row['rate'];

    // How old is the rate?
    $updated = new DateTime($row['last_updated']);
    $now = new DateTime();
    $diff = $now->diff($updated);
    $age = $diff->h . "h " . $diff->i . "m ago";

    echo json_encode([
        "from"      => $from,
        "to"        => $to,
        "amount"    => $amount,
        "rate"      => $row['rate'],
        "converted" => $converted,
        "updated"   => $age
    ]);
} else {
    echo json_encode(["error" => "Rate not found"]);
}
?>