<?php
require __DIR__ . '/../config.php';
cors();
start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['error' => 'POST required.'], 405);

$in = json_input();
$username = trim($in['username'] ?? '');
$password = $in['password'] ?? '';

if ($username === '' || $password === '') {
    respond(['error' => 'Username and password are required.'], 400);
}

$stmt = db()->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    respond(['error' => 'Invalid username or password.'], 401);
}

$_SESSION['user'] = ['id' => $user['id'], 'username' => $user['username'], 'role' => $user['role']];
respond(['user' => $_SESSION['user']]);
