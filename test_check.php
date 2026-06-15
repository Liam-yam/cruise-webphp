<?php
require_once __DIR__ . "/db.php";

echo "=== tbl_ticket sample ===\n";
$result = $conn->query('SELECT ticket_no, cruise_ship, ticket_tier, departure_date, room_no FROM tbl_ticket ORDER BY cruise_ship, ticket_tier LIMIT 5');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ticket_no={$row['ticket_no']} ship='{$row['cruise_ship']}' tier='{$row['ticket_tier']}' date='{$row['departure_date']}'\n";
    }
} else {
    echo "tbl_ticket error: " . $conn->error . "\n";
}

echo "\n=== count tbl_ticket ===\n";
$res = $conn->query('SELECT COUNT(*) AS c FROM tbl_ticket');
echo "Total: " . $res->fetch_assoc()['c'] . "\n";

echo "\n=== check foreign keys ===\n";
$res = $conn->query("
    SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'paglaot_db'
      AND REFERENCED_TABLE_NAME IS NOT NULL
    ORDER BY TABLE_NAME, COLUMN_NAME
");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo "{$row['TABLE_NAME']}.{$row['COLUMN_NAME']} -> {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']} ({$row['CONSTRAINT_NAME']})\n";
    }
}

echo "\n=== try resolving Tropical/PREMIUM/2026-06-02 ===\n";
$stmt = $conn->prepare('SELECT ticket_no FROM tbl_ticket WHERE cruise_ship = ? AND ticket_tier = ? AND departure_date = ? LIMIT 1');
$ship = 'Tropical';
$tier = 'PREMIUM';
$date = '2026-06-02';
$stmt->bind_param('sss', $ship, $tier, $date);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if ($row) {
    echo "Found ticket_no = {$row['ticket_no']}\n";
} else {
    echo "NOT FOUND\n";
    echo "Error: " . $stmt->error . "\n";
}