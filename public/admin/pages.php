<?php
session_start();
require '../../includes/db.php';
require '../../includes/auth.php';

$user = $_SESSION['user'] ?? null;

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user['id'] ?? 0]);
$me = $stmt->fetch();

if (!$me || !in_array($me['role'], ['admin', 'editor'])) {
  http_response_code(403);
  exit("Access denied");
}

$pages = [
  ['title' => 'News', 'slug' => 'news'],
  ['title' => 'Projects', 'slug' => 'projects'],
  ['title' => 'Careers', 'slug' => 'careers', 'tally' => 'https://tally.so/r/xyz123'], // replace with your actual link
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Pages</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6 bg-gray-50">
  <div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Manage Pages</h1>
    
    <ul class="space-y-4">
      <?php foreach ($pages as $page): ?>
        <li class="flex items-center justify-between bg-white shadow p-4 rounded">
          <div>
            <h2 class="font-semibold text-lg"><?= htmlspecialchars($page['title']) ?></h2>
            <small class="text-gray-500">Last updated: <?= $page['updated_at'] ?></small>
          </div>
          <a href="editor.php?slug=<?= urlencode($page['slug']) ?>" class="bg-blue-600 text-white px-4 py-2 rounded">Edit</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</body>
</html>
