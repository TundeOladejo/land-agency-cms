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
    header("Location: login.php");
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - Land Agency CMS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <style>
    .modal {
      background-color: rgba(0, 0, 0, 0.6);
    }
  </style>
</head>
<body class="bg-gray-100">

  <div class="min-h-screen flex items-center justify-center">
    <div class="bg-white p-6 rounded shadow-md w-full max-w-md">

      <?php if ($success): ?>
        <!-- Modal -->
        <div id="successModal" class="fixed inset-0 flex items-center justify-center modal z-50">
          <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm w-full mx-4 text-center">
            <div class="text-green-600 text-4xl mb-4">
              <i class="fa-solid fa-circle-check"></i>
            </div>
            <h2 class="text-xl font-semibold mb-2">Password Reset Successful</h2>
            <p class="text-gray-700 mb-6">You can now <a href="login.php" class="text-blue-600 underline">log in</a> with your new password.</p>
            <button onclick="closeModal()" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded">OK</button>
          </div>
        </div>
        <script>
          function closeModal() {
            document.getElementById('successModal').style.display = 'none';
          }
          window.onload = () => {
            document.getElementById('successModal').querySelector('button')?.focus();
          };
        </script>

      <?php elseif ($reset): ?>
        <h2 class="text-2xl font-bold mb-6">Set a New Password</h2>

        <?php foreach ($errors as $e): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-2 text-sm">
            <?= htmlspecialchars($e) ?>
          </div>
        <?php endforeach; ?>

        <form method="POST" class="space-y-4 mt-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">New Password</label>
            <input type="password" name="password" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:ring-blue-300">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" name="confirm_password" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:ring-blue-300">
          </div>

          <div>
            <button class="w-full bg-gray-800 hover:bg-gray-600 text-white py-2 px-5 rounded">Update Password</button>
          </div>
        </form>

      <?php else: ?>
        <h2 class="text-2xl text-red-600 font-bold mb-4">Invalid or Expired Token</h2>
        <p class="text-sm text-gray-700">Please request a new password reset <a href="forgot_password.php" class="text-blue-600 underline">here</a>.</p>
      <?php endif; ?>

    </div>
  </div>

</body>
</html>
