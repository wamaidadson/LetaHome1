<?php
// Supabase/PostgreSQL Setup
require_once 'php/config.php';

$pdo = getDatabaseConnection();

echo "<h2>LETA HOMES PostgreSQL Setup (Supabase)</h2>";

$sql = file_get_contents('database_pgsql_fixed.sql');
if ($sql === false) {
    die("Error reading database_pgsql_fixed.sql");
}

try {
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ PostgreSQL tables created!</p>";
    echo "<p><strong>Admin: admin/admin</strong></p>";
    echo "<a href='index.php'>→ Dashboard</a>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . htmlspecialchars($sql) . "</pre>";
}
?>

