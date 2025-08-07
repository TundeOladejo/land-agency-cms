<?php
// FILE: includes/db.php

$dbPath = __DIR__ . '/../storage/database.sqlite';
$dsn = 'sqlite:' . $dbPath;

try {
  $pdo = new PDO($dsn);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
?>
