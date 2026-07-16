<?php
require __DIR__ . '/../config.php';
cors();
require_login();

$rows = db()->query('SELECT * FROM bonds ORDER BY id DESC')->fetchAll();

foreach ($rows as &$b) {
    $b['current_stock'] = (int)$b['qty'] - (int)$b['damage_qty'] - (int)$b['shortage_qty']
                           - (int)$b['sample_qty'] - (int)$b['shipped_qty'];
}

respond(['bonds' => $rows]);
