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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome (Solid icons) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

  <style>
    .modal {
      background-color: rgba(0, 0, 0, 0.6);
    }
  </style>
</head>
<body class="bg-gray-100">

  <div class="min-h-screen flex items-center justify-center">
    <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
      <h2 class="text-2xl font-bold mb-6">Change Password</h2>

      <?php if (!$success): ?>
        <?php foreach ($errors as $e): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-2 text-sm">
            <?= htmlspecialchars($e) ?>
          </div>
        <?php endforeach; ?>

        <form method="POST" class="space-y-4 mt-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Current Password</label>
            <input type="password" name="current_password" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:ring-blue-300">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">New Password</label>
            <input type="password" name="new_password" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:ring-blue-300">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
            <input type="password" name="confirm_password" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:ring-blue-300">
          </div>

          <div>
            <button class="w-full bg-gray-800 hover:bg-gray-600 text-white py-2 px-5 rounded">Change Password</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- Success Modal -->
  <?php if ($success): ?>
    <div id="successModal" class="fixed inset-0 flex items-center justify-center modal z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm w-full mx-4 text-center">
        <div class="text-green-600 text-4xl mb-4">
          <i class="fa-solid fa-circle-check"></i>
        </div>
        <h2 class="text-xl font-semibold mb-2">Password Updated</h2>
        <p class="text-gray-700 mb-6">Your password has been changed successfully.</p>
        <button onclick="closeModal()" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded">OK</button>
      </div>
    </div>

    <script>
      function closeModal() {
        document.getElementById('successModal').style.display = 'none';
      }
      window.onload = () => {
        const modal = document.getElementById('successModal');
        if (modal) modal.querySelector('button')?.focus();
      };
    </script>
  <?php endif; ?>

</body>
</html>
