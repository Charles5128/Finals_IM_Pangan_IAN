<?php
/**
 * Database initialization script
 * This script initializes the database with the required schema
 */

require_once 'includes/config.php';
require_once 'includes/db.php';

try {
    // Get the database connection
    $pdo = getDbConnection();
    
    // Select the appropriate schema file based on database type
    $schemaFile = (DB_TYPE === 'pgsql') ? 'postgres-schema.sql' : 'database.sql';
    
    // Read the SQL file
    $sql = file_get_contents($schemaFile);
    
    // Execute the SQL
    $pdo->exec($sql);
    
    echo "Database initialized successfully with $schemaFile schema!";
} catch (PDOException $e) {
    die("Database initialization failed: " . $e->getMessage());
}
?>