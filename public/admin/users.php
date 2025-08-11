<?php
session_start();
require '../../includes/db.php';
require '../../includes/auth.php';

$user = $_SESSION['user'] ?? null;

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user['id'] ?? 0]);
$me = $stmt->fetch();

if (!$me || $me['role'] !== 'admin') {
  http_response_code(403);
  exit("Access denied");
}

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

require_once '../../includes/layout/top.php';
?>

<h1 class="text-2xl font-bold mb-4">Users</h1>

<div class="mb-4 flex justify-between gap-4">
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

  <a href="create_user.php" class="bg-gray-800 text-white px-5 py-2 rounded">Add User</a>
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
    <?php foreach ($users as $u): ?>
      <tr class="border-b hover:bg-gray-50">
        <td class="p-3"><?= htmlspecialchars($u['name']) ?></td>
        <td class="p-3"><?= htmlspecialchars($u['email']) ?></td>
        <td class="p-3 capitalize"><?= $u['role'] ?></td>
        <td class="p-3"><?= is_null($u['deleted_at']) ? 'Active' : 'Inactive' ?></td>
        <td class="p-3">
          <?php if ($u['id'] != $user['id']): ?>
            <form method="POST" action="toggle_user.php" style="display:inline">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <button class="text-sm text-blue-600 underline" onclick="return confirm('Are you sure?')">
                <?= is_null($u['deleted_at']) ? 'Deactivate' : 'Activate' ?>
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

<?php require_once '../../includes/layout/bottom.php'; ?>
