<?php
session_start();
require '../../includes/db.php';
require '../../includes/auth.php';
require '../../includes/mail.php';

// Ensure only admins can access
$user = $_SESSION['user'] ?? null;

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user['id'] ?? 0]);
$me = $stmt->fetch();

if (!$me || $me['role'] !== 'admin') {
  http_response_code(403);
  exit("Access denied");
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $role = $_POST['role'] ?? 'editor';

  if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Valid name and email are required.";
  }

  // Check if email already exists
  $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->execute([$email]);
  if ($stmt->fetch()) {
    $errors[] = "A user with this email already exists.";
  }

  if (empty($errors)) {
    $password = bin2hex(random_bytes(4)); // 8-char random string
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hashed, $role]);

    sendWelcomeEmail($email, $password);
    $success = true;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create User</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-lg mx-auto bg-white shadow p-6 rounded">
    <h1 class="text-xl font-bold mb-4">Create New User</h1>

    <?php if ($success): ?>
      <p class="text-green-600 mb-4">User created and welcome email sent!</p>
      <a href="users.php" class="text-blue-600 underline">Back to user list</a>
    <?php else: ?>
      <?php foreach ($errors as $e): ?>
        <p class="text-red-500 text-sm"><?= htmlspecialchars($e) ?></p>
      <?php endforeach; ?>
      <form method="POST" class="mt-4 space-y-4">
        <div>
          <label class="block mb-1">Full Name</label>
          <input type="text" name="name" required class="w-full border px-3 py-2 rounded">
        </div>
        <div>
          <label class="block mb-1">Email</label>
          <input type="email" name="email" required class="w-full border px-3 py-2 rounded">
        </div>
        <div>
          <label class="block mb-1">Role</label>
          <select name="role" class="w-full border px-3 py-2 rounded">
            <option value="admin">Admin</option>
            <option value="editor" selected>Editor</option>
            <option value="viewer">Viewer</option>
          </select>
        </div>
        <button class="bg-green-600 text-white px-4 py-2 rounded">Create User</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>