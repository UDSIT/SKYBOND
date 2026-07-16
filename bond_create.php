<?php
require __DIR__ . '/../config.php';
cors();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error' => 'POST required.'], 405);

$in = json_input();
$airline    = trim($in['airline'] ?? '');
$product    = trim($in['product'] ?? '');
$qty        = (int)($in['qty'] ?? 0);
$value      = (float)($in['value'] ?? 0);
$duty       = (float)($in['duty'] ?? 0);
$bondDate   = $in['bond_date'] ?? null;
$expiryDate = $in['expiry_date'] ?? null;

if ($airline === '' || $product === '' || $qty <= 0 || !$expiryDate) {
    respond(['error' => 'Airline, product, quantity, and expiry date are required.'], 400);
}

$pdo = db();

// Auto-numbered bond, e.g. 6E/00007/26-27, sequential per airline.
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bonds WHERE airline = ?");
$stmt->execute([$airline]);
$seq = (int)$stmt->fetchColumn() + 1;
$fyStart = date('y');
$fyEnd = date('y', strtotime('+1 year'));
$bondNo = sprintf('%s/%05d/%s-%s', $airline, $seq, $fyStart, $fyEnd);

$bondDate = $bondDate ?: date('Y-m-d');

$ins = $pdo->prepare(
    'INSERT INTO bonds (bond_no, airline, product, qty, value, duty, bond_date, expiry_date)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
$ins->execute([$bondNo, $airline, $product, $qty, $value, $duty, $bondDate, $expiryDate]);

respond(['bond_no' => $bondNo, 'id' => $pdo->lastInsertId()]);
