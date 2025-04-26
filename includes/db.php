<?php
$isReplit = (getenv('REPLIT_DB_URL') || getenv('PGHOST'));

if ($isReplit) {
    $pgHost = getenv('PGHOST') ?: 'localhost';
    $pgPort = getenv('PGPORT') ?: '5432';
    $pgDatabase = getenv('PGDATABASE') ?: 'postgres';
    $pgUser = getenv('PGUSER') ?: 'postgres';
    $pgPassword = getenv('PGPASSWORD') ?: '';

    define('DB_HOST', $pgHost);
    define('DB_PORT', $pgPort);
    define('DB_USER', $pgUser);
    define('DB_PASS', $pgPassword);
    define('DB_NAME', $pgDatabase);
    define('DB_TYPE', 'pgsql');
} else {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'exam_reviewer');
    define('DB_TYPE', 'mysql');
}

function getDbConnection() {
    static $pdo;
    
    if (!$pdo) {
        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            if (DB_TYPE === 'pgsql') {
                $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } else {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            }
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
?>
