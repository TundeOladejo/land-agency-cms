<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';
require '../includes/mail.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $current = $_POST['current_password'] ?? '';
  $new = $_POST['new_password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch();

  if (!$user || !password_verify($current, $user['password'])) {
    $errors[] = "Your current password is incorrect.";
  } elseif (strlen($new) < 8) {
    $errors[] = "New password must be at least 8 characters.";
  } elseif ($new !== $confirm) {
    $errors[] = "New passwords do not match.";
  } elseif (password_verify($new, $user['password'])) {
    $errors[] = "You cannot reuse your old password.";
  }

  if (empty($errors)) {
    $newHash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$newHash, $_SESSION['user_id']]);

    sendConfirmationEmail($user['email']);
    $success = true;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="min-h-screen flex items-center justify-center">
    <div class="bg-white p-6 rounded shadow-md w-96">
      <h2 class="text-xl mb-4">Change Password</h2>

      <?php if ($success): ?>
        <p class="text-green-600">Your password has been changed successfully.</p>
      <?php else: ?>
        <?php foreach ($errors as $e): ?>
          <p class="text-red-500 text-sm"><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>

        <form method="POST" class="mt-4">
          <label class="block mb-1">Current Password</label>
          <input type="password" name="current_password" required class="w-full border px-3 py-2 rounded mb-4">

          <label class="block mb-1">New Password</label>
          <input type="password" name="new_password" required class="w-full border px-3 py-2 rounded mb-4">

          <label class="block mb-1">Confirm New Password</label>
          <input type="password" name="confirm_password" required class="w-full border px-3 py-2 rounded mb-4">

          <button class="w-full bg-blue-600 text-white py-2 rounded">Change Password</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
