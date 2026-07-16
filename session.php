<?php
require __DIR__ . '/../config.php';
cors();
start_session();
respond(['user' => $_SESSION['user'] ?? null]);
