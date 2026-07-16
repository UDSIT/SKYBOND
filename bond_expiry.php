<?php
require __DIR__ . '/../config.php';
cors();
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error' => 'POST required.'], 405);

$in = json_input();
$bondNo = trim($in['bond_no'] ?? '');
$revised = $in['revised_expiry'] ?? '';

if ($bondNo === '' || !$revised) respond(['error' => 'Bond number and revised date are required.'], 400);

$pdo = db();
$stmt = $pdo->prepare('SELECT id, expiry_date FROM bonds WHERE bond_no = ?');
$stmt->execute([$bondNo]);
$bond = $stmt->fetch();
if (!$bond) respond(['error' => 'Bond not found.'], 404);

$pdo->prepare('UPDATE bonds SET expiry_date = ? WHERE id = ?')->execute([$revised, $bond['id']]);

$pdo->prepare(
    'INSERT INTO bond_entries (bond_id, entry_type, qty, entry_date, remark, created_by)
     VALUES (?, "expiry_change", 0, ?, ?, ?)'
)->execute([$bond['id'], date('Y-m-d'), "Changed from {$bond['expiry_date']} to {$revised}", $user['username']]);

respond(['ok' => true]);
