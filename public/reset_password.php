<?php
require '../includes/db.php';
require '../includes/auth.php';
require '../includes/mail.php';

$token = $_GET['token'] ?? '';
$errors = [];
$success = false;

$reset = validateResetToken($token);

if (!$reset) {
  $errors[] = "Invalid or expired reset token.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  if (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters.";
  }

  if ($password !== $confirm) {
    $errors[] = "Passwords do not match.";
  }

  if (empty($errors)) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    updateUserPassword($reset['email'], $hashed);
    markTokenUsed($token);
    sendConfirmationEmail($reset['email']);
    $success = true;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="min-h-screen flex items-center justify-center">
    <div class="bg-white p-6 rounded shadow-md w-96">
      <?php if ($success): ?>
        <h2 class="text-xl text-green-700 mb-4">Password Reset Successful</h2>
        <p class="text-sm">You can now <a href="login.php" class="text-blue-600">log in</a> with your new password.</p>
      <?php elseif ($reset): ?>
        <h2 class="text-xl mb-4">Set a New Password</h2>
        <?php foreach ($errors as $e): ?>
          <p class="text-red-500 text-sm"><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
        <form method="POST" class="mt-4">
          <label class="block mb-1">New Password</label>
          <input type="password" name="password" required class="w-full border px-3 py-2 rounded mb-4">
          <label class="block mb-1">Confirm Password</label>
          <input type="password" name="confirm_password" required class="w-full border px-3 py-2 rounded mb-4">
          <button class="w-full bg-blue-600 text-white py-2 rounded">Update Password</button>
        </form>
      <?php else: ?>
        <h2 class="text-xl text-red-600">Invalid or Expired Token</h2>
        <p class="text-sm mt-2">Please request a new password reset <a href="forgot_password.php" class="text-blue-600">here</a>.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
