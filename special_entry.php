<?php
require __DIR__ . '/../config.php';
cors();
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error' => 'POST required.'], 405);

$in = json_input();
$bondNo = trim($in['bond_no'] ?? '');
$type   = $in['entry_type'] ?? ''; // damage | sample | shortage
$qty    = (int)($in['qty'] ?? 0);
$date   = $in['entry_date'] ?? date('Y-m-d');
$remark = trim($in['remark'] ?? '');

$columnMap = ['damage' => 'damage_qty', 'sample' => 'sample_qty', 'shortage' => 'shortage_qty'];
if (!isset($columnMap[$type])) respond(['error' => 'entry_type must be damage, sample, or shortage.'], 400);
if ($bondNo === '' || $qty <= 0) respond(['error' => 'Bond number and a positive quantity are required.'], 400);
if ($remark === '') respond(['error' => 'Remark & authorization is required.'], 400);

$pdo = db();
$stmt = $pdo->prepare('SELECT id, qty, damage_qty, shortage_qty, sample_qty, shipped_qty FROM bonds WHERE bond_no = ?');
$stmt->execute([$bondNo]);
$bond = $stmt->fetch();
if (!$bond) respond(['error' => 'Bond not found.'], 404);

$currentStock = $bond['qty'] - $bond['damage_qty'] - $bond['shortage_qty'] - $bond['sample_qty'] - $bond['shipped_qty'];
if ($qty > $currentStock) respond(['error' => "Quantity exceeds available stock ({$currentStock})."], 400);

$col = $columnMap[$type];
$pdo->prepare("UPDATE bonds SET {$col} = {$col} + ? WHERE id = ?")->execute([$qty, $bond['id']]);

$pdo->prepare(
    'INSERT INTO bond_entries (bond_id, entry_type, qty, entry_date, remark, created_by)
     VALUES (?, ?, ?, ?, ?, ?)'
)->execute([$bond['id'], $type, $qty, $date, $remark, $user['username']]);

respond(['ok' => true]);
