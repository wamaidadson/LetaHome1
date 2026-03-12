<?php
require_once 'php/config_supabase.php';

$pdo = getDatabaseConnection();

$sql = file_get_contents('database.sql');

if ($pdo->exec($sql) !== false) {
    echo "<h2>✅ Supabase DB setup complete!</h2>";
    echo "<p>Run SQL Editor: SELECT * FROM users; (admin ready)</p>";
} else {
    echo "<p>Error: " . print_r($pdo->errorInfo(), true) . "</p>";
}
?>

