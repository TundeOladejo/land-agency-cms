<?php
require '../includes/db.php';
require '../includes/mail.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'] ?? '';
  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  $msg = "The reset link has been successfully sent to your email address. This link is valid for only 15 minutes.";

  if ($user) {
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', time() + 1800); // 30 minutes
    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $token, $expires_at]);

    $link = "https://ubiquitous-journey-w9jxr6qv4r6c9p7q-8000.app.github.dev/reset-password.php?token=$token";
    sendResetEmail($email, $link);
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - Land Agency CMS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

  <style>
    .modal {
      background-color: rgba(0, 0, 0, 0.6);
    }
  </style>
</head>
<body class="bg-gray-100">

  <div class="flex flex-col lg:flex-row min-h-screen">
    
    <!-- Left side -->
    <div class="flex flex-col justify-center items-center text-white p-8 lg:w-1/3" style="background-image: url('assets/images/backdrop.png'); background-size: cover; background-position: center;">
      <img src="assets/images/oy-logo.png" alt="Logo" class="w-24 sm:w-32 mb-6">
      <p class="font-bold text-3xl sm:text-4xl text-center">CMS Portal</p>
    </div>

    <!-- Right side -->
    <div class="flex flex-1 items-center justify-center p-6">
      <div class="w-full max-w-md bg-white p-6 sm:p-8 shadow-lg rounded-lg">
        <h1 class="text-2xl font-bold mb-6">Forgot Your Password?</h1>
        <p class="mb-4 text-sm text-gray-600">Enter your email address and we’ll send you a link to reset your password.</p>

        <form method="POST" class="space-y-4" id="resetForm">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:ring-blue-300">
          </div>
          <div>
            <button type="submit" class="bg-gray-800 hover:bg-gray-600 text-white py-2 px-5 rounded">Send Reset Link</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php if ($msg): ?>
  <div id="msgModal" class="fixed inset-0 flex items-center justify-center modal z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full mx-4 text-center">
      <!-- Icon -->
      <div class="text-green-600 text-4xl mb-4">
        <i class="fa-solid fa-circle-check"></i>
      </div>
      <h2 class="text-xl font-semibold mb-2">Reset Link Sent Successfully</h2>
      <p class="text-gray-700 mb-6"><?= htmlspecialchars($msg) ?></p>
      <button onclick="closeModal()" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded">OK</button>
    </div>
  </div>
  <script>
    function closeModal() {
      document.getElementById('msgModal').style.display = 'none';
    }
    window.onload = () => {
      const modal = document.getElementById('msgModal');
      if (modal) modal.querySelector('button')?.focus();
    };
  </script>
<?php endif; ?>


</body>
</html>
