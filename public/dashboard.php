<?php
// FILE: public/dashboard.php

require_once '../includes/auth.php';
require_once '../includes/db.php';

// Redirect to login if not authenticated
session_start();
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard | Land Agency CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen text-gray-900">

  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <h1 class="text-xl font-bold">Land Agency Admin Dashboard</h1>
    <div class="space-x-4">
      <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</span>
      <a href="logout.php" class="text-red-500 hover:underline">Logout</a>
    </div>
  </nav>

  <main class="p-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold">Manage Users</h2>
        <p class="text-sm text-gray-600">Create, update, or delete admin users.</p>
        <a href="users.php" class="inline-block mt-2 text-blue-600 hover:underline">Go to Users</a>
      </div>

      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold">View Submissions</h2>
        <p class="text-sm text-gray-600">View contact form or property requests.</p>
        <a href="submissions.php" class="inline-block mt-2 text-blue-600 hover:underline">Go to Submissions</a>
      </div>

      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold">Settings</h2>
        <p class="text-sm text-gray-600">Update site configuration or password.</p>
        <a href="settings.php" class="inline-block mt-2 text-blue-600 hover:underline">Go to Settings</a>
      </div>
    </div>
  </main>

</body>
</html>
