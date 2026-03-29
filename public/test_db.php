<?php
$dotenv = parse_ini_file(__DIR__ . '/../.env');

$pdo = new PDO(
    "pgsql:host={$dotenv['DB_HOST']};port={$dotenv['DB_PORT']};dbname={$dotenv['DB_NAME']}",
    $dotenv['DB_USER'],
    $dotenv['DB_PASSWORD']
);

$result = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'pilote'");
$columns = $result->fetchAll(PDO::FETCH_ASSOC);

echo "<ul>";
foreach ($columns as $col) {
    echo "<li>" . $col['column_name'] . "</li>";
}
echo "</ul>";
?>