<?php
session_start();
require '../includes/db.php';
require '../includes/auth.php';

// Only allow admin users
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id'] ?? 0]);
$me = $stmt->fetch();
if (!$me || $me['role'] !== 'admin') {
  http_response_code(403);
  exit("Access denied");
}

// Fetch users
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$sql = "SELECT * FROM users WHERE 1";
$params = [];

if ($roleFilter) {
  $sql .= " AND role = ?";
  $params[] = $roleFilter;
}
if ($statusFilter === 'active') {
  $sql .= " AND deleted_at IS NULL";
} elseif ($statusFilter === 'inactive') {
  $sql .= " AND deleted_at IS NOT NULL";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6 bg-gray-50">
  <div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Users</h1>

    <div class="mb-4 flex gap-4">
      <form method="GET" class="flex gap-2">
        <select name="role" class="border rounded px-3 py-1">
          <option value="">All Roles</option>
          <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
          <option value="editor" <?= $roleFilter === 'editor' ? 'selected' : '' ?>>Editor</option>
          <option value="viewer" <?= $roleFilter === 'viewer' ? 'selected' : '' ?>>Viewer</option>
        </select>
        <select name="status" class="border rounded px-3 py-1">
          <option value="">All Status</option>
          <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
        <button class="bg-blue-600 text-white px-4 py-1 rounded">Filter</button>
      </form>
      <a href="create_user.php" class="bg-green-600 text-white px-4 py-1 rounded">Add User</a>
    </div>

    <table class="w-full bg-white shadow rounded overflow-hidden">
      <thead class="bg-gray-100 text-left">
        <tr>
          <th class="p-3">Name</th>
          <th class="p-3">Email</th>
          <th class="p-3">Role</th>
          <th class="p-3">Status</th>
          <th class="p-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="p-3"><?= htmlspecialchars($user['name']) ?></td>
            <td class="p-3"><?= htmlspecialchars($user['email']) ?></td>
            <td class="p-3"><?= $user['role'] ?></td>
            <td class="p-3">
              <?= is_null($user['deleted_at']) ? 'Active' : 'Inactive' ?>
            </td>
            <td class="p-3">
              <?php if ($user['id'] != $_SESSION['user_id']): ?>
                <form method="POST" action="toggle_user.php" style="display:inline">
                  <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                  <button class="text-sm text-blue-600 underline" onclick="return confirm('Are you sure?')">
                    <?= is_null($user['deleted_at']) ? 'Deactivate' : 'Activate' ?>
                  </button>
                </form>
              <?php else: ?>
                <span class="text-sm text-gray-400">You</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
