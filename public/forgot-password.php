<?php
require '../includes/db.php';
require '../includes/mail.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'] ?? '';
  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  $msg = "If that email is registered, a reset link has been sent.";

  if ($user) {
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', time() + 1800); // 30 minutes
    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $token, $expires_at]);

    $link = "http://localhost/land-agency-cms/public/reset_password.php?token=$token";
    sendResetEmail($email, $link);
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="min-h-screen flex items-center justify-center">
    <form method="POST" class="bg-white p-6 rounded shadow-md w-96">
      <h2 class="text-xl mb-4">Reset Password</h2>
      <?php if ($msg): ?>
        <p class="mb-4 text-green-600"><?= $msg ?></p>
      <?php endif; ?>
      <label class="block mb-2">Email</label>
      <input type="email" name="email" required class="w-full border px-3 py-2 rounded mb-4">
      <button class="w-full bg-blue-600 text-white py-2 rounded">Send Reset Link</button>
    </form>
  </div>
</body>
</html>
