<?php
// FILE: admin/pages/delete.php
session_start();
require __DIR__ . '/../../../includes/auth.php';

$type = $_GET['type'] ?? null;
$file = $_GET['file'] ?? null;

if (!$type || !$file || !in_array($type,['news','projects','careers'])) {
  http_response_code(400);
  echo "Invalid request";
  exit;
}

$path = realpath(__DIR__ . '/../../../storage') . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . basename($file);
if (file_exists($path)) {
  unlink($path);
  // optionally remove images referenced in JSON
  // attempt to decode and delete images
  if (file_exists($path)) { } // nothing
}

// return small HTML snippet or JSON; our SPA expects to reload the list after.
echo "<script>parent.loadPage('pages/{$type}.php');</script>";
