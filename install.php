<?php
// install.php - run once to create full ERD schema and sample data
require_once 'db.php';

$sql = file_get_contents('full_schema.sql');
if (!$sql) {
    die('full_schema.sql not found');
}
$queries = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));
$errors = [];
foreach ($queries as $q) {
    if (strlen($q) < 5) continue;
    if (!mysqli_query($conn, $q)) {
        $errors[] = mysqli_error($conn) . " -- QUERY: " . substr($q,0,200);
    }
}

if (count($errors) > 0) {
    echo "<h2>Completed with errors</h2><pre>";
    echo htmlspecialchars(implode("\n\n", $errors));
    echo "</pre>";
} else {
    echo "<h2>Schema & sample data created successfully.</h2>";
    echo "<p>Remove install.php after use for security.</p>";
}
?>