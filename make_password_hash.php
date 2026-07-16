<?php
// Run this once from the command line (or briefly via browser, then DELETE the file)
// to generate a password hash you can paste into the users table.
//
// CLI:    php make_password_hash.php "YourPassword123"
// Browser: https://yourdomain.com/make_password_hash.php?password=YourPassword123
//          (delete this file from the server immediately after use — it must
//           never stay on a live site, since it accepts a password in plain text)

$password = $argv[1] ?? ($_GET['password'] ?? null);

if (!$password) {
    echo "Usage: php make_password_hash.php \"YourPassword\"\n";
    exit(1);
}

echo password_hash($password, PASSWORD_BCRYPT) . "\n";
