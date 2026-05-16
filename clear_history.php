<?php
require_once 'db.php';

$token = $_POST['token'] ?? '';
$stmt = $conn->prepare(
    "SELECT user_id FROM sessions 
     WHERE token=? AND expires_at > NOW()"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();

if (!$session) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = $session['user_id'];
$stmt = $conn->prepare(
    "DELETE FROM conversion_history WHERE user_id=?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();

echo json_encode(["status" => "cleared"]);
?>