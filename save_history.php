<?php
require_once 'db.php';
// Validate token & get user_id
$token = $_POST['token'] ?? '';
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

$user_id          = $session['user_id'];
$from             = $_POST['from'] ?? '';
$to               = $_POST['to'] ?? '';
$amount           = $_POST['amount'] ?? 0;
$converted_amount = $_POST['converted'] ?? 0;
$rate             = $_POST['rate'] ?? 0;

$stmt = $conn->prepare(
    "INSERT INTO conversion_history 
     (user_id, from_currency, to_currency, 
      amount, converted_amount, rate)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    "issddd",
    $user_id, $from, $to,
    $amount, $converted_amount, $rate
);

if ($stmt->execute()) {
    echo json_encode(["status" => "saved"]);
} else {
    echo json_encode(["error" => "Failed to save"]);
}
?>