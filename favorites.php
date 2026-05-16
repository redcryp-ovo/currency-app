<?php
require_once 'db.php';

// Validate token
$token = $_POST['token'] ?? $_GET['token'] ?? '';
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
$action  = $_REQUEST['action'] ?? 'get';

// GET favorites
if ($action === 'get') {
    $stmt = $conn->prepare(
        "SELECT id, from_currency, to_currency 
         FROM favorites WHERE user_id=?"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $favs = [];
    while ($row = $result->fetch_assoc()) {
        $favs[] = $row;
    }
    echo json_encode($favs);
}

// ADD favorite
elseif ($action === 'add') {
    $from = $_POST['from'] ?? '';
    $to   = $_POST['to']   ?? '';

    $stmt = $conn->prepare(
        "INSERT IGNORE INTO favorites 
         (user_id, from_currency, to_currency)
         VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iss", $user_id, $from, $to);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "added"]);
    } else {
        echo json_encode(["error" => "Already saved"]);
    }
}

// DELETE favorite
elseif ($action === 'delete') {
    $fav_id = $_POST['fav_id'] ?? 0;

    $stmt = $conn->prepare(
        "DELETE FROM favorites 
         WHERE id=? AND user_id=?"
    );
    $stmt->bind_param("ii", $fav_id, $user_id);
    $stmt->execute();
    echo json_encode(["status" => "removed"]);
}
?>