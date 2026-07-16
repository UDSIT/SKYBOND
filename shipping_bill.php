<?php
require __DIR__ . '/../config.php';
cors();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error' => 'POST required.'], 405);

$in = json_input();
$airline = trim($in['airline'] ?? '');
$flightNo = trim($in['flight_no'] ?? '');
$destination = trim($in['destination'] ?? '');
$items = $in['items'] ?? []; // [{bond_no, qty}]

if (!$items || !is_array($items)) respond(['error' => 'At least one item is required.'], 400);

$pdo = db();
$pdo->beginTransaction();

try {
    $billStmt = $pdo->prepare(
        'INSERT INTO shipping_bills (airline, flight_no, destination, bill_date) VALUES (?, ?, ?, ?)'
    );
    $billStmt->execute([$airline, $flightNo, $destination, date('Y-m-d')]);
    $billId = $pdo->lastInsertId();

    $bondStmt = $pdo->prepare('SELECT id, qty, duty, damage_qty, shortage_qty, sample_qty, shipped_qty FROM bonds WHERE bond_no = ? FOR UPDATE');
    $itemStmt = $pdo->prepare('INSERT INTO shipping_bill_items (shipping_bill_id, bond_id, qty, duty_amount) VALUES (?, ?, ?, ?)');
    $updateStmt = $pdo->prepare('UPDATE bonds SET shipped_qty = shipped_qty + ? WHERE id = ?');

    foreach ($items as $item) {
        $bondNo = trim($item['bond_no'] ?? '');
        $qty = (int)($item['qty'] ?? 0);
        if ($bondNo === '' || $qty <= 0) throw new Exception('Each item needs a bond number and positive quantity.');

        $bondStmt->execute([$bondNo]);
        $bond = $bondStmt->fetch();
        if (!$bond) throw new Exception("Bond {$bondNo} not found.");

        $currentStock = $bond['qty'] - $bond['damage_qty'] - $bond['shortage_qty'] - $bond['sample_qty'] - $bond['shipped_qty'];
        if ($qty > $currentStock) throw new Exception("Quantity for {$bondNo} exceeds available stock ({$currentStock}).");

        $dutyAmount = round(($bond['duty'] / max($bond['qty'], 1)) * $qty, 2);
        $itemStmt->execute([$billId, $bond['id'], $qty, $dutyAmount]);
        $updateStmt->execute([$qty, $bond['id']]);
    }

    $pdo->commit();
    respond(['ok' => true, 'shipping_bill_id' => $billId]);
} catch (Exception $e) {
    $pdo->rollBack();
    respond(['error' => $e->getMessage()], 400);
}
