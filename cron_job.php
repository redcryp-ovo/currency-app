<?php
// This file checks if rates need updating
// and triggers update_rates.php automatically

require_once 'db.php';
// Get the last update time
$result = $conn->query(
    "SELECT MAX(last_updated) as last 
     FROM exchange_rates"
);
$row = $result->fetch_assoc();
$last = new DateTime($row['last']);
$now = new DateTime();
$diff = $now->diff($last);

// Update if older than 24 hours
if ($diff->h >= 24 || $diff->days >= 1) {
    include 'update_rates.php';
} else {
    echo json_encode([
        "message" => "Rates are fresh",
        "last_updated" => $row['last']
    ]);
}
?>