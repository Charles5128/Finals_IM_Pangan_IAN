<?php
/**
 * Database initialization script
 * This script initializes the database with the required schema
 */

require_once 'includes/config.php';
require_once 'includes/db.php';

try {
    $pdo = getDbConnection();
    $schemaFile = (DB_TYPE === 'pgsql') ? 'postgres-schema.sql' : 'database.sql';
    $sql = file_get_contents($schemaFile);
    $pdo->exec($sql);
    
    echo "Database initialized successfully with $schemaFile schema!";
} catch (PDOException $e) {
    die("Database initialization failed: " . $e->getMessage());
}
?>