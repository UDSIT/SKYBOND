<?php
require __DIR__ . '/../config.php';
cors();
start_session();
$_SESSION = [];
session_destroy();
respond(['ok' => true]);
