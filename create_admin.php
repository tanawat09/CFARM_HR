<?php

$host = '192.168.7.3';
$port = '3308';
$db   = 'cfarm_hr';
$user = 'itadmin';
$pass = 'Noipalee@09';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Hash password using bcrypt (Laravel compatible)
    $password = password_hash('password', PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@cfarm.co.th']);
    if ($stmt->fetch()) {
        echo "Admin user already exists! Force updating password to 'password'...\n";
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->execute([$password, 'admin@cfarm.co.th']);
        echo "Password updated successfully!\n";
    } else {
        $insert = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $insert->execute(['admin', 'admin@cfarm.co.th', $password, 'admin']);
        echo "Admin user created successfully!\n";
    }

} catch (\PDOException $e) {
    echo "Connection or Query failed: " . $e->getMessage() . "\n";
}
