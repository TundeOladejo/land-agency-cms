<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: users.php');
  exit;
}

$userId = $_POST['user_id'] ?? 0;
$adminId = $_SESSION['user_id'];

// Prevent self-deactivation
if ($userId == $adminId) {
  header('Location: users.php');
  exit;
}

// Toggle deletion
$stmt = $pdo->prepare("SELECT deleted_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($user) {
  if ($user['deleted_at']) {
    $stmt = $pdo->prepare("UPDATE users SET deleted_at = NULL WHERE id = ?");
  } else {
    $stmt = $pdo->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
  }
  $stmt->execute([$userId]);
}

header('Location: users.php');
