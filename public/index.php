<?php
// FILE: public/login.php

require_once '../includes/db.php';
require_once '../includes/auth.php';

session_start();

$error = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $ip = $_SERVER['REMOTE_ADDR'];

  // Check for lockout
  if (isLockedOut($email, $ip)) {
    $error = "Too many failed login attempts. Try again in 30 minutes.";
  } else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
      // Login successful
      clearLoginAttempts($email, $ip);
      $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role']
      ];
      header("Location: dashboard.php");
      exit;
    } else {
      // Failed login
      logFailedAttempt($email, $ip);
      $error = "Invalid email or password.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Land Agency CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen px-4">
  <div class="w-full max-w-md bg-white p-8 shadow-lg rounded-lg">
    <h1 class="text-2xl font-bold mb-6 text-center">Login to Admin</h1>

    <?php if ($error): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:ring-blue-300">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" name="password" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:ring-blue-300">
      </div>

      <div>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">Login</button>
      </div>
    </form>

    <p class="mt-4 text-sm text-center">
      <a href="forgot-password.php" class="text-blue-500 hover:underline">Forgot password?</a>
    </p>
  </div>
</body>
</html>
