<?php
require_once 'db.php';

// Your API key from exchangerate-api.com
$api_key = "YOUR_API_KEY_HERE";
$base = "USD"; // Base currency

// Fetch live rates
$url = "https://v6.exchangerate-api.com/v6/{$api_key}/latest/{$base}";
$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data['result'] === 'success') {
    $rates = $data['conversion_rates'];
    $now = date('Y-m-d H:i:s');

    // Currencies we support
    $supported = ['KES', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF'];

    foreach ($supported as $target) {
        if (isset($rates[$target])) {
            $rate = $rates[$target];

            // Check if rate exists
            $check = $conn->prepare(
                "SELECT id FROM exchange_rates 
                 WHERE base_currency=? AND target_currency=?"
            );
            $check->bind_param("ss", $base, $target);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {
                // Update existing rate
                $stmt = $conn->prepare(
                    "UPDATE exchange_rates 
                     SET rate=?, last_updated=? 
                     WHERE base_currency=? AND target_currency=?"
                );
                $stmt->bind_param(
                    "dsss", $rate, $now, $base, $target
                );
            } else {
                // Insert new rate
                $stmt = $conn->prepare(
                    "INSERT INTO exchange_rates 
                     (base_currency, target_currency, rate, last_updated)
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param(
                    "ssds", $base, $target, $rate, $now
                );
            }
            $stmt->execute();
        }
    }

    // Also update KES→USD reverse rate
    if (isset($rates['KES'])) {
        $reverse = 1 / $rates['KES'];
        $stmt = $conn->prepare(
            "UPDATE exchange_rates SET rate=?, last_updated=?
             WHERE base_currency='KES' AND target_currency='USD'"
        );
        $stmt->bind_param("ds", $reverse, $now);
        $stmt->execute();
    }

    // Log success
    $msg = "Rates updated successfully";
    $status = "success";
    $log = $conn->prepare(
        "INSERT INTO api_log (status, message) VALUES (?, ?)"
    );
    $log->bind_param("ss", $status, $msg);
    $log->execute();

    echo json_encode([
        "status" => "success",
        "message" => "Rates updated",
        "timestamp" => $now
    ]);

} else {
    // Log failure
    $msg = $data['error-type'] ?? 'Unknown error';
    $status = "failed";
    $log = $conn->prepare(
        "INSERT INTO api_log (status, message) VALUES (?, ?)"
    );
    $log->bind_param("ss", $status, $msg);
    $log->execute();

    echo json_encode([
        "status" => "error",
        "message" => $msg
    ]);
}
?>