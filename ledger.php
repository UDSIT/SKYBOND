<?php
require __DIR__ . '/../config.php';
cors();
require_login();

$bondNo = trim($_GET['bond_no'] ?? '');
if ($bondNo === '') respond(['error' => 'bond_no query parameter is required.'], 400);

$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM bonds WHERE bond_no = ?');
$stmt->execute([$bondNo]);
$bond = $stmt->fetch();
if (!$bond) respond(['error' => 'Bond not found.'], 404);

$bond['current_stock'] = $bond['qty'] - $bond['damage_qty'] - $bond['shortage_qty'] - $bond['sample_qty'] - $bond['shipped_qty'];

$entries = $pdo->prepare('SELECT entry_type, qty, entry_date, remark, created_by, created_at FROM bond_entries WHERE bond_id = ? ORDER BY created_at DESC');
$entries->execute([$bond['id']]);

$shipments = $pdo->prepare(
    'SELECT sb.bill_date, sb.flight_no, sb.destination, sbi.qty, sbi.duty_amount
     FROM shipping_bill_items sbi JOIN shipping_bills sb ON sb.id = sbi.shipping_bill_id
     WHERE sbi.bond_id = ? ORDER BY sb.bill_date DESC'
);
$shipments->execute([$bond['id']]);

respond([
    'bond' => $bond,
    'entries' => $entries->fetchAll(),
    'shipments' => $shipments->fetchAll(),
]);
